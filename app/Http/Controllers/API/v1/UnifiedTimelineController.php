<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnifiedTimelineController extends Controller
{
    public function getTimeline(Request $request)
    {
        $userId = $request->input('user_id');
        $tab = $request->input('tab', 'all'); // all, services, wallet, orders, subscription, cards

        $timeline = [];

        // 1. Wallet Transactions
        if (in_array($tab, ['all', 'wallet'])) {
            $walletItems = DB::table('tbl_earning')
                ->where('user_id', $userId)
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
                ->map(function ($item) {
                    return [
                        'id'          => 'W-' . $item->id,
                        'type'        => 'wallet',
                        'title'       => ucfirst(str_replace('_', ' ', $item->type)),
                        'amount'      => floatval($item->amount),
                        'status'      => 'completed',
                        'created_at'  => $item->created_at,
                        'invoice_url' => null,
                    ];
                });
            $timeline = array_merge($timeline, $walletItems->toArray());
        }

        // 2. Service Bookings
        if (in_array($tab, ['all', 'services'])) {
            $services = DB::table('service_requests')
                ->where('user_id', $userId)
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
                ->map(function ($item) {
                    return [
                        'id'          => 'S-' . $item->id,
                        'type'        => 'service',
                        'title'       => $item->service_name ?? 'Home Service',
                        'amount'      => floatval($item->amount ?? 0),
                        'status'      => $item->status ?? 'pending',
                        'created_at'  => $item->created_at,
                        'invoice_url' => url("/invoice/{$item->id}/download"),
                    ];
                });
            $timeline = array_merge($timeline, $services->toArray());
        }

        // Sort combined timeline chronologically
        usort($timeline, function ($a, $b) {
            return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
        });

        return response()->json([
            'success' => true,
            'tab'     => $tab,
            'data'    => $timeline,
        ]);
    }
}
