<?php

namespace App\Services\Food;

use App\Models\Food\FoodChargeRule;
use App\Models\Food\FoodCommissionRule;
use App\Models\Food\FoodDeliveryChargeRule;
use App\Models\Food\FoodMarkupRule;
use App\Models\Food\FoodPremiumDeal;
use App\Models\Food\FoodProduct;
use App\Models\Food\FoodRestaurant;
use Carbon\Carbon;

class FoodPricingEngine
{
    public function resolveMarkup(FoodProduct $product, FoodRestaurant $restaurant): array
    {
        $now = Carbon::now();
        $baseQuery = FoodMarkupRule::query()
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });

        $candidates = [
            ['scope' => 'product', 'product_id' => $product->id],
            ['scope' => 'category', 'category_id' => $product->category_id],
            ['scope' => 'restaurant', 'restaurant_id' => $restaurant->id],
            ['scope' => 'global'],
        ];

        foreach ($candidates as $filter) {
            if ($filter['scope'] === 'category' && empty($filter['category_id'])) {
                continue;
            }
            $rule = (clone $baseQuery)->where('scope', $filter['scope']);
            if (!empty($filter['product_id'])) {
                $rule->where('product_id', $filter['product_id']);
            }
            if (!empty($filter['category_id'])) {
                $rule->where('category_id', $filter['category_id']);
            }
            if (!empty($filter['restaurant_id'])) {
                $rule->where('restaurant_id', $filter['restaurant_id']);
            }
            $found = $rule->orderByDesc('id')->first();
            if ($found) {
                $out = $this->applyAmount((float) $product->restaurant_price, $found->rule_type, (float) $found->rule_value);
                $out['rule_id'] = $found->id;
                $out['source'] = $filter['scope'];
                return $out;
            }
        }

        return ['amount' => 0.0, 'rule_type' => null, 'rule_value' => 0, 'rule_id' => null, 'source' => 'none'];
    }

    public function resolveCommission(FoodRestaurant $restaurant, float $foodAmount, ?int $categoryId = null, ?int $productId = null): array
    {
        $now = Carbon::now();
        $deal = FoodPremiumDeal::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderByDesc('id')
            ->first();

        if ($deal && $deal->deal_type === 'commission_off') {
            return ['amount' => 0.0, 'rule_type' => 'premium_off', 'rule_value' => 0, 'rule_id' => $deal->id, 'source' => 'premium'];
        }

        $baseQuery = FoodCommissionRule::query()
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });

        $candidates = [
            ['scope' => 'product', 'product_id' => $productId],
            ['scope' => 'category', 'category_id' => $categoryId],
            ['scope' => 'restaurant', 'restaurant_id' => $restaurant->id],
            ['scope' => 'restaurant_type', 'restaurant_type_id' => $restaurant->type_id],
            ['scope' => 'global'],
        ];

        $found = null;
        $source = 'none';
        foreach ($candidates as $filter) {
            if (($filter['scope'] === 'product' && empty($filter['product_id']))
                || ($filter['scope'] === 'category' && empty($filter['category_id']))
                || ($filter['scope'] === 'restaurant_type' && empty($filter['restaurant_type_id']))) {
                continue;
            }
            $rule = (clone $baseQuery)->where('scope', $filter['scope']);
            foreach (['product_id', 'category_id', 'restaurant_id', 'restaurant_type_id'] as $key) {
                if (!empty($filter[$key])) {
                    $rule->where($key, $filter[$key]);
                }
            }
            $found = $rule->orderByDesc('id')->first();
            if ($found) {
                $source = $filter['scope'];
                break;
            }
        }

        if (!$found) {
            return ['amount' => 0.0, 'rule_type' => null, 'rule_value' => 0, 'rule_id' => null, 'source' => 'none'];
        }

        $result = $this->applyAmount($foodAmount, $found->rule_type, (float) $found->rule_value);
        $result['rule_id'] = $found->id;
        $result['source'] = $source;

        if ($deal && $deal->deal_type === 'percent_discount' && $deal->deal_value !== null) {
            $result['amount'] = round($result['amount'] * (1 - ((float) $deal->deal_value / 100)), 2);
            $result['source'] = 'premium_discount';
        } elseif ($deal && $deal->deal_type === 'fixed_commission' && $deal->deal_value !== null) {
            $result['amount'] = round((float) $deal->deal_value, 2);
            $result['source'] = 'premium_fixed';
        }

        return $result;
    }

    public function customerUnitPrice(FoodProduct $product, FoodRestaurant $restaurant): array
    {
        $base = (float) ($product->discount_price ?: $product->restaurant_price);
        $markup = $this->resolveMarkup($product, $restaurant);
        return [
            'restaurant_price' => (float) $product->restaurant_price,
            'base_price' => $base,
            'markup' => $markup['amount'],
            'customer_price' => round($base + $markup['amount'], 2),
            'markup_meta' => $markup,
        ];
    }

    public function calculateDeliveryCharge(float $distanceKm): array
    {
        $rule = FoodDeliveryChargeRule::query()->where('is_active', true)->orderByDesc('id')->first();
        if (!$rule) {
            return ['amount' => 0.0, 'available' => true, 'meta' => null];
        }

        if ($distanceKm > (float) $rule->max_distance_km) {
            if (!$rule->allow_above_max) {
                return ['amount' => 0.0, 'available' => false, 'meta' => $rule];
            }
            $extra = $distanceKm - (float) $rule->max_distance_km;
            $amount = (float) $rule->base_charge
                + max(0, (float) $rule->max_distance_km - (float) $rule->base_radius_km) * (float) $rule->per_km_charge
                + $extra * (float) ($rule->above_max_per_km ?? $rule->per_km_charge);
            return ['amount' => round($amount, 2), 'available' => true, 'meta' => $rule];
        }

        if (!empty($rule->distance_slabs) && is_array($rule->distance_slabs)) {
            foreach ($rule->distance_slabs as $slab) {
                $from = (float) ($slab['from'] ?? 0);
                $to = (float) ($slab['to'] ?? 0);
                if ($distanceKm >= $from && $distanceKm <= $to) {
                    return ['amount' => round((float) ($slab['charge'] ?? 0), 2), 'available' => true, 'meta' => $rule];
                }
            }
        }

        if ($distanceKm <= (float) $rule->free_radius_km) {
            return ['amount' => 0.0, 'available' => true, 'meta' => $rule];
        }

        $extraKm = max(0, $distanceKm - (float) $rule->base_radius_km);
        $amount = (float) $rule->base_charge + ($extraKm * (float) $rule->per_km_charge);
        return ['amount' => round($amount, 2), 'available' => true, 'meta' => $rule];
    }

    public function calculatePlatformCharges(float $foodSubtotal): array
    {
        $now = Carbon::now();
        $charges = FoodChargeRule::query()
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->get();

        $total = 0.0;
        $breakdown = [];
        foreach ($charges as $charge) {
            if ($charge->order_min !== null && $foodSubtotal < (float) $charge->order_min) {
                continue;
            }
            if ($charge->order_max !== null && $foodSubtotal > (float) $charge->order_max) {
                continue;
            }
            if ($charge->time_from && $charge->time_to) {
                $t = $now->format('H:i:s');
                if ($t < $charge->time_from || $t > $charge->time_to) {
                    continue;
                }
            }

            $amount = 0.0;
            if ($charge->charge_type === 'percentage') {
                $amount = $foodSubtotal * ((float) $charge->charge_value / 100);
            } elseif ($charge->charge_type === 'flat') {
                $amount = (float) $charge->charge_value;
            } elseif ($charge->charge_type === 'slab' && is_array($charge->slab_json)) {
                foreach ($charge->slab_json as $slab) {
                    $from = (float) ($slab['from'] ?? 0);
                    $to = (float) ($slab['to'] ?? PHP_FLOAT_MAX);
                    if ($foodSubtotal >= $from && $foodSubtotal <= $to) {
                        $amount = (float) ($slab['charge'] ?? 0);
                        break;
                    }
                }
            }

            if ($charge->min_amount !== null) {
                $amount = max($amount, (float) $charge->min_amount);
            }
            if ($charge->max_amount !== null) {
                $amount = min($amount, (float) $charge->max_amount);
            }
            $amount = round($amount, 2);
            $total += $amount;
            $breakdown[] = [
                'name' => $charge->name,
                'code' => $charge->code,
                'amount' => $amount,
            ];
        }

        return ['total' => round($total, 2), 'breakdown' => $breakdown];
    }

    protected function applyAmount(float $base, string $type, float $value): array
    {
        $amount = $type === 'flat' ? $value : round($base * ($value / 100), 2);
        return [
            'amount' => $amount,
            'rule_type' => $type,
            'rule_value' => $value,
        ];
    }

    public static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return round($earth * (2 * atan2(sqrt($a), sqrt(1 - $a))), 2);
    }
}
