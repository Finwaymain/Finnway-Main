<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function downloadInvoice($bookingId)
    {
        // Fetch booking from service_requests or tj_requete or marketplace_orders
        $booking = DB::table('service_requests')->where('id', $bookingId)->first();
        if (!$booking) {
            $booking = DB::table('tj_requete')->where('id', $bookingId)->first();
        }

        if (!$booking) {
            abort(404, 'Booking or invoice not found.');
        }

        $user = DB::table('users')->where('id', $booking->user_id ?? $booking->id_user_app ?? 0)->first();
        $currency = DB::table('tj_currency')->where('statut', 'yes')->first();

        return view('invoices.invoice', compact('booking', 'user', 'currency'));
    }
}
