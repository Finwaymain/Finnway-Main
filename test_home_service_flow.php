<?php

/**
 * Automated End-to-End Test for Home Services Booking & Full Lifecycle
 * 
 * Usage: php test_home_service_flow.php
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Http\Controllers\API\v1\ServiceRequestAPIController;

class HomeServiceAutomatedTester
{
    private $controller;
    private $passCount = 0;
    private $failCount = 0;
    private $testUserId = 999333;
    private $testDriverId = 888444;
    private $intruderDriverId = 777555;
    private $createdBookingId = null;

    public function __construct()
    {
        $this->controller = new ServiceRequestAPIController();
    }

    public function run()
    {
        echo "\n\033[1;36m========================================================================\033[0m\n";
        echo "\033[1;36m   FIINWAY AUTOMATED HOME SERVICES BOOKING & COMPLETION TEST SUITE      \033[0m\n";
        echo "\033[1;36m========================================================================\033[0m\n\n";

        $this->setupTestData();

        try {
            $this->test1_DiscoverCategories();
            $this->test2_DiscoverHomeServices();
            $this->test3_UserBooksHomeService();
            $this->test4_ProviderReceivesIncomingBooking();
            $this->test5_ProviderAcceptsBooking();
            $this->test6_ProviderStartsService();
            $this->test7_ProviderCompletesService();
            $this->test8_UserVerifiesCompletedHistory();
            $this->test9_SecurityConcurrencyHijackCheck();
        } catch (\Throwable $e) {
            $this->fail("Unhandled Exception: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
        } finally {
            $this->cleanup();
        }

        $this->printSummary();
    }

    private function setupTestData()
    {
        echo "\033[1;33m[SETUP]\033[0m Preparing test fixtures in database...\n";
        if (Schema::hasTable('tj_user_app')) {
            DB::table('tj_user_app')->updateOrInsert(
                ['id' => $this->testUserId],
                [
                    'prenom' => 'AutomatedTest',
                    'nom' => 'Customer',
                    'phone' => '+919999999999',
                    'email' => 'autotest.customer@fiinway.test',
                    'statut' => 'yes',
                ]
            );
        }
        if (Schema::hasTable('tj_conducteur')) {
            DB::table('tj_conducteur')->updateOrInsert(
                ['id' => $this->testDriverId],
                [
                    'prenom' => 'Professional',
                    'nom' => 'Electrician & AC Expert',
                    'phone' => '+918888888888',
                    'email' => 'autotest.provider@fiinway.test',
                    'statut' => 'yes',
                ]
            );
        }
        echo "\033[32m  ✔ Test user (#{$this->testUserId}) & provider (#{$this->testDriverId}) ready.\033[0m\n\n";
    }

    private function test1_DiscoverCategories()
    {
        echo "\033[1;34m[STEP 1]\033[0m Testing Consumer Service Categories Discovery (GET /api/v1/service-categories)...\n";
        $request = Request::create('/api/v1/service-categories', 'GET');
        $response = $this->controller->getServiceCategories($request);
        $data = $response->getData(true);

        if (($data['success'] ?? '') === 'success' && is_array($data['data'] ?? null)) {
            $count = count($data['data']);
            $this->pass("Service categories retrieved successfully. Total root categories: {$count}.");
        } else {
            $this->fail("Failed to retrieve service categories: " . json_encode($data));
        }
    }

    private function test2_DiscoverHomeServices()
    {
        echo "\033[1;34m[STEP 2]\033[0m Testing Home Services Catalog (GET /api/v1/home-services)...\n";
        $request = Request::create('/api/v1/home-services', 'GET');
        $response = $this->controller->getHomeServices($request);
        $data = $response->getData(true);

        if (($data['success'] ?? '') === 'success' && is_array($data['data'] ?? null)) {
            $this->pass("Home services catalog endpoint responded successfully.");
        } else {
            $this->fail("Failed to retrieve home services catalog: " . json_encode($data));
        }
    }

    private function test3_UserBooksHomeService()
    {
        echo "\033[1;34m[STEP 3]\033[0m Testing User Books Home Service (POST /api/v1/book-service)...\n";
        $payload = [
            'user_id' => $this->testUserId,
            'service_name' => 'AC Jet Pump Deep Cleaning & Gas Check',
            'address_type' => 'Flat 301, Ocean View Towers, Mumbai',
            'lat' => 19.0760,
            'lng' => 72.8777,
            'date' => date('Y-m-d', strtotime('+1 day')),
            'time' => '11:00 AM',
            'description' => 'AC making rattling noise and low airflow.',
            'booking_mode' => 'home_visit',
            'amount' => 599.00,
        ];

        $request = Request::create('/api/v1/book-service', 'POST', $payload);
        $response = $this->controller->bookService($request);
        $data = $response->getData(true);

        if (($data['success'] ?? '') === 'success' && !empty($data['data']['id'])) {
            $this->createdBookingId = $data['data']['id'];
            
            // Verify in database
            $dbRow = DB::table('service_requests')->where('id', $this->createdBookingId)->first();
            if ($dbRow && $dbRow->status === 'Pending' && (int)$dbRow->user_id === $this->testUserId) {
                $this->pass("Booking created successfully with ID #{$this->createdBookingId} in 'Pending' status.");
            } else {
                $this->fail("Booking record found in DB but status/user mismatch.");
            }
        } else {
            $this->fail("Failed to create booking: " . json_encode($data));
        }
    }

    private function test4_ProviderReceivesIncomingBooking()
    {
        echo "\033[1;34m[STEP 4]\033[0m Testing Provider Incoming Bookings Console (GET /api/v1/driver/bookings?status=incoming)...\n";
        $request = Request::create('/api/v1/driver/bookings', 'GET', [
            'id_driver' => $this->testDriverId,
            'status' => 'incoming',
        ]);
        $response = $this->controller->getDriverBookings($request);
        $data = $response->getData(true);

        $found = false;
        if (($data['success'] ?? '') === 'success' && is_array($data['data'] ?? null)) {
            foreach ($data['data'] as $item) {
                if ((string)$item['id'] === (string)$this->createdBookingId && $item['type'] === 'service') {
                    $found = true;
                    if ($item['status_group'] === 'incoming') {
                        $this->pass("Provider received incoming booking #{$this->createdBookingId} in 'incoming' group (status: {$item['status']}).");
                    } else {
                        $this->fail("Booking found in feed but status_group is not 'incoming'.");
                    }
                    break;
                }
            }
        }

        if (!$found) {
            $this->fail("Booking #{$this->createdBookingId} did not appear in provider's incoming feed.");
        }
    }

    private function test5_ProviderAcceptsBooking()
    {
        echo "\033[1;34m[STEP 5]\033[0m Testing Provider Accepts Booking (POST /api/v1/driver/bookings/service-status -> Accepted)...\n";
        $request = Request::create('/api/v1/driver/bookings/service-status', 'POST', [
            'id_driver' => $this->testDriverId,
            'booking_id' => $this->createdBookingId,
            'status' => 'accepted',
        ]);
        $response = $this->controller->updateServiceBookingStatus($request);
        $data = $response->getData(true);

        if (($data['success'] ?? '') === 'success') {
            $dbRow = DB::table('service_requests')->where('id', $this->createdBookingId)->first();
            if ($dbRow && $dbRow->status === 'Accepted' && (int)$dbRow->driver_id === $this->testDriverId) {
                $this->pass("Provider accepted booking. Assigned driver_id: #{$dbRow->driver_id}, status: 'Accepted'.");
            } else {
                $this->fail("DB status mismatch after acceptance: " . json_encode($dbRow));
            }
        } else {
            $this->fail("Failed to accept booking: " . json_encode($data));
        }
    }

    private function test6_ProviderStartsService()
    {
        echo "\033[1;34m[STEP 6]\033[0m Testing Provider Starts Service (POST /api/v1/driver/bookings/service-status -> In Progress)...\n";
        $request = Request::create('/api/v1/driver/bookings/service-status', 'POST', [
            'id_driver' => $this->testDriverId,
            'booking_id' => $this->createdBookingId,
            'status' => 'in_progress',
        ]);
        $response = $this->controller->updateServiceBookingStatus($request);
        $data = $response->getData(true);

        if (($data['success'] ?? '') === 'success') {
            $dbRow = DB::table('service_requests')->where('id', $this->createdBookingId)->first();
            if ($dbRow && $dbRow->status === 'In Progress') {
                $this->pass("Service started. DB status updated to 'In Progress'.");
            } else {
                $this->fail("DB status not updated to In Progress: " . json_encode($dbRow));
            }
        } else {
            $this->fail("Failed to start service: " . json_encode($data));
        }
    }

    private function test7_ProviderCompletesService()
    {
        echo "\033[1;34m[STEP 7]\033[0m Testing Provider Completes Service (POST /api/v1/driver/bookings/service-status -> Completed)...\n";
        $request = Request::create('/api/v1/driver/bookings/service-status', 'POST', [
            'id_driver' => $this->testDriverId,
            'booking_id' => $this->createdBookingId,
            'status' => 'completed',
        ]);
        $response = $this->controller->updateServiceBookingStatus($request);
        $data = $response->getData(true);

        if (($data['success'] ?? '') === 'success') {
            $dbRow = DB::table('service_requests')->where('id', $this->createdBookingId)->first();
            if ($dbRow && $dbRow->status === 'Completed') {
                $this->pass("Service completed successfully. DB status: 'Completed'.");
            } else {
                $this->fail("DB status not updated to Completed: " . json_encode($dbRow));
            }
        } else {
            $this->fail("Failed to complete service: " . json_encode($data));
        }
    }

    private function test8_UserVerifiesCompletedHistory()
    {
        echo "\033[1;34m[STEP 8]\033[0m Testing User Views Completed Service in History (GET /api/v1/service-history)...\n";
        $request = Request::create('/api/v1/service-history', 'GET', [
            'user_id' => $this->testUserId,
        ]);
        $response = $this->controller->getHistory($request);
        $data = $response->getData(true);

        $found = false;
        if (($data['success'] ?? '') === 'success' && is_array($data['data'] ?? null)) {
            foreach ($data['data'] as $item) {
                if ((string)$item['id'] === (string)$this->createdBookingId) {
                    $found = true;
                    if ($item['status'] === 'Completed' && (int)$item['driver_id'] === $this->testDriverId) {
                        $this->pass("User history verified: Booking #{$this->createdBookingId} is 'Completed' by provider #{$this->testDriverId}.");
                    } else {
                        $this->fail("User history item status or provider mismatch: " . json_encode($item));
                    }
                    break;
                }
            }
        }

        if (!$found) {
            $this->fail("Completed service was not returned in user's history list.");
        }
    }

    private function test9_SecurityConcurrencyHijackCheck()
    {
        echo "\033[1;34m[STEP 9]\033[0m Testing Concurrency Protection (Another Provider Cannot Hijack Booking)...\n";
        $request = Request::create('/api/v1/driver/bookings/service-status', 'POST', [
            'id_driver' => $this->intruderDriverId,
            'booking_id' => $this->createdBookingId,
            'status' => 'completed',
        ]);
        $response = $this->controller->updateServiceBookingStatus($request);
        $data = $response->getData(true);

        if (($data['success'] ?? '') === 'error' && str_contains(strtolower($data['message'] ?? ''), 'assigned to another provider')) {
            $this->pass("Security check passed: Unauthorized provider #{$this->intruderDriverId} rejected with: '{$data['message']}'.");
        } else {
            $this->fail("Security failure: Unauthorized provider was not properly blocked: " . json_encode($data));
        }
    }

    private function cleanup()
    {
        echo "\n\033[1;33m[CLEANUP]\033[0m Removing test records...\n";
        if ($this->createdBookingId) {
            DB::table('service_requests')->where('id', $this->createdBookingId)->delete();
        }
        if (Schema::hasTable('tj_user_app')) {
            DB::table('tj_user_app')->where('id', $this->testUserId)->delete();
        }
        if (Schema::hasTable('tj_conducteur')) {
            DB::table('tj_conducteur')->where('id', $this->testDriverId)->delete();
        }
        echo "\033[32m  ✔ Test fixtures cleaned up.\033[0m\n";
    }

    private function pass(string $msg)
    {
        $this->passCount++;
        echo "  \033[32m✔ PASS: {$msg}\033[0m\n\n";
    }

    private function fail(string $msg)
    {
        $this->failCount++;
        echo "  \033[31m✘ FAIL: {$msg}\033[0m\n\n";
    }

    private function printSummary()
    {
        echo "\033[1;36m========================================================================\033[0m\n";
        echo "\033[1;37mTEST RUN RESULTS:\033[0m\n";
        echo "  \033[32m✔ Passed: {$this->passCount}\033[0m\n";
        if ($this->failCount > 0) {
            echo "  \033[31m✘ Failed: {$this->failCount}\033[0m\n";
            echo "\033[1;31mSTATUS: TESTS FAILED\033[0m\n";
        } else {
            echo "  \033[32m✘ Failed: 0\033[0m\n";
            echo "\033[1;32mSTATUS: ALL HOME SERVICE AUTOMATED TESTS PASSED (100% SUCCESS) 🎉\033[0m\n";
        }
        echo "\033[1;36m========================================================================\033[0m\n\n";
    }
}

$tester = new HomeServiceAutomatedTester();
$tester->run();
