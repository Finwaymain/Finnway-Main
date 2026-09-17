<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SmtpSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SmtpSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show SMTP Settings & Verification Page
     */
    public function index()
    {
        $setting = SmtpSetting::first();
        if (!$setting) {
            $setting = SmtpSetting::create([
                'mail_mailer'       => 'smtp',
                'mail_host'         => 'smtp.hostinger.com',
                'mail_port'         => 465,
                'mail_username'     => 'git@openscore.msmeloan.sbs',
                'mail_password'     => 'Hostinger@2026Secure!',
                'mail_encryption'   => 'ssl',
                'mail_from_address' => 'git@openscore.msmeloan.sbs',
                'mail_from_name'    => 'Fiinway Desk',
                'is_active'         => true,
            ]);
        }

        return view('administration_tools.smtp_settings.index', compact('setting'));
    }

    /**
     * Save SMTP Settings
     */
    public function save(Request $request)
    {
        $request->validate([
            'mail_host'         => 'required|string',
            'mail_port'         => 'required|numeric|min:1|max:65535',
            'mail_username'     => 'required|string',
            'mail_password'     => 'nullable|string',
            'mail_encryption'   => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name'    => 'required|string',
        ]);

        $setting = SmtpSetting::first();
        if (!$setting) {
            $setting = new SmtpSetting();
        }

        $setting->mail_mailer       = $request->input('mail_mailer', 'smtp');
        $setting->mail_host         = trim($request->mail_host);
        $setting->mail_port         = (int)$request->mail_port;
        $setting->mail_username     = trim($request->mail_username);

        // Only update password if provided
        if ($request->filled('mail_password')) {
            $setting->mail_password = $request->mail_password;
        }

        $setting->mail_encryption   = $request->mail_encryption === 'none' ? null : $request->mail_encryption;
        $setting->mail_from_address = trim($request->mail_from_address);
        $setting->mail_from_name    = trim($request->mail_from_name);
        $setting->is_active         = $request->has('is_active');

        $setting->save();

        // Apply immediately into config
        SmtpSetting::applyConfig();

        // Sync to .env
        $setting->syncToEnv();

        return redirect()->back()->with('success', 'SMTP settings saved and activated successfully.');
    }

    /**
     * Verify / Test SMTP Configuration by sending a real email
     */
    public function testConnection(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        $testEmail = trim($request->test_email);
        $setting = SmtpSetting::getActive();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'No active SMTP settings found. Please save your configuration first.',
            ], 422);
        }

        // Apply configuration dynamically
        SmtpSetting::applyConfig();

        try {
            $appName = $setting->mail_from_name ?: config('app.name', 'Fiinway');
            $fromAddr = $setting->mail_from_address ?: $setting->mail_username;
            $host = $setting->mail_host;
            $port = $setting->mail_port;
            $encryption = strtoupper($setting->mail_encryption ?: 'None');
            $testedAt = date('d M Y, h:i:s A');

            $html = "
            <!DOCTYPE html>
            <html>
            <head><meta charset='utf-8'><title>SMTP Verification</title></head>
            <body style='margin:0;padding:0;background-color:#0F172A;font-family:Arial,sans-serif;'>
                <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#0F172A;padding:40px 15px;'>
                    <tr>
                        <td align='center'>
                            <table width='540' cellpadding='0' cellspacing='0' style='background:#1E293B;border-radius:16px;border:1px solid #334155;overflow:hidden;'>
                                <tr>
                                    <td style='background:linear-gradient(135deg, #10B981 0%, #059669 100%);padding:28px 32px;text-align:center;'>
                                        <h1 style='margin:0;color:#FFFFFF;font-size:24px;font-weight:800;'>SMTP CONNECTION VERIFIED</h1>
                                        <p style='margin:6px 0 0;color:rgba(255,255,255,0.9);font-size:13px;'>Fiinway Email Dispatch Engine</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='padding:30px;'>
                                        <p style='color:#E2E8F0;font-size:15px;margin:0 0 16px;'>Congratulations! Your SMTP server settings are correctly configured and operational.</p>
                                        
                                        <div style='background:#0F172A;border:1px solid #334155;border-radius:10px;padding:16px;margin:0 0 20px;'>
                                            <table width='100%' cellpadding='4' cellspacing='0' style='font-size:13px;color:#94A3B8;'>
                                                <tr><td width='40%'><strong>SMTP Host:</strong></td><td style='color:#E2E8F0;'>{$host}</td></tr>
                                                <tr><td><strong>Port & Security:</strong></td><td style='color:#E2E8F0;'>{$port} ({$encryption})</td></tr>
                                                <tr><td><strong>Sender:</strong></td><td style='color:#E2E8F0;'>{$fromAddr}</td></tr>
                                                <tr><td><strong>Recipient:</strong></td><td style='color:#E2E8F0;'>{$testEmail}</td></tr>
                                                <tr><td><strong>Verified At:</strong></td><td style='color:#10B981;'>{$testedAt}</td></tr>
                                            </table>
                                        </div>

                                        <p style='color:#94A3B8;font-size:13px;line-height:1.5;margin:0;'>
                                            All transactional emails (Email OTPs for Driver and User plan activation, tax invoices, and courier updates) will now be delivered successfully.
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='background:#0F172A;padding:16px;text-align:center;border-top:1px solid #334155;'>
                                        <p style='color:#64748B;font-size:11px;margin:0;'>© " . date('Y') . " Fiinway Technologies. All rights reserved.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>
            ";

            Mail::html($html, function ($message) use ($testEmail, $fromAddr, $appName) {
                $message->to($testEmail)
                        ->from($fromAddr, $appName)
                        ->subject("{$appName} — SMTP Configuration Test Verified");
            });

            // Update record
            $setting->last_tested_at = now();
            $setting->last_test_status = 'success';
            $setting->last_test_message = "Test email delivered successfully to {$testEmail}.";
            $setting->save();

            return response()->json([
                'success' => true,
                'message' => "Success: Connected to {$host}:{$port} and test email delivered to {$testEmail}!",
                'tested_at' => $setting->last_tested_at->format('d M Y, h:i A'),
            ]);

        } catch (\Throwable $e) {
            Log::error("SmtpSetting: Test email failed: " . $e->getMessage());

            $setting->last_tested_at = now();
            $setting->last_test_status = 'failed';
            $setting->last_test_message = $e->getMessage();
            $setting->save();

            return response()->json([
                'success' => false,
                'message' => 'Connection Failed: ' . $e->getMessage(),
                'tested_at' => $setting->last_tested_at->format('d M Y, h:i A'),
            ], 500);
        }
    }
}
