<?php

namespace App\Http\Controllers\API\v1\Food;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodDispute;
use App\Models\Food\FoodDuePayment;
use App\Models\Food\FoodNotification;
use App\Models\Food\FoodOffer;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodRestaurant;
use App\Models\Food\FoodReview;
use App\Models\Food\FoodSettlement;
use App\Models\Food\FoodSupportTicket;
use App\Models\Food\FoodTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RestaurantFinanceController extends Controller
{
    protected function restaurant(Request $request): ?FoodRestaurant
    {
        $owner = $request->attributes->get('food_owner');
        return FoodRestaurant::where('owner_id', $owner->id)->orderByDesc('id')->first();
    }

    public function sales(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $from = $request->get('from', now()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $q = FoodOrder::where('restaurant_id', $restaurant->id)
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);
        return response()->json([
            'success' => true,
            'data' => [
                'from' => $from,
                'to' => $to,
                'orders' => (clone $q)->count(),
                'gross_sales' => (float) (clone $q)->sum('food_amount'),
                'customer_paid' => (float) (clone $q)->sum('customer_payable'),
                'commission' => (float) (clone $q)->sum('commission_amount'),
                'platform_charges' => (float) (clone $q)->sum('platform_charges'),
                'net_earnings' => (float) (clone $q)->sum('restaurant_net_amount'),
                'delivered' => (clone $q)->whereIn('order_status', ['delivered', 'completed'])->count(),
                'cancelled' => (clone $q)->whereIn('order_status', ['cancelled', 'rejected'])->count(),
            ],
        ]);
    }

    public function dues(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $dues = FoodDuePayment::where('restaurant_id', $restaurant->id)->orderByDesc('id')->get();
        $pending = $dues->where('status', '!=', 'paid')->sum(fn ($d) => $d->amount - $d->paid_amount);
        return response()->json([
            'success' => true,
            'data' => [
                'pending_due' => round($pending, 2),
                'items' => $dues,
            ],
        ]);
    }

    public function payDue(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $due = FoodDuePayment::where('restaurant_id', $restaurant->id)->where('id', $request->get('due_id'))->first();
        if (!$due) {
            return response()->json(['success' => false, 'error' => 'Due not found.']);
        }
        $amount = (float) $request->get('amount', $due->amount - $due->paid_amount);
        $due->paid_amount = min($due->amount, $due->paid_amount + $amount);
        $due->status = $due->paid_amount >= $due->amount ? 'paid' : 'partial';
        $due->payment_ref = $request->get('payment_ref', 'DUE_' . time());
        if ($due->status === 'paid') {
            $due->paid_at = now();
        }
        $due->save();

        FoodTransaction::create([
            'txn_number' => 'TXN' . time() . $restaurant->id,
            'restaurant_id' => $restaurant->id,
            'order_id' => $due->order_id,
            'txn_type' => 'due_payment',
            'amount' => $amount,
            'payment_method' => $request->get('payment_method', 'upi'),
            'status' => 'paid',
            'gateway_ref' => $due->payment_ref,
            'notes' => 'Company due payment',
        ]);

        return response()->json(['success' => true, 'data' => $due]);
    }

    public function settlements(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $rows = FoodSettlement::where('restaurant_id', $restaurant->id)->orderByDesc('id')->paginate(20);
        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function transactions(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $rows = FoodTransaction::where('restaurant_id', $restaurant->id)->orderByDesc('id')->paginate(20);
        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function offers(Request $request)
    {
        $restaurant = $this->restaurant($request);
        return response()->json([
            'success' => true,
            'data' => FoodOffer::where('restaurant_id', $restaurant->id)->orderByDesc('id')->get(),
        ]);
    }

    public function saveOffer(Request $request, $id = null)
    {
        $restaurant = $this->restaurant($request);
        $offer = $id
            ? FoodOffer::where('restaurant_id', $restaurant->id)->where('id', $id)->first()
            : new FoodOffer(['restaurant_id' => $restaurant->id]);
        if (!$offer) {
            return response()->json(['success' => false, 'error' => 'Offer not found.']);
        }
        $offer->fill([
            'restaurant_id' => $restaurant->id,
            'name' => $request->get('name', $offer->name),
            'discount_type' => $request->get('discount_type', $offer->discount_type ?? 'percentage'),
            'discount_value' => (float) $request->get('discount_value', $offer->discount_value ?? 0),
            'min_order' => $request->get('min_order', $offer->min_order),
            'max_discount' => $request->get('max_discount', $offer->max_discount),
            'starts_at' => $request->get('starts_at', $offer->starts_at),
            'ends_at' => $request->get('ends_at', $offer->ends_at),
            'status' => $request->get('status', $offer->status ?? 'draft'),
        ]);
        $offer->save();
        return response()->json(['success' => true, 'data' => $offer]);
    }

    public function reviews(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $rows = FoodReview::where('restaurant_id', $restaurant->id)->orderByDesc('id')->paginate(20);
        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function replyReview(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);
        $review = FoodReview::where('restaurant_id', $restaurant->id)->where('id', $id)->first();
        if (!$review) {
            return response()->json(['success' => false, 'error' => 'Review not found.']);
        }
        $review->restaurant_reply = $request->get('reply');
        $review->replied_at = now();
        $review->save();
        return response()->json(['success' => true, 'data' => $review]);
    }

    public function disputes(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $rows = FoodDispute::where('restaurant_id', $restaurant->id)->orderByDesc('id')->get();
        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function raiseDispute(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $dispute = FoodDispute::create([
            'ticket_number' => 'DIS' . time(),
            'order_id' => $request->get('order_id'),
            'restaurant_id' => $restaurant->id,
            'raised_by' => 'restaurant',
            'issue_type' => $request->get('issue_type', 'other'),
            'description' => $request->get('description'),
            'priority' => $request->get('priority', 'medium'),
            'status' => 'open',
        ]);
        return response()->json(['success' => true, 'data' => $dispute]);
    }

    public function tickets(Request $request)
    {
        $owner = $request->attributes->get('food_owner');
        $rows = FoodSupportTicket::where('owner_id', $owner->id)->orderByDesc('id')->get();
        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function createTicket(Request $request)
    {
        $owner = $request->attributes->get('food_owner');
        $restaurant = $this->restaurant($request);
        $ticket = FoodSupportTicket::create([
            'ticket_number' => 'SUP' . time(),
            'restaurant_id' => $restaurant?->id,
            'owner_id' => $owner->id,
            'category' => $request->get('category'),
            'subject' => $request->get('subject'),
            'message' => $request->get('message'),
            'priority' => $request->get('priority', 'medium'),
            'status' => 'open',
        ]);
        return response()->json(['success' => true, 'data' => $ticket]);
    }

    public function notifications(Request $request)
    {
        $owner = $request->attributes->get('food_owner');
        $rows = FoodNotification::where('owner_id', $owner->id)->orderByDesc('id')->limit(100)->get();
        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function markNotificationRead(Request $request, $id)
    {
        $owner = $request->attributes->get('food_owner');
        $n = FoodNotification::where('owner_id', $owner->id)->where('id', $id)->first();
        if ($n) {
            $n->is_read = true;
            $n->save();
        }
        return response()->json(['success' => true, 'data' => $n]);
    }
}
