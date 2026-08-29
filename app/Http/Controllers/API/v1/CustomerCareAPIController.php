<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerCareAPIController extends Controller
{
    /**
     * GET /api/v1/customer-care
     * Returns customer care WhatsApp & Calling numbers for Business App and Customer App.
     */
    public function getCustomerCare(Request $request)
    {
        $settings = DB::table('tj_settings')->first();

        $businessWhatsapp = !empty($settings->business_whatsapp_number) ? $settings->business_whatsapp_number : '9429693669';
        $businessCall     = !empty($settings->business_call_number)     ? $settings->business_call_number     : '9429693669';
        $customerWhatsapp = !empty($settings->customer_whatsapp_number) ? $settings->customer_whatsapp_number : '9429693669';
        $customerCall     = !empty($settings->customer_call_number)     ? $settings->customer_call_number     : '9429693669';

        return response()->json([
            'success' => 'success',
            'data' => [
                'business_app' => [
                    'whatsapp_number' => $businessWhatsapp,
                    'call_number'     => $businessCall,
                ],
                'customer_app' => [
                    'whatsapp_number' => $customerWhatsapp,
                    'call_number'     => $customerCall,
                ],
            ],
        ]);
    }
}
