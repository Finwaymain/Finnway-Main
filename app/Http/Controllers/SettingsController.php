<?php

namespace App\Http\Controllers;

use App\Models\PaymentSettings;
use App\Models\Settings;
use App\Models\Currency;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;


class SettingsController extends Controller
{

    public function __construct()
    {

        $this->middleware('auth');

    }


    public function index()
    {

        $settings = Settings::paginate(1);
        $currency=Currency::where('statut','yes')->first();
        return view("administration_tools.settings.index")->with("settings", $settings)->with('currency',$currency);

    }

    public function getSettings()
    {
        return '#4f46e5';
    }

    public function edit($id)
    {

        $settings = Settings::find($id);
        return view("settings.edit")->with('settings', $settings);

    }

    public function update(Request $request, $id)
    {

		$validator = Validator::make($request->all(),[
            'title' => 'required',
            'footer' => 'required',
            'driver_radios' => 'required',
            'map_key' => 'required',
            'referral_amount'=>'required',
            'minimum_deposit_amount'=>'required',
            'minimum_withdrawal_amount'=>'required',
            'driver_location_update'=>'required',
            'map_type'=>'required',
            'senderId'=>'required|integer',
            'serviceJson'=> Rule::requiredIf(!Storage::disk('local')->has('firebase/credentials.json')),
            'map_for_app'=>'required'
        ]);

        $validator->setCustomMessages([
            'serviceJson.required' => 'The credentials file field is required.',
            'serviceJson.file|mimetypes:application/json' => 'The credentials file must be a file of type: application/json.',
        ]);

        if($validator->fails()){
            return redirect('administration_tools/settings')->withErrors($validator)->withInput();
        }

    	$title = $request->input('title');
        $footer = $request->input('footer');
        $email = $request->input('email');
        $appcolor = $request->input('website_color');
        $adminpanelcolor = $request->input('adminpanel_color');
        $driverappcolor = $request->input('driverapp_color');
        $api_key = $request->input('map_key');
        $driver_radios = $request->input('driver_radios');
        $trip_accept_reject_by_driver = $request->input('trip_accept_reject_by_driver');
        $is_social_media = $request->is_social_media;
        $user_schedule_time = $request->input('user_schedule_time');
        $show_ride = $request->input('show_ride');
        $show_ride_later = $request->input('show_ride_later');
        $show_ride_otp = $request->input('show_ride_otp');
        $delivery_distance = $request->input('delivery_distance');
        $contact_us_address = $request->input('contact_us_address');
        $app_version = $request->input('app_version');
        $web_version = $request->input('web_version');
        $contact_us_phone = $request->input('contact_us_phone');
        $contact_us_email=$request->input('contact_us_email');
        $minimum_deposit_amount=$request->input('minimum_deposit_amount');
        $minimum_withdrawal_amount=$request->input('minimum_withdrawal_amount');
        $referral_amount=$request->input('referral_amount');
        $map_type = $request->input('map_type');
        $map_for_app = $request->input('map_for_app');
        $driver_location_update = $request->input('driver_location_update');
        $delivery_charge_parcel = $request->input('delivery_charge_parcel');
        $parcel_active = $request->has('parcel_active') ? "yes" : "no";
        $parcel_per_weight_charge=$request->input('parcel_per_weight_charge');
        $parcel_per_height_charge=$request->input('parcel_per_height_charge');
        $home_screen_type = $request->input('home_screen_type');

        $modifier = date('Y-m-d H:i:s');
        $senderId=$request->input('senderId');
        
        $settings = Settings::find($id);

        if ($settings) {
            $settings->title = $title;
            $settings->footer = $footer;
            $settings->email = $email;
            $settings->website_color = $appcolor;
            $settings->adminpanel_color = $adminpanelcolor;
            $settings->driverapp_color = $driverappcolor;
            $settings->google_map_api_key = $api_key;
            $settings->is_social_media = $is_social_media;
            $settings->driver_radios = $driver_radios;
            $settings->user_ride_schedule_time_minute = $user_schedule_time;
            $settings->trip_accept_reject_driver_time_sec = $trip_accept_reject_by_driver;
            $settings->show_ride_without_destination = $show_ride;
            $settings->show_ride_otp = $show_ride_otp;
            $settings->show_ride_later = $show_ride_later;
            $settings->modifier = $modifier;
            $settings->delivery_distance = $delivery_distance;
            $settings->contact_us_address = $contact_us_address;
            $settings->contact_us_phone = $contact_us_phone;
            $settings->contact_us_email = $contact_us_email;
            $settings->app_version = $app_version;
            $settings->web_version = $web_version;
            $settings->minimum_deposit_amount=$minimum_deposit_amount;
            $settings->minimum_withdrawal_amount=$minimum_withdrawal_amount;
            $settings->referral_amount=$referral_amount;
            $settings->mapType = $map_type;
            $settings->driverLocationUpdate = $driver_location_update;
            $settings->delivery_charge_parcel = $delivery_charge_parcel;
            $settings->parcel_active = $parcel_active;
            $settings->parcel_per_weight_charge = $parcel_per_weight_charge;
            $settings->parcel_per_height_charge = $parcel_per_height_charge;
            $settings->senderId = $senderId;
            $settings->map_for_application=$map_for_app;
            $settings->home_screen_type = $home_screen_type;

        }

		if($request->hasfile('app_logo')){
			$destination = public_path('assets/images/app_logo.png');
           	if(File::exists($destination)) {
                File::delete($destination);
            }
            $file = $request->file('app_logo');
            $filename = 'app_logo.png';
            $file->move(public_path('assets/images/'), $filename);
            $image = str_replace('data:image/png;base64,', '', $file);
            $settings->app_logo = $filename;
        }

		if($request->hasfile('app_logo_small')){
			$destination = public_path('assets/images/app_logo_small.png');
           	if(File::exists($destination)) {
                File::delete($destination);
            }
            $file = $request->file('app_logo_small');
            $filename = 'app_logo_small.png';
            $file->move(public_path('assets/images/'), $filename);
            $image = str_replace('data:image/png;base64,', '', $file);
            $settings->app_logo_small = $filename;
        }

        if($request->hasfile('serviceJson')){
			$destination = storage_path('app/firebase/credentials.json');
           	if(File::exists($destination)) {
                File::delete($destination);
            }
            $file = $request->file('serviceJson');
            $file->move(storage_path('app/firebase/'),'credentials.json');
            $settings->serviceJson = asset('storage/app/firebase/credentials.json');
        }

        $settings->save();

		return redirect()->back();
    }
   
    public function cod()
    {
        $stripe = PaymentSettings::where('id_payment_method', 10)->get();
        $razorpay = PaymentSettings::where('id_payment_method', 13)->get();
        $cods = PaymentSettings::where('id_payment_method', 5)->get();
        $paypal = PaymentSettings::where('id_payment_method', 15)->get();
        $payfast = PaymentSettings::where('id_payment_method', 7)->get();
        $paystack = PaymentSettings::where('id_payment_method', 11)->get();
        $flutterwave = PaymentSettings::where('id_payment_method', 12)->get();
        $wallet = PaymentSettings::where('id_payment_method', 9)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $applePay = PaymentSettings::where('id_payment_method', 17)->get();
        $xendit = PaymentSettings::where('id_payment_method', 20)->get();
        $orangepay = PaymentSettings::where('id_payment_method', 19)->get();
        $midtrans = PaymentSettings::where('id_payment_method', 18)->get();

        return view('settings.app.cod')->with('stripe', $stripe)
            ->with('razorpay', $razorpay)->with('cods', $cods)
            ->with('payfast', $payfast)->with('paystack', $paystack)
            ->with('paypal', $paypal)
            ->with('flutterwave', $flutterwave)->with('wallet', $wallet)
            ->with('applePay', $applePay)->with('mercadopago', $mercadopago)
            ->with('xendit', $xendit)->with('midtrans', $midtrans)
            ->with('orangepay', $orangepay);
    }

    public function codUpdate(Request $request, $id)
    {
        $isEnabled = $request->isEnabled;

        $modifier = date('Y-m-d H:i:s');

        $settings = PaymentSettings::find($id);

        if ($settings) {
            $settings->isEnabled = $isEnabled;
            $settings->modifier = $modifier;

        }
        $settings->save();
    }

    public function applePay()
    {
        $stripe = PaymentSettings::where('id_payment_method', 10)->get();
        $razorpay = PaymentSettings::where('id_payment_method', 13)->get();
        $cods = PaymentSettings::where('id_payment_method', 5)->get();
        $paypal = PaymentSettings::where('id_payment_method', 15)->get();
        $payfast = PaymentSettings::where('id_payment_method', 7)->get();
        $paystack = PaymentSettings::where('id_payment_method', 11)->get();
        $flutterwave = PaymentSettings::where('id_payment_method', 12)->get();
        $wallet = PaymentSettings::where('id_payment_method', 9)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $applePay = PaymentSettings::where('id_payment_method', 17)->get();
        $xendit = PaymentSettings::where('id_payment_method', 20)->get();
        $orangepay = PaymentSettings::where('id_payment_method', 19)->get();
        $midtrans = PaymentSettings::where('id_payment_method', 18)->get();

        return view('settings.app.applepay')->with('stripe', $stripe)
            ->with('razorpay', $razorpay)->with('cods', $cods)
            ->with('payfast', $payfast)->with('paystack', $paystack)
            ->with('paypal', $paypal)
            ->with('flutterwave', $flutterwave)->with('wallet', $wallet)
            ->with('applePay', $applePay)->with('mercadopago', $mercadopago)
            ->with('xendit', $xendit)->with('midtrans', $midtrans)
            ->with('orangepay', $orangepay);
    }
    public function applepayUpdate(Request $request,$id)  {
        $isEnabled = $request->isEnabled;
        $merchantId = $request->merchantId;
        $secretKey = $request->secretKey;
        $modifier = date('Y-m-d H:i:s');

        $settings = PaymentSettings::find($id);

        if ($settings) {
            $settings->isEnabled = $isEnabled;
            $settings->merchant_Id = $merchantId;
            $settings->secret_key = $secretKey;
            $settings->modifier = $modifier;

        }
        $settings->save();

    }

    public function stripe()
    {

        $stripe = PaymentSettings::where('id_payment_method', 10)->get();
        $razorpay = PaymentSettings::where('id_payment_method', 13)->get();
        $cods = PaymentSettings::where('id_payment_method', 5)->get();
        $paypal = PaymentSettings::where('id_payment_method', 15)->get();
        $payfast = PaymentSettings::where('id_payment_method', 7)->get();
        $paystack = PaymentSettings::where('id_payment_method', 11)->get();
        $flutterwave = PaymentSettings::where('id_payment_method', 12)->get();
        $wallet = PaymentSettings::where('id_payment_method', 9)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $applePay = PaymentSettings::where('id_payment_method', 17)->get();
        $xendit = PaymentSettings::where('id_payment_method', 20)->get();
        $orangepay = PaymentSettings::where('id_payment_method', 19)->get();
        $midtrans = PaymentSettings::where('id_payment_method', 18)->get();

        return view('settings.app.stripe')->with('stripe', $stripe)
            ->with('razorpay', $razorpay)->with('cods', $cods)
            ->with('payfast', $payfast)->with('paystack', $paystack)
            ->with('paypal', $paypal)
            ->with('flutterwave', $flutterwave)->with('wallet', $wallet)
            ->with('applePay', $applePay)->with('mercadopago', $mercadopago)
            ->with('xendit', $xendit)->with('midtrans', $midtrans)
            ->with('orangepay', $orangepay);
    }

    public function stripeUpdate(Request $request, $id)
    {
        $isEnabled = $request->isEnabled;
        $stripekey = $request->stripekey;
        $stripesecret = $request->stripeSecret;
        $modifier = date('Y-m-d H:i:s');

        $settings = PaymentSettings::find($id);

        if ($settings) {
            $settings->isEnabled = $isEnabled;
            $settings->key = $stripekey;
            $settings->secret_key = $stripesecret;
            $settings->modifier = $modifier;

        }
        $settings->save();
    }

    public function razorpay()
    {
        $stripe = PaymentSettings::where('id_payment_method', 10)->get();
        $razorpay = PaymentSettings::where('id_payment_method', 13)->get();
        $cods = PaymentSettings::where('id_payment_method', 5)->get();
        $paypal = PaymentSettings::where('id_payment_method', 15)->get();
        $payfast = PaymentSettings::where('id_payment_method', 7)->get();
        $paystack = PaymentSettings::where('id_payment_method', 11)->get();
        $flutterwave = PaymentSettings::where('id_payment_method', 12)->get();
        $wallet = PaymentSettings::where('id_payment_method', 9)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $applePay = PaymentSettings::where('id_payment_method', 17)->get();
        $xendit = PaymentSettings::where('id_payment_method', 20)->get();
        $orangepay = PaymentSettings::where('id_payment_method', 19)->get();
        $midtrans = PaymentSettings::where('id_payment_method', 18)->get();

        return view('settings.app.razorpay')->with('stripe', $stripe)
            ->with('razorpay', $razorpay)->with('cods', $cods)
            ->with('payfast', $payfast)->with('paystack', $paystack)
            ->with('paypal', $paypal)
            ->with('flutterwave', $flutterwave)->with('wallet', $wallet)
            ->with('applePay', $applePay)->with('mercadopago', $mercadopago)
            ->with('xendit', $xendit)->with('midtrans', $midtrans)
            ->with('orangepay', $orangepay);
    }

    public function razorpayUpdate(Request $request, $id)
    {
        $isRazorpayenabled = $request->isRazorpayenabled;
        $razorpayKey = $request->razorpayKey;
        $razorpaySecret = $request->razorpaySecret;
        $sendboxmode = $request->sendboxmode;
        $modifier = date('Y-m-d H:i:s');

        $settings = PaymentSettings::find($id);

        if ($settings) {
            $settings->isEnabled = $isRazorpayenabled;
            $settings->key = $razorpayKey;
            $settings->secret_key = $razorpaySecret;
            $settings->isSandboxEnabled = $sendboxmode;
            $settings->modifier = $modifier;

            $settings->save();

            \App\Helpers\RazorpayConfig::syncToApiKeySettings(
                (string) $razorpayKey,
                (string) $razorpaySecret,
                $isRazorpayenabled === 'true',
                $sendboxmode === 'true'
            );
        }
    }


   


    public function paypal()
    {
        $stripe = PaymentSettings::where('id_payment_method', 10)->get();
        $razorpay = PaymentSettings::where('id_payment_method', 13)->get();
        $cods = PaymentSettings::where('id_payment_method', 5)->get();
        $paypal = PaymentSettings::where('id_payment_method', 15)->get();
        $payfast = PaymentSettings::where('id_payment_method', 7)->get();
        $paystack = PaymentSettings::where('id_payment_method', 11)->get();
        $flutterwave = PaymentSettings::where('id_payment_method', 12)->get();
        $wallet = PaymentSettings::where('id_payment_method', 9)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $applePay = PaymentSettings::where('id_payment_method', 17)->get();
        $xendit = PaymentSettings::where('id_payment_method', 20)->get();
        $orangepay = PaymentSettings::where('id_payment_method', 19)->get();
        $midtrans = PaymentSettings::where('id_payment_method', 18)->get();

        return view('settings.app.paypal')->with('stripe', $stripe)
            ->with('razorpay', $razorpay)->with('cods', $cods)
            ->with('payfast', $payfast)->with('paystack', $paystack)
            ->with('paypal', $paypal)
            ->with('flutterwave', $flutterwave)->with('wallet', $wallet)
            ->with('applePay', $applePay)->with('mercadopago', $mercadopago)
            ->with('xendit', $xendit)->with('midtrans', $midtrans)
            ->with('orangepay', $orangepay);
    }

    public function paypalUpdate(Request $request, $id)
    {
        $isEnabled = $request->isEnabled;
        $isLive = $request->isLive;
        $app_id = $request->app_id;
        $secret_key = $request->secret_key;
        $username = $request->username;
        $password = $request->password;
        $modifier = date('Y-m-d H:i:s');

        $settings = PaymentSettings::find($id);
        if ($settings) {
            $settings->isEnabled = $isEnabled;
            $settings->isLive = $isLive;
            $settings->app_id = $app_id;
            $settings->secret_key = $secret_key;
            $settings->username = $username;
            $settings->password = $password;
            $settings->modifier = $modifier;

        }
        $settings->save();
    }

    public function payfast()
    {
        $stripe = PaymentSettings::where('id_payment_method', 10)->get();
        $razorpay = PaymentSettings::where('id_payment_method', 13)->get();
        $cods = PaymentSettings::where('id_payment_method', 5)->get();
        $paypal = PaymentSettings::where('id_payment_method', 15)->get();
        $payfast = PaymentSettings::where('id_payment_method', 7)->get();
        $paystack = PaymentSettings::where('id_payment_method', 11)->get();
        $flutterwave = PaymentSettings::where('id_payment_method', 12)->get();
        $wallet = PaymentSettings::where('id_payment_method', 9)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $applePay = PaymentSettings::where('id_payment_method', 17)->get();
        $xendit = PaymentSettings::where('id_payment_method', 20)->get();
        $orangepay = PaymentSettings::where('id_payment_method', 19)->get();
        $midtrans = PaymentSettings::where('id_payment_method', 18)->get();

        return view('settings.app.payfast')->with('stripe', $stripe)
            ->with('razorpay', $razorpay)->with('cods', $cods)
            ->with('payfast', $payfast)->with('paystack', $paystack)
            ->with('paypal', $paypal)
            ->with('flutterwave', $flutterwave)->with('wallet', $wallet)
            ->with('applePay', $applePay)->with('mercadopago', $mercadopago)
            ->with('xendit', $xendit)->with('midtrans', $midtrans)
            ->with('orangepay', $orangepay);
    }

    public function payfastUpdate(Request $request, $id)
    {
        $merchant_Id = $request->merchant_Id;
        $merchant_key = $request->merchant_key;
        $cancel_url = $request->cancel_url;
        $notify_url = $request->notify_url;
        $return_url = $request->return_url;
        $isEnabled = $request->isEnabled;
        $isSandboxEnabled = $request->isSandboxEnabled;
        $modifier = date('Y-m-d H:i:s');

        $settings = PaymentSettings::find($id);
        
        if ($settings) {
            $settings->merchant_Id = $merchant_Id;
            $settings->merchant_key = $merchant_key;
            $settings->cancel_url = $cancel_url;
            $settings->notify_url = $notify_url;
            $settings->return_url = $return_url;
            $settings->isEnabled = $isEnabled;
            $settings->isSandboxEnabled = $isSandboxEnabled;
            $settings->modifier = $modifier;

        }
        $settings->save();

    }

    public function paystack()
    {
        $stripe = PaymentSettings::where('id_payment_method', 10)->get();
        $razorpay = PaymentSettings::where('id_payment_method', 13)->get();
        $cods = PaymentSettings::where('id_payment_method', 5)->get();
        $paypal = PaymentSettings::where('id_payment_method', 15)->get();
        $payfast = PaymentSettings::where('id_payment_method', 7)->get();
        $paystack = PaymentSettings::where('id_payment_method', 11)->get();
        $flutterwave = PaymentSettings::where('id_payment_method', 12)->get();
        $wallet = PaymentSettings::where('id_payment_method', 9)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $applePay = PaymentSettings::where('id_payment_method', 17)->get();
        $xendit = PaymentSettings::where('id_payment_method', 20)->get();
        $orangepay = PaymentSettings::where('id_payment_method', 19)->get();
        $midtrans = PaymentSettings::where('id_payment_method', 18)->get();

        return view('settings.app.paystack')->with('stripe', $stripe)
            ->with('razorpay', $razorpay)->with('cods', $cods)
            ->with('payfast', $payfast)->with('paystack', $paystack)
            ->with('paypal', $paypal)
            ->with('flutterwave', $flutterwave)->with('wallet', $wallet)
            ->with('applePay', $applePay)->with('mercadopago', $mercadopago)
            ->with('xendit', $xendit)->with('midtrans', $midtrans)
            ->with('orangepay', $orangepay);
    }

    public function paystackUpdate(Request $request, $id)
    {
        $secret_key = $request->secret_key;
        $public_key = $request->public_key;
        $callback_url = $request->callback_url;
        $webhook_url = $request->webhook_url;
        $isEnabled = $request->isEnabled;
        $isSandboxEnabled = $request->isSandboxEnabled;
        $modifier = date('Y-m-d H:i:s');

        $settings = PaymentSettings::find($id);
        
        if ($settings) {
            $settings->secret_key = $secret_key;
            $settings->public_key = $public_key;
            $settings->callback_url = $callback_url;
            $settings->webhook_url = $webhook_url;
            $settings->isEnabled = $isEnabled;
            $settings->isSandboxEnabled = $isSandboxEnabled;
            $settings->modifier = $modifier;

        }
        $settings->save();

    }

    public function flutterwave()
    {
        $stripe = PaymentSettings::where('id_payment_method', 10)->get();
        $razorpay = PaymentSettings::where('id_payment_method', 13)->get();
        $cods = PaymentSettings::where('id_payment_method', 5)->get();
        $paypal = PaymentSettings::where('id_payment_method', 15)->get();
        $payfast = PaymentSettings::where('id_payment_method', 7)->get();
        $paystack = PaymentSettings::where('id_payment_method', 11)->get();
        $flutterwave = PaymentSettings::where('id_payment_method', 12)->get();
        $wallet = PaymentSettings::where('id_payment_method', 9)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $applePay = PaymentSettings::where('id_payment_method', 17)->get();
        $xendit = PaymentSettings::where('id_payment_method', 20)->get();
        $orangepay = PaymentSettings::where('id_payment_method', 19)->get();
        $midtrans = PaymentSettings::where('id_payment_method', 18)->get();

        return view('settings.app.flutterwave')->with('stripe', $stripe)
            ->with('razorpay', $razorpay)->with('cods', $cods)
            ->with('payfast', $payfast)->with('paystack', $paystack)
            ->with('paypal', $paypal)
            ->with('flutterwave', $flutterwave)->with('wallet', $wallet)
            ->with('applePay', $applePay)->with('mercadopago', $mercadopago)
            ->with('xendit', $xendit)->with('midtrans', $midtrans)
            ->with('orangepay', $orangepay);
    }

    public function flutterUpdate(Request $request, $id)
    {
        $secret_key = $request->secret_key;
        $public_key = $request->public_key;
        $encryption_key = $request->encryption_key;
        $isEnabled = $request->isEnabled;
        $isSandboxEnabled = $request->issandboxEnabled;
        $modifier = date('Y-m-d H:i:s');

        $settings = PaymentSettings::find($id);
        if ($settings) {
            $settings->secret_key = $secret_key;
            $settings->public_key = $public_key;
            $settings->encryption_key = $encryption_key;
            $settings->isEnabled = $isEnabled;
            $settings->isSandboxEnabled = $isSandboxEnabled;
            $settings->modifier = $modifier;

        }
        $settings->save();
    }

    public function wallet()
    {
        $stripe = PaymentSettings::where('id_payment_method', 10)->get();
        $razorpay = PaymentSettings::where('id_payment_method', 13)->get();
        $cods = PaymentSettings::where('id_payment_method', 5)->get();
        $paypal = PaymentSettings::where('id_payment_method', 15)->get();
        $payfast = PaymentSettings::where('id_payment_method', 7)->get();
        $paystack = PaymentSettings::where('id_payment_method', 11)->get();
        $flutterwave = PaymentSettings::where('id_payment_method', 12)->get();
        $wallet = PaymentSettings::where('id_payment_method', 9)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $applePay = PaymentSettings::where('id_payment_method', 17)->get();
        $xendit = PaymentSettings::where('id_payment_method', 20)->get();
        $orangepay = PaymentSettings::where('id_payment_method', 19)->get();
        $midtrans = PaymentSettings::where('id_payment_method', 18)->get();

        return view('settings.app.wallet')->with('stripe', $stripe)
            ->with('razorpay', $razorpay)->with('cods', $cods)
            ->with('payfast', $payfast)->with('paystack', $paystack)
            ->with('paypal', $paypal)
            ->with('flutterwave', $flutterwave)->with('wallet', $wallet)
            ->with('applePay', $applePay)->with('mercadopago', $mercadopago)
            ->with('xendit', $xendit)->with('midtrans', $midtrans)
            ->with('orangepay', $orangepay);
    }

    public function walletUpdate(Request $request, $id)
    {
        $isEnabled = $request->isEnabled;

        $modifier = date('Y-m-d H:i:s');

        $settings = PaymentSettings::find($id);

        if ($settings) {
            $settings->isEnabled = $isEnabled;
            $settings->modifier = $modifier;

        }
        $settings->save();
    }

    public function mercadopago()
    {
        $stripe = PaymentSettings::where('id_payment_method', 10)->get();
        $razorpay = PaymentSettings::where('id_payment_method', 13)->get();
        $cods = PaymentSettings::where('id_payment_method', 5)->get();
        $paypal = PaymentSettings::where('id_payment_method', 15)->get();
        $payfast = PaymentSettings::where('id_payment_method', 7)->get();
        $paystack = PaymentSettings::where('id_payment_method', 11)->get();
        $flutterwave = PaymentSettings::where('id_payment_method', 12)->get();
        $wallet = PaymentSettings::where('id_payment_method', 9)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $applePay = PaymentSettings::where('id_payment_method', 17)->get();
        $xendit = PaymentSettings::where('id_payment_method', 20)->get();
        $orangepay = PaymentSettings::where('id_payment_method', 19)->get();
        $midtrans = PaymentSettings::where('id_payment_method', 18)->get();

        return view('settings.app.mercadopago')->with('stripe', $stripe)
            ->with('razorpay', $razorpay)->with('cods', $cods)
            ->with('payfast', $payfast)->with('paystack', $paystack)
            ->with('paypal', $paypal)
            ->with('flutterwave', $flutterwave)->with('wallet', $wallet)
            ->with('applePay', $applePay)->with('mercadopago', $mercadopago)
            ->with('xendit', $xendit)->with('midtrans', $midtrans)
            ->with('orangepay', $orangepay);
    }

    public function mercadopagoUpdate(Request $request, $id)
    {
        $mercadopagoKey = $request->mercadopagoKey;
        $mercadopago_accesstoken = $request->mercadopago_accesstoken;
        $ismercadopagoEnabled = $request->ismercadopagoEnabled;
        $isSandboxEnabled = $request->isSandboxEnabled;
        $modifier = date('Y-m-d H:i:s');

        $settings = PaymentSettings::find($id);
        if ($settings) {
            $settings->public_key = $mercadopagoKey;
            $settings->accesstoken = $mercadopago_accesstoken;
            $settings->isEnabled = $ismercadopagoEnabled;
            $settings->isSandboxEnabled = $isSandboxEnabled;
            $settings->modifier = $modifier;

        }
        $settings->save();
    }

    public function xendit()
    {
        $stripe = PaymentSettings::where('id_payment_method', 10)->get();
        $razorpay = PaymentSettings::where('id_payment_method', 13)->get();
        $cods = PaymentSettings::where('id_payment_method', 5)->get();
        $paypal = PaymentSettings::where('id_payment_method', 15)->get();
        $payfast = PaymentSettings::where('id_payment_method', 7)->get();
        $paystack = PaymentSettings::where('id_payment_method', 11)->get();
        $flutterwave = PaymentSettings::where('id_payment_method', 12)->get();
        $wallet = PaymentSettings::where('id_payment_method', 9)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $applePay = PaymentSettings::where('id_payment_method', 17)->get();
        $xendit = PaymentSettings::where('id_payment_method', 20)->get();
        $orangepay = PaymentSettings::where('id_payment_method', 19)->get();
        $midtrans = PaymentSettings::where('id_payment_method', 18)->get();
        return view('settings.app.xendit')->with('stripe', $stripe)
            ->with('razorpay', $razorpay)->with('cods', $cods)
            ->with('payfast', $payfast)->with('paystack', $paystack)
            ->with('paypal', $paypal)
            ->with('flutterwave', $flutterwave)->with('wallet', $wallet)
            ->with('applePay', $applePay)->with('mercadopago', $mercadopago)
            ->with('orangepay', $orangepay)->with('xendit', $xendit)
            ->with('midtrans', $midtrans);
    }

    public function xenditUpdate(Request $request, $id)
    {
        $xenditKey = $request->apiKey;
        $isEnabled = $request->isEnabled;
        $isSandboxEnabled = $request->isSandboxEnabled;
        $modifier = date('Y-m-d H:i:s');

        $settings = PaymentSettings::find($id);
        if ($settings) {
            $settings->key = $xenditKey;
            $settings->isEnabled = $isEnabled;
            $settings->isSandboxEnabled = $isSandboxEnabled;
            $settings->modifier = $modifier;

        }
        $settings->save();
    }
    public function orangepay()
    {
        $stripe = PaymentSettings::where('id_payment_method', 10)->get();
        $razorpay = PaymentSettings::where('id_payment_method', 13)->get();
        $cods = PaymentSettings::where('id_payment_method', 5)->get();
        $paypal = PaymentSettings::where('id_payment_method', 15)->get();
        $payfast = PaymentSettings::where('id_payment_method', 7)->get();
        $paystack = PaymentSettings::where('id_payment_method', 11)->get();
        $flutterwave = PaymentSettings::where('id_payment_method', 12)->get();
        $wallet = PaymentSettings::where('id_payment_method', 9)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $applePay = PaymentSettings::where('id_payment_method', 17)->get();
        $xendit = PaymentSettings::where('id_payment_method', 20)->get();
        $orangepay = PaymentSettings::where('id_payment_method', 19)->get();
        $midtrans = PaymentSettings::where('id_payment_method', 18)->get();
        return view('settings.app.orangepay')->with('stripe', $stripe)
            ->with('razorpay', $razorpay)->with('cods', $cods)
            ->with('payfast', $payfast)->with('paystack', $paystack)
            ->with('paypal', $paypal)
            ->with('flutterwave', $flutterwave)->with('wallet', $wallet)
            ->with('applePay', $applePay)->with('mercadopago', $mercadopago)
            ->with('orangepay', $orangepay)->with('xendit', $xendit)
            ->with('midtrans', $midtrans);
    }

    public function orangepayUpdate(Request $request, $id)
    {
        $apikey = $request->apiKey;
        $clientId = $request->clientId;
        $secretKey = $request->clientSecret;
        $isEnabled = $request->isEnabled;
        $isSandboxEnabled = $request->isSandboxEnabled;
        $cancelUrl = $request->cancelUrl;
        $merchatKey = $request->merchatKey;
        $notifyUrl = $request->notifyUrl;
        $returnUrl = $request->returnUrl;
        $modifier = date('Y-m-d H:i:s');

        $settings = PaymentSettings::find($id);
        if ($settings) {
            $settings->key = $apikey;
            $settings->isEnabled = $isEnabled;
            $settings->isSandboxEnabled = $isSandboxEnabled;
            $settings->clientpublishableKey = $clientId;
            $settings->secret_key = $secretKey;
            $settings->merchant_key = $merchatKey;
            $settings->cancel_url = $cancelUrl;
            $settings->notify_url = $notifyUrl;
            $settings->return_url = $returnUrl;
            $settings->modifier = $modifier;

        }
        $settings->save();
    }
    public function midtrans()
    {
        $stripe = PaymentSettings::where('id_payment_method', 10)->get();
        $razorpay = PaymentSettings::where('id_payment_method', 13)->get();
        $cods = PaymentSettings::where('id_payment_method', 5)->get();
        $paypal = PaymentSettings::where('id_payment_method', 15)->get();
        $payfast = PaymentSettings::where('id_payment_method', 7)->get();
        $paystack = PaymentSettings::where('id_payment_method', 11)->get();
        $flutterwave = PaymentSettings::where('id_payment_method', 12)->get();
        $wallet = PaymentSettings::where('id_payment_method', 9)->get();
        $mercadopago = PaymentSettings::where('id_payment_method', 16)->get();
        $applePay = PaymentSettings::where('id_payment_method', 17)->get();
        $xendit = PaymentSettings::where('id_payment_method', 20)->get();
        $orangepay = PaymentSettings::where('id_payment_method', 19)->get();
        $midtrans = PaymentSettings::where('id_payment_method', 18)->get();
        return view('settings.app.midtrans')->with('stripe', $stripe)
            ->with('razorpay', $razorpay)->with('cods', $cods)
            ->with('payfast', $payfast)->with('paystack', $paystack)
            ->with('paypal', $paypal)
            ->with('flutterwave', $flutterwave)->with('wallet', $wallet)
            ->with('applePay', $applePay)->with('mercadopago', $mercadopago)
            ->with('orangepay', $orangepay)->with('xendit', $xendit)
            ->with('midtrans', $midtrans);
    }

    public function midtransUpdate(Request $request, $id)
    {
        $serverKey = $request->serverKey;
        $isEnabled = $request->isEnabled;
        $isSandboxEnabled = $request->isSandboxEnabled;
        $modifier = date('Y-m-d H:i:s');

        $settings = PaymentSettings::find($id);
        if ($settings) {
            $settings->key = $serverKey;
            $settings->isEnabled = $isEnabled;
            $settings->isSandboxEnabled = $isSandboxEnabled;
            $settings->modifier = $modifier;

        }
        $settings->save();
    }

}
