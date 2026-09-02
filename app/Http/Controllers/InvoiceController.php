<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Helpers\Helper;

class InvoiceController extends Controller
{
    public function downloadInvoice(Request $request, $id)
    {
        $rideId = $request->query('ride_id');
        $searchId = !empty($rideId) ? $rideId : $id;
        $amount = (float) ($request->query('amount') ?? 0);
        $paymentMethod = $request->query('payment_method') ?? '';
        $isDebit = $request->query('is_debit', '1') === '1';

        // 1. Try to find in service_requests
        $booking = null;
        if (Schema::hasTable('service_requests')) {
            $booking = DB::table('service_requests')->where('id', $searchId)->first();
        }

        // 2. Try to find in tj_requete (rides)
        if (!$booking && Schema::hasTable('tj_requete')) {
            $booking = DB::table('tj_requete')->where('id', $searchId)->first();
        }

        // 3. Try to find in parcel_orders
        if (!$booking && Schema::hasTable('parcel_orders')) {
            $booking = DB::table('parcel_orders')->where('id', $searchId)->first();
        }

        // 4. Try to find in marketplace_orders
        $mpOrder = null;
        if (Schema::hasTable('marketplace_orders')) {
            $mpOrder = DB::table('marketplace_orders')->where('id', $searchId)->first();
        }

        // 5. Try to find in tj_transaction
        $transaction = null;
        if (Schema::hasTable('tj_transaction')) {
            $transaction = DB::table('tj_transaction')->where('id', $id)->first();
        }
        if (!$transaction && Schema::hasTable('tj_conducteur_transaction')) {
            $transaction = DB::table('tj_conducteur_transaction')->where('id', $id)->first();
        }

        if (empty($paymentMethod)) {
            $paymentMethod = $booking->payment_status ?? ($mpOrder->payment_method ?? ($transaction->payment_method ?? 'Wallet'));
        }

        // Resolve title
        $title = $request->query('title');
        if (empty($title)) {
            if ($booking) {
                $title = $booking->service_name ?? $booking->destination_name ?? 'Service Booking';
            } elseif ($mpOrder) {
                $title = 'Marketplace Sale';
            } elseif ($transaction) {
                $title = $transaction->description ?? ($transaction->note ?? 'Wallet Transaction');
            } else {
                $title = 'Fiinway Wallet Transaction';
            }
        }

        // Base amount
        if ($amount <= 0) {
            if ($booking) {
                $amount = (float) ($booking->amount ?? ($booking->montant ?? 0));
            } elseif ($mpOrder) {
                $amount = (float) ($mpOrder->seller_payout_amount ?? ($mpOrder->total_amount ?? 0));
            } elseif ($transaction) {
                $amount = abs((float) ($transaction->amount ?? 0));
            }
        }

        // Resolve user
        $userName = $request->query('user_name');
        $userPhone = 'N/A';
        $userEmail = 'N/A';
        $paidFrom = $request->query('paid_from');
        $paidTo = $request->query('paid_to');
        $driverObj = null;

        if ($booking) {
            $userId = $booking->user_id ?? ($booking->id_user_app ?? 0);
            $userObj = null;
            if (Schema::hasTable('tj_user_app')) {
                $userObj = DB::table('tj_user_app')->where('id', $userId)->first();
            }
            if (!$userObj && Schema::hasTable('users')) {
                $userObj = DB::table('users')->where('id', $userId)->first();
            }
            if ($userObj) {
                if (empty($userName)) {
                    $userName = trim(($userObj->nom ?? '') . ' ' . ($userObj->prenom ?? ''));
                }
                $userPhone = $userObj->phone ?? ($userObj->telephone ?? 'N/A');
                $userEmail = $userObj->email ?? 'N/A';
            }

            $driverId = $booking->driver_id ?? ($booking->id_conducteur ?? 0);
            if (!empty($driverId) && Schema::hasTable('tj_conducteur')) {
                $driverObj = DB::table('tj_conducteur')->where('id', $driverId)->first();
            }
        }

        if (empty($userName) || $userName === 'Customer' || $userName === 'User') {
            $userName = $request->query('user_name', 'Fiinway Member');
        }

        $driverName = $driverObj ? trim(($driverObj->nom ?? '') . ' ' . ($driverObj->prenom ?? '')) : '';
        $driverPhone = $driverObj->phone ?? ($driverObj->telephone ?? '');

        if (empty($paidFrom)) {
            if ($isDebit) {
                $paidFrom = $userName;
            } else {
                if (stripos($title, 'marketplace') !== false) {
                    $paidFrom = 'Marketplace Escrow';
                } elseif (stripos($title, 'referral') !== false) {
                    $paidFrom = 'Fiinway Referral Program';
                } elseif (stripos($title, 'cashback') !== false) {
                    $paidFrom = 'Fiinway Smart Value Rewards';
                } elseif (stripos($title, 'top-up') !== false || stripos($title, 'topup') !== false) {
                    $paidFrom = !empty($paymentMethod) ? $paymentMethod : 'Payment Gateway';
                } elseif (!empty($driverName)) {
                    $paidFrom = $driverName;
                } else {
                    $paidFrom = 'Fiinway Services';
                }
            }
        }

        if (empty($paidTo)) {
            if ($isDebit) {
                if (!empty($driverName)) {
                    $paidTo = $driverName . ' (Service Partner)';
                } elseif (stripos($title, 'withdraw') !== false) {
                    $paidTo = 'Linked Bank Account';
                } else {
                    $paidTo = 'Fiinway Services';
                }
            } else {
                $paidTo = $userName . ' (Wallet)';
            }
        }

        $dateStr = $request->query('date');
        $rawDate = !empty($dateStr) 
            ? $dateStr 
            : ($booking->date_heure ?? ($booking->creer ?? ($mpOrder->created_at ?? ($transaction->creer ?? date('Y-m-d H:i:s')))));
        
        $date = (string)$rawDate;
        if (strtotime((string)$rawDate)) {
            $date = date('d M Y, h:i A', strtotime((string)$rawDate));
        }

        $pmRaw = strtolower(trim((string)$paymentMethod));
        if (str_contains($pmRaw, 'cash')) {
            $paymentMethod = 'Cash Paid';
        } elseif (str_contains($pmRaw, 'wallet')) {
            $paymentMethod = 'Fiinway Wallet';
        } elseif (str_contains($pmRaw, 'upi') || str_contains($pmRaw, 'razorpay')) {
            $paymentMethod = 'UPI / Online';
        }

        $currencySymbol = Helper::getCurrencySymbol();
        $invoiceNo = 'FIIN-' . str_pad((string) $id, 7, '0', STR_PAD_LEFT);

        return view('invoices.invoice', compact(
            'id',
            'invoiceNo',
            'title',
            'amount',
            'userName',
            'userPhone',
            'userEmail',
            'paidFrom',
            'paidTo',
            'driverName',
            'driverPhone',
            'date',
            'paymentMethod',
            'isDebit',
            'currencySymbol'
        ));
    }
}
