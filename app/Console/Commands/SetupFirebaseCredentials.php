<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupFirebaseCredentials extends Command
{
    protected $signature = 'firebase:setup';
    protected $description = 'Set up and verify Firebase Service Account credentials for fiinway-app';

    public function handle()
    {
        $this->info('=================================================================');
        $this->info('  SETTING UP FIREBASE SERVICE ACCOUNT (fiinway-app)');
        $this->info('=================================================================');

        $dir = storage_path('app/firebase');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $privateKeyLines = [
            "-----BEGIN PRIVATE KEY-----",
            "MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDMG8d/YOBx7MxS",
            "wTjhZCy3wTuvuJ65W+yV8adKZjeYghtFXvFymc0pO/3zmQ4PV0ljs6D9tQXIEbdP",
            "8QMr5IFAGcApqJv5Q87yOJdVr/JO+W41wmiho1GR6kURr8qgunmSxPNADUY3PGjm",
            "DEMzM4M/6Q6JhGFWT5V2kZu46FgeQwV+g+GAbht8czyU+avJev3QYpwPafig0CXH",
            "UWNwOk3+bKbLz6Rcvs9Ohdn3xy66rr+oB1xYq/61r0E0ZaCAyMhiiXzw4rxyjk6O",
            "lqTHommtNC3XYj4eMNIX+28pdh+G1LRKZEXTLnkVHhtNTyJuUXqGMN4ZRaR5b1G8",
            "MbwuKMsFAgMBAAECggEAL4FpsfKE2s9AESTKzd7ob45oi3Ifc0n7azzW8ICRAQyL",
            "N16tOLymXSoK5eciOBzbRnrZBiPbaCEDdvZEBDPHh3lW2ftV7jLDmGnmgzmT3qC8",
            "b4PDoZdnFVye3cp6EWXFhQ0VBrvwM0v6qEyDWYfsTPQ7/3LRyup0AahLQ8LEO6p4",
            "Pd26WVVyAOouvrLiExTG9bGYs4CzESq5qmgi0pGAcBsf3h/8TDsZ1RLHvh4Jrj/A",
            "M1pnp5W/3OaaYtvujCxxSJIu1zf6eYNDo3c1bgjVKA3c3EDG/WDU7AzfwKsNMMM3",
            "PKMMGDT9gOW10XOscUacfmPovQkZysgGPalMn/cnswKBgQDuP29xLxpU2XOD2hZC",
            "6r4s53mktRrR86GGrHBFcXN+O/NwsoEq8KlsTgBW6blFVkGVwsmEk9rMxrVAIGUf",
            "46WRPjaI+475iwNR1pD4kaC8kquFj6Ki8E9P/idjg8lfwET5bhcCnhAbROV+wYSy",
            "1hpqJTe2GU49ww4FcM+cscWaIwKBgQDbUSOOgD1Bfal0Qdr2lAWg6StR2GBpSD55",
            "0+HPUBwx9JQDpzwW/G+lEuwXybE9OiSJMjk8W6xOi8oXnChJw91U4ZY81PbSchmB",
            "k+7Mlvl5dtLKGJxqnWpgj93mg5VW03gbmbXZuvHyVjJOTQOTVbyRiFkPjERSHPx5",
            "PH7+T020twKBgAxoR1B4qIFktaxXLjOb7/18rIwUVmo6qt3NmycyqlYXnyzuTuXC",
            "lMAsK75a+6gNvuqis0XxQULK0mOdjEal31h6CfMGTsLWJ30alIAtbVaEuOQCv/CG",
            "XUsILFr1YQZYh/8Jaa9cNfCFnQIF5/g8SDHg8E7OcJGs180Wu+koJOdLAoGBAKIU",
            "P7PDQAHTTVT2ikxqLhKx3urYfr+vvUQpexrLuFqOxohAoh7WpeeXqVUXIF4ARxoB",
            "PN1HXnqZwltac0e4cSyWnoIMXPA/lGv3mKYn+Ox0DOl/8LC17vS3vaTqn4YQOBYl",
            "rYfgKYgPfZPyRG8xEG95FWBxJ9iLRWaPd8aXcvTXAoGBAORgAPWoQh8yUF2ENBuc",
            "+cOct8R0+dMTLhs4fgfli61pNtc5hKbYOGazvCNWMPGZJ6Z6Pcl7LxdJd3nNtAU/",
            "JovTqos2n0mSO+db8afM9B7QQJtyGBXCMHIbrx5KYfuOkbDyRitXCpQLe9OvzX/6",
            "lcxEGFhrzV4GBSUm8pymni09",
            "-----END PRIVATE KEY-----\n"
        ];

        $credentialsData = [
            "type" => "service_account",
            "project_id" => "fiinway-app",
            "private_key_id" => "dfbff37c15510d5d3140c341c0ccdfadf63435be",
            "private_key" => implode("\n", $privateKeyLines),
            "client_email" => "firebase-adminsdk-fbsvc@fiinway-app.iam.gserviceaccount.com",
            "client_id" => "110285145750798796084",
            "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
            "token_uri" => "https://oauth2.googleapis.com/token",
            "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
            "client_x509_cert_url" => "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-fbsvc%40fiinway-app.iam.gserviceaccount.com",
            "universe_domain" => "googleapis.com"
        ];

        $jsonFormatted = json_encode($credentialsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $filePath = storage_path('app/firebase/credentials.json');
        File::put($filePath, $jsonFormatted);

        $this->info("  [OK] Saved credentials to: {$filePath}");
        $this->info("  Project ID   : " . $credentialsData['project_id']);
        $this->info("  Client Email : " . $credentialsData['client_email']);

        // Verify Google Client Authentication
        $this->info("\n--- Verifying Google OAuth2 Token Generation ---");
        try {
            $client = new \Google\Client();
            $client->setAuthConfig($credentialsData);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->refreshTokenWithAssertion();
            $token = $client->getAccessToken();

            if (!empty($token['access_token'])) {
                $this->info('  [PASS] Successfully authenticated with Firebase Cloud Messaging API (HTTP v1)!');
                $this->info('  Access Token : ' . substr($token['access_token'], 0, 25) . '...');
                $this->info('  Expires In   : ' . ($token['expires_in'] ?? 'N/A') . ' seconds');
                $this->info("\n=================================================================");
                $this->info('  FIREBASE SETUP COMPLETE & FULLY OPERATIONAL');
                $this->info('=================================================================');
                return 0;
            } else {
                $this->error('  [FAIL] Could not retrieve access token from Google.');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('  [FAIL] Authentication Exception: ' . $e->getMessage());
            return 1;
        }
    }
}
