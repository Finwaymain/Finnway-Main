<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerCareController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $settings = Settings::first();
        if (!$settings) {
            $settings = Settings::create([
                'title' => 'Fiinway',
                'business_whatsapp_number' => '9429693669',
                'business_call_number' => '9429693669',
                'customer_whatsapp_number' => '9429693669',
                'customer_call_number' => '9429693669',
            ]);
        } else {
            // Apply fallback defaults if null or empty string
            $updated = false;
            if (empty($settings->business_whatsapp_number)) {
                $settings->business_whatsapp_number = '9429693669';
                $updated = true;
            }
            if (empty($settings->business_call_number)) {
                $settings->business_call_number = '9429693669';
                $updated = true;
            }
            if (empty($settings->customer_whatsapp_number)) {
                $settings->customer_whatsapp_number = '9429693669';
                $updated = true;
            }
            if (empty($settings->customer_call_number)) {
                $settings->customer_call_number = '9429693669';
                $updated = true;
            }
            if ($updated) {
                $settings->save();
            }
        }

        return view('customer_care.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_whatsapp_number' => 'required|string|max:30',
            'business_call_number'     => 'required|string|max:30',
            'customer_whatsapp_number' => 'required|string|max:30',
            'customer_call_number'     => 'required|string|max:30',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $settings = Settings::first();
        if (!$settings) {
            $settings = new Settings();
        }

        $settings->business_whatsapp_number = trim($request->input('business_whatsapp_number'));
        $settings->business_call_number     = trim($request->input('business_call_number'));
        $settings->customer_whatsapp_number = trim($request->input('customer_whatsapp_number'));
        $settings->customer_call_number     = trim($request->input('customer_call_number'));
        $settings->save();

        return redirect()->route('customer-care.index')->with('success', 'Customer Care numbers updated successfully!');
    }
}
