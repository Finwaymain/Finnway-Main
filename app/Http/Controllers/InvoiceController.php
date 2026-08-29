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

        // 4. Try to find in tj_transaction
        $transaction = null;
        if (Schema::hasTable('tj_transaction')) {
            $transaction = DB::table('tj_transaction')->where('id', $id)->first();
        }
        if (!$transaction && Schema::hasTable('tj_conducteur_transaction')) {
            $transaction = DB::table('tj_conducteur_transaction')->where('id', $id)->first();
        }

        if (empty($paymentMethod)) {
            $paymentMethod = $booking->payment_status ?? ($transaction->payment_method ?? 'Wallet');
        }

        // Resolve title
        $title = $request->query('title');
        if (empty($title)) {
            if ($booking) {
                $title = $booking->service_name ?? $booking->destination_name ?? 'Service Booking';
            } elseif ($transaction) {
                $title = $transaction->deduction_type ?? $transaction->note ?? 'Wallet Transaction';
            } else {
                $title = 'Fiinway Wallet Transaction';
            }
        }

        // Resolve base amount and tax breakdown
        $baseAmount = 0.0;
        $taxTotal = 0.0;
        $taxList = [];

        if ($booking) {
            $baseAmount = (float) ($booking->amount ?? $booking->montant ?? 0);
            if ($baseAmount <= 0) {
                $baseAmount = (float) $amount;
            }

            // Check if tax column has JSON
            if (!empty($booking->tax)) {
                $decoded = is_string($booking->tax) ? json_decode($booking->tax, true) : $booking->tax;
                if (is_array($decoded)) {
                    foreach ($decoded as $t) {
                        if (is_array($t)) {
                            $tLabel = $t['libelle'] ?? ($t['name'] ?? 'Tax');
                            $tVal = $t['value'] ?? '';
                            $tType = $t['type'] ?? 'Percentage';
                            $tAmt = isset($t['amount']) ? (float) $t['amount'] : 0.0;
                            if ($tAmt <= 0 && is_numeric($tVal) && (float) $tVal > 0) {
                                $tAmt = ($tType === 'Percentage') ? round(($baseAmount * (float)$tVal) / 100, 2) : (float) $tVal;
                            }
                            $taxTotal += $tAmt;
                            $taxList[] = [
                                'label' => $tLabel . ($tType === 'Percentage' && !empty($tVal) ? " ({$tVal}%)" : ''),
                                'amount' => $tAmt,
                            ];
                        }
                    }
                }
            } elseif (!empty($booking->tax_amount) && (float) $booking->tax_amount > 0) {
                $taxTotal = (float) $booking->tax_amount;
                $taxList[] = [
                    'label' => 'Taxes & Service Fees',
                    'amount' => $taxTotal,
                ];
            } else {
                // Check active taxes from tj_tax if booking is paid with taxes
                $pmClean = strtolower(trim((string) $paymentMethod));
                if (Schema::hasTable('tj_tax') && !empty($pmClean) && $pmClean !== 'exempt') {
                    $activeTaxes = DB::table('tj_tax')->where('statut', 'yes')->get();
                    foreach ($activeTaxes as $taxRow) {
                        $appOn = !empty($taxRow->applicable_on) ? explode(',', $taxRow->applicable_on) : ['cash','upi','wallet','online'];
                        $matches = in_array($pmClean, $appOn) || 
                                   ($pmClean === 'upi' && in_array('online', $appOn)) || 
                                   ($pmClean === 'online' && in_array('upi', $appOn)) ||
                                   (str_contains($pmClean, 'cash') && in_array('cash', $appOn)) ||
                                   (str_contains($pmClean, 'wallet') && in_array('wallet', $appOn));
                        if ($matches) {
                            $tVal = (float) ($taxRow->value ?? 0);
                            $tAmt = ($taxRow->type === 'Percentage') ? round(($baseAmount * $tVal) / 100, 2) : $tVal;
                            if ($tAmt > 0) {
                                $taxTotal += $tAmt;
                                $taxList[] = [
                                    'label' => ($taxRow->libelle ?? 'Tax') . ($taxRow->type === 'Percentage' ? " ({$taxRow->value}%)" : ''),
                                    'amount' => $tAmt,
                                ];
                            }
                        }
                    }
                }
            }
        } elseif ($transaction) {
            $totalTxAmount = abs((float) ($transaction->amount ?? 0));
            $baseAmount = $totalTxAmount;
            $taxTotal = 0.0;
        }

        if ($baseAmount <= 0) {
            $baseAmount = (float) $amount;
        }
        $finalTotal = round($baseAmount + $taxTotal, 2);
        if ($amount > 0 && ($amount >= $finalTotal || empty($taxList))) {
            $finalTotal = $amount;
        }

        // Resolve user
        $userName = $request->query('user_name');
        $userPhone = 'N/A';
        $userEmail = 'N/A';

        if ($booking) {
            $userId = $booking->user_id ?? $booking->id_user_app ?? 0;
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
                $userPhone = $userObj->phone ?? $userObj->telephone ?? 'N/A';
                $userEmail = $userObj->email ?? 'N/A';
            }
        }

        if (empty($userName) || $userName === 'Customer' || $userName === 'User') {
            $userName = $request->query('user_name', 'Fiinway Valued Member');
        }

        $dateStr = $request->query('date');
        $rawDate = !empty($dateStr) 
            ? $dateStr 
            : ($booking->date_heure ?? ($booking->creer ?? ($transaction->creer ?? date('Y-m-d H:i:s'))));
        $date = (strtotime((string)$rawDate)) ? date('d M Y, h:i A', strtotime((string)$rawDate)) : (string)$rawDate;

        $paymentMethod = $request->query('payment_method', $booking->payment_status ?? ($transaction->payment_method ?? 'Smart Value Wallet'));
        $pmRaw = strtolower(trim((string)$paymentMethod));
        if (str_contains($pmRaw, 'cash')) {
            $paymentMethod = 'Cash Paid';
        } elseif (str_contains($pmRaw, 'wallet')) {
            $paymentMethod = 'Fiinway Wallet';
        } elseif (str_contains($pmRaw, 'upi') || str_contains($pmRaw, 'razorpay')) {
            $paymentMethod = 'UPI / Online';
        }
        $isDebit = $request->query('is_debit', '1') === '1';

        $currencySymbol = Helper::getCurrencySymbol();
        $invoiceNo = 'FIIN-' . str_pad((string) $id, 7, '0', STR_PAD_LEFT);

        return view('invoices.invoice', compact(
            'id',
            'invoiceNo',
            'booking',
            'transaction',
            'title',
            'baseAmount',
            'taxList',
            'taxTotal',
            'finalTotal',
            'amount',
            'userName',
            'userPhone',
            'userEmail',
            'date',
            'paymentMethod',
            'isDebit',
            'currencySymbol'
        ));
    }
}
