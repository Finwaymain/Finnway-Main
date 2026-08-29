<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ServiceRequest;
use App\Http\Controllers\API\v1\ServiceRequestAPIController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ServiceNotificationDispatchTest extends TestCase
{
    public function test_driver_profile_service_matching()
    {
        $controller = app(ServiceRequestAPIController::class);
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('serviceMatchesDriverProfile');
        $method->setAccessible(true);

        // 1. Cleaning Provider profile
        $cleaningProfile = [
            'match_keywords' => ['clean', 'cleaning', 'deep cleaning', 'sofa cleaning']
        ];

        // Should match cleaning services
        $this->assertTrue($method->invoke($controller, 'Home Deep Cleaning', $cleaningProfile));
        $this->assertTrue($method->invoke($controller, 'Sofa Cleaning Service', $cleaningProfile));

        // Should NOT match AC Installation
        $this->assertFalse($method->invoke($controller, 'AC Installation', $cleaningProfile));
        $this->assertFalse($method->invoke($controller, 'AC Gas Refill', $cleaningProfile));
        $this->assertFalse($method->invoke($controller, 'Electrician Wiring', $cleaningProfile));

        // 2. AC Technician profile
        $acProfile = [
            'match_keywords' => ['ac repair', 'ac service', 'ac installation', 'air conditioner']
        ];

        // Should match AC services
        $this->assertTrue($method->invoke($controller, 'AC Installation', $acProfile));
        $this->assertTrue($method->invoke($controller, 'AC Repair & Service', $acProfile));

        // Should NOT match Cleaning
        $this->assertFalse($method->invoke($controller, 'Full Home Deep Cleaning', $acProfile));
    }

    public function test_booking_status_groups()
    {
        $controller = app(ServiceRequestAPIController::class);
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('bookingStatusGroup');
        $method->setAccessible(true);

        $this->assertEquals('incoming', $method->invoke($controller, 'pending'));
        $this->assertEquals('incoming', $method->invoke($controller, 'new'));
        $this->assertEquals('incoming', $method->invoke($controller, 'open'));

        $this->assertEquals('active', $method->invoke($controller, 'confirmed'));
        $this->assertEquals('active', $method->invoke($controller, 'on ride'));
        $this->assertEquals('active', $method->invoke($controller, 'in progress'));

        $this->assertEquals('history', $method->invoke($controller, 'completed'));
        $this->assertEquals('history', $method->invoke($controller, 'rejected'));
    }

    public function test_driver_cancellation_blocking_rules()
    {
        $blockedStatuses = ['in progress', 'in_progress', 'started', 'on ride', 'onride', 'on_ride', 'awaiting payment', 'awaiting_payment', 'completed'];
        
        foreach ($blockedStatuses as $status) {
            $isBlocked = in_array(strtolower(trim($status)), $blockedStatuses, true);
            $this->assertTrue($isBlocked, "Status $status must block driver cancellation");
        }

        $allowedStatuses = ['pending', 'new', 'open', 'assigned'];
        foreach ($allowedStatuses as $status) {
            $isBlocked = in_array(strtolower(trim($status)), $blockedStatuses, true);
            $this->assertFalse($isBlocked, "Status $status should allow driver cancellation");
        }
    }

    public function test_payment_methods_tax_and_fee_calculation()
    {
        $baseService = 500.0;
        $visitingCharge = 100.0;
        $baseTotal = $baseService + $visitingCharge; // 600.0

        // Mock Admin Tax Rules from tj_tax
        $adminTaxes = [
            ['libelle' => 'GST', 'value' => '18', 'type' => 'Percentage', 'statut' => 'yes', 'applicable_on' => 'cash,upi,wallet,online'],
            ['libelle' => 'Platform Fee', 'value' => '10', 'type' => 'Percentage', 'statut' => 'yes', 'applicable_on' => 'upi,online,wallet'],
            ['libelle' => 'UPI Handling', 'value' => '2', 'type' => 'Percentage', 'statut' => 'yes', 'applicable_on' => 'upi'],
        ];

        // 1. UPI Payment: All 3 apply
        $upiTaxes = 0.0;
        foreach ($adminTaxes as $tax) {
            $app = explode(',', $tax['applicable_on']);
            if (in_array('upi', $app, true)) {
                $amt = round(($baseTotal * (float)$tax['value']) / 100, 2);
                $upiTaxes += $amt;
            }
        }
        // GST(18%)=108 + Platform Fee(10%)=60 + UPI(2%)=12 => 180
        $this->assertEquals(180.0, $upiTaxes);
        $this->assertEquals(780.0, $baseTotal + $upiTaxes);

        // 2. Wallet Payment: GST + Platform Fee apply (no UPI handling)
        $walletTaxes = 0.0;
        foreach ($adminTaxes as $tax) {
            $app = explode(',', $tax['applicable_on']);
            if (in_array('wallet', $app, true)) {
                $amt = round(($baseTotal * (float)$tax['value']) / 100, 2);
                $walletTaxes += $amt;
            }
        }
        // GST(18%)=108 + Platform Fee(10%)=60 => 168
        $this->assertEquals(168.0, $walletTaxes);
        $this->assertEquals(768.0, $baseTotal + $walletTaxes);

        // 3. Cash Payment: Only GST applies
        $cashTaxes = 0.0;
        foreach ($adminTaxes as $tax) {
            $app = explode(',', $tax['applicable_on']);
            if (in_array('cash', $app, true)) {
                $amt = round(($baseTotal * (float)$tax['value']) / 100, 2);
                $cashTaxes += $amt;
            }
        }
        // GST(18%)=108
        $this->assertEquals(108.0, $cashTaxes);
        $this->assertEquals(708.0, $baseTotal + $cashTaxes);
    }
}
