<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SmtpSetting extends Model
{
    use HasFactory;

    protected $table = 'smtp_settings';

    protected $fillable = [
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'is_active',
        'last_tested_at',
        'last_test_status',
        'last_test_message',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'mail_port'       => 'integer',
        'last_tested_at'  => 'datetime',
    ];

    /**
     * Get the active SMTP configuration or fallback to first
     */
    public static function getActive(): ?self
    {
        if (!Schema::hasTable('smtp_settings')) {
            return null;
        }

        return self::where('is_active', true)->first() ?? self::first();
    }

    /**
     * Apply active SMTP configuration dynamically to Laravel mail config
     */
    public static function applyConfig(): ?self
    {
        $setting = self::getActive();
        if (!$setting || !$setting->is_active) {
            return $setting;
        }

        $encryption = strtolower(trim((string)$setting->mail_encryption));
        if ($encryption === 'none' || $encryption === 'null' || empty($encryption)) {
            $encryption = null;
        }

        config([
            'mail.default' => $setting->mail_mailer ?: 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $setting->mail_host,
            'mail.mailers.smtp.port' => (int)($setting->mail_port ?: 465),
            'mail.mailers.smtp.encryption' => $encryption,
            'mail.mailers.smtp.username' => $setting->mail_username,
            'mail.mailers.smtp.password' => $setting->mail_password,
            'mail.from.address' => $setting->mail_from_address ?: $setting->mail_username,
            'mail.from.name' => $setting->mail_from_name ?: config('app.name', 'Fiinway'),
        ]);

        try {
            Mail::purge('smtp');
        } catch (\Throwable $e) {
            // Ignored if mailer wasn't booted yet
        }

        return $setting;
    }

    /**
     * Optionally sync configured values to .env file
     */
    public function syncToEnv(): bool
    {
        try {
            $envPath = base_path('.env');
            if (!file_exists($envPath) || !is_writable($envPath)) {
                return false;
            }

            $content = file_get_contents($envPath);

            $pairs = [
                'MAIL_MAILER'       => $this->mail_mailer ?: 'smtp',
                'MAIL_HOST'         => $this->mail_host ?: 'smtp.hostinger.com',
                'MAIL_PORT'         => (string)($this->mail_port ?: 465),
                'MAIL_USERNAME'     => $this->mail_username ?: '',
                'MAIL_PASSWORD'     => $this->mail_password ?: '',
                'MAIL_ENCRYPTION'   => $this->mail_encryption ?: 'ssl',
                'MAIL_FROM_ADDRESS' => $this->mail_from_address ?: $this->mail_username,
                'MAIL_FROM_NAME'    => '"' . addslashes($this->mail_from_name ?: 'Fiinway') . '"',
            ];

            foreach ($pairs as $key => $val) {
                if (preg_match("/^{$key}=.*/m", $content)) {
                    $content = preg_replace("/^{$key}=.*/m", "{$key}={$val}", $content);
                } else {
                    $content .= PHP_EOL . "{$key}={$val}";
                }
            }

            file_put_contents($envPath, $content);
            return true;
        } catch (\Throwable $e) {
            Log::warning("SmtpSetting: Failed to sync .env: " . $e->getMessage());
            return false;
        }
    }
}
