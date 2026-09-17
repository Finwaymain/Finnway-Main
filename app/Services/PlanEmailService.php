<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PlanEmailService
{
    /**
     * Send OTP for Email Verification before plan activation
     */
    public static function sendPlanOtpEmail(string $toEmail, string $otp, string $name = 'User', string $userType = 'driver'): bool
    {
        try {
            $smtp = \App\Models\SmtpSetting::applyConfig();
            $appName = $smtp ? ($smtp->mail_from_name ?: config('app.name', 'Fiinway')) : config('app.name', 'Fiinway');
            $fromAddr = $smtp ? ($smtp->mail_from_address ?: $smtp->mail_username) : env('OTP_MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', 'git@openscore.msmeloan.sbs'));
            $subject = "$appName — Verify Your Email for Plan Activation";

            $html = "
            <!DOCTYPE html>
            <html>
            <head><meta charset='utf-8'><title>Email Verification</title></head>
            <body style='margin:0;padding:0;background-color:#0F172A;font-family:Arial,sans-serif;'>
                <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#0F172A;padding:40px 10px;'>
                    <tr>
                        <td align='center'>
                            <table width='540' cellpadding='0' cellspacing='0' style='background:#1E293B;border-radius:16px;border:1px solid #334155;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.5);'>
                                <tr>
                                    <td style='background:linear-gradient(135deg, #FF6B00 0%, #D95300 100%);padding:28px 32px;text-align:center;'>
                                        <h1 style='margin:0;color:#FFFFFF;font-size:26px;letter-spacing:1px;font-weight:800;'>FIINWAY</h1>
                                        <p style='margin:4px 0 0;color:rgba(255,255,255,0.9);font-size:13px;'>Drive. Serve. Grow.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='padding:32px;'>
                                        <p style='color:#E2E8F0;font-size:15px;margin:0 0 16px;'>Hello <strong>" . htmlspecialchars($name) . "</strong>,</p>
                                        <p style='color:#94A3B8;font-size:14px;line-height:1.6;margin:0 0 24px;'>
                                            Please use the following One-Time Password (OTP) to verify your email address and activate your <strong>FIINWAY Membership Plan</strong>.
                                        </p>
                                        <div style='background:#0F172A;border:1px dashed #FF6B00;border-radius:12px;padding:18px;text-align:center;margin:0 0 24px;'>
                                            <span style='font-size:32px;font-weight:bold;letter-spacing:8px;color:#FF6B00;'>" . htmlspecialchars($otp) . "</span>
                                        </div>
                                        <p style='color:#94A3B8;font-size:13px;line-height:1.5;margin:0 0 8px;'>
                                            • This OTP is valid for <strong>10 minutes</strong>.<br>
                                            • Do not share this OTP with anyone, including Fiinway representatives.
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='background:#0F172A;padding:20px;text-align:center;border-top:1px solid #334155;'>
                                        <p style='color:#64748B;font-size:12px;margin:0;'>© " . date('Y') . " Fiinway Technologies. All rights reserved.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>
            ";

            Mail::html($html, function ($message) use ($toEmail, $fromAddr, $appName, $subject) {
                $message->to($toEmail)
                        ->from($fromAddr, $appName)
                        ->subject($subject);
            });

            return true;
        } catch (\Throwable $e) {
            Log::error("PlanEmailService: Failed to send OTP email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Plan Activation Confirmation Email
     */
    public static function sendPlanActivationEmail(array $data): bool
    {
        try {
            $toEmail = $data['email'] ?? '';
            if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
                return false;
            }

            $smtp = \App\Models\SmtpSetting::applyConfig();
            $appName = $smtp ? ($smtp->mail_from_name ?: config('app.name', 'Fiinway')) : config('app.name', 'Fiinway');
            $fromAddr = $smtp ? ($smtp->mail_from_address ?: $smtp->mail_username) : env('OTP_MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', 'git@openscore.msmeloan.sbs'));
            $planName = $data['plan_name'] ?? 'Fiinway Membership';
            $subject = "$appName — Plan Activated: $planName";

            $userName = htmlspecialchars($data['user_name'] ?? 'Valued Member');
            $userId = htmlspecialchars((string)($data['user_id'] ?? ''));
            $userType = ucfirst($data['user_type'] ?? 'Partner');
            $validity = htmlspecialchars($data['validity'] ?? '365 Days');
            $expiryDate = htmlspecialchars($data['expiry_date'] ?? date('d M Y', strtotime('+1 year')));
            $amountPaid = number_format(floatval($data['amount'] ?? 0), 2);
            $txnId = htmlspecialchars($data['txn_id'] ?? ('TXN-' . time()));
            $benefits = $data['benefits'] ?? [];

            $benefitsListHtml = "";
            foreach ($benefits as $b) {
                $benefitsListHtml .= "<li style='padding:6px 0;color:#E2E8F0;font-size:13px;border-bottom:1px solid #334155;'>✓ " . htmlspecialchars($b) . "</li>";
            }

            $html = "
            <!DOCTYPE html>
            <html>
            <head><meta charset='utf-8'><title>Plan Activated</title></head>
            <body style='margin:0;padding:0;background-color:#0F172A;font-family:Arial,sans-serif;'>
                <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#0F172A;padding:30px 10px;'>
                    <tr>
                        <td align='center'>
                            <table width='580' cellpadding='0' cellspacing='0' style='background:#1E293B;border-radius:16px;border:1px solid #334155;overflow:hidden;'>
                                <tr>
                                    <td style='background:linear-gradient(135deg, #10B981 0%, #059669 100%);padding:30px;text-align:center;'>
                                        <div style='background:rgba(255,255,255,0.2);display:inline-block;border-radius:50%;width:56px;height:56px;line-height:56px;color:#FFFFFF;font-size:28px;margin-bottom:12px;'>✓</div>
                                        <h1 style='margin:0;color:#FFFFFF;font-size:24px;font-weight:800;'>PLAN ACTIVATED SUCCESSFULLY!</h1>
                                        <p style='margin:6px 0 0;color:#E2E8F0;font-size:14px;'>Welcome to <strong>" . htmlspecialchars($planName) . "</strong></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='padding:28px;'>
                                        <p style='color:#E2E8F0;font-size:15px;margin:0 0 18px;'>Dear <strong>$userName</strong> ($userType ID: #$userId),</p>
                                        <p style='color:#94A3B8;font-size:14px;line-height:1.5;margin:0 0 20px;'>
                                            Congratulations! Your subscription has been successfully activated. You are now entitled to full VIP membership benefits, higher earnings, and priority support.
                                        </p>

                                        <!-- Invoice Summary Box -->
                                        <table width='100%' cellpadding='10' cellspacing='0' style='background:#0F172A;border-radius:12px;border:1px solid #334155;margin-bottom:24px;'>
                                            <tr>
                                                <td style='color:#94A3B8;font-size:13px;border-bottom:1px solid #1E293B;'>Plan Name:</td>
                                                <td align='right' style='color:#FFFFFF;font-weight:bold;font-size:13px;border-bottom:1px solid #1E293B;'>" . htmlspecialchars($planName) . "</td>
                                            </tr>
                                            <tr>
                                                <td style='color:#94A3B8;font-size:13px;border-bottom:1px solid #1E293B;'>Amount Paid:</td>
                                                <td align='right' style='color:#10B981;font-weight:bold;font-size:14px;border-bottom:1px solid #1E293B;'>₹$amountPaid</td>
                                            </tr>
                                            <tr>
                                                <td style='color:#94A3B8;font-size:13px;border-bottom:1px solid #1E293B;'>Transaction ID:</td>
                                                <td align='right' style='color:#CBD5E1;font-size:12px;border-bottom:1px solid #1E293B;'>$txnId</td>
                                            </tr>
                                            <tr>
                                                <td style='color:#94A3B8;font-size:13px;border-bottom:1px solid #1E293B;'>Validity:</td>
                                                <td align='right' style='color:#FFFFFF;font-size:13px;border-bottom:1px solid #1E293B;'>$validity</td>
                                            </tr>
                                            <tr>
                                                <td style='color:#94A3B8;font-size:13px;'>Expires On:</td>
                                                <td align='right' style='color:#F59E0B;font-weight:bold;font-size:13px;'>$expiryDate</td>
                                            </tr>
                                        </table>

                                        <!-- Benefits Section -->
                                        <h3 style='color:#FFFFFF;font-size:16px;margin:0 0 12px;border-left:4px solid #10B981;padding-left:10px;'>Your Unlocked Benefits:</h3>
                                        <ul style='list-style:none;padding:0;margin:0 0 24px;'>
                                            $benefitsListHtml
                                        </ul>

                                        <div style='background:#1E293B;border:1px solid #334155;border-radius:10px;padding:14px;color:#94A3B8;font-size:12px;line-height:1.5;'>
                                            <strong>Membership Policy:</strong> Plan subscription is non-refundable. Only higher-tier upgrades are permitted. For assistance, contact support in the Fiinway app or write to support@fiinway.com.
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='background:#0F172A;padding:20px;text-align:center;border-top:1px solid #334155;'>
                                        <p style='color:#64748B;font-size:12px;margin:0;'>© " . date('Y') . " Fiinway Technologies Pvt Ltd. All rights reserved.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>
            ";

            Mail::html($html, function ($message) use ($toEmail, $fromAddr, $appName, $subject) {
                $message->to($toEmail)
                        ->from($fromAddr, $appName)
                        ->subject($subject);
            });

            return true;
        } catch (\Throwable $e) {
            Log::error("PlanEmailService: Failed to send activation email: " . $e->getMessage());
            return false;
        }
    }
}
