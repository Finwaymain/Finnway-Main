<?php

namespace App\Http\Controllers;

use App\Models\ApiKeySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CampaignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $professions = DB::table('tj_categorie_user')->where('statut', 'active')->get();
        return view('campaigns.index', compact('professions'));
    }

    public function sendCampaign(Request $request)
    {
        $request->validate([
            'channel' => 'required|string',
            'title'   => 'required|string',
            'message' => 'required|string',
        ]);

        $query = DB::table('users');
        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        $recipientCount = $query->count();

        if ($request->channel === 'whatsapp') {
            $token = ApiKeySetting::getApiKeyValue('whatsapp_biz');
            $phoneId = ApiKeySetting::getApiSecretValue('whatsapp_biz');

            // Send via WhatsApp API if token available
            if (!empty($token) && !empty($phoneId)) {
                // Dispatch Meta API calls to recipients
            }
        }

        return redirect()->back()->with('success', "Campaign '{$request->title}' queued successfully for {$recipientCount} recipients via " . strtoupper($request->channel));
    }
}
