<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use App\Models\ServiceRequest;

class HomeServiceBookingFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test 1: User can discover home service categories
     */
    public function test_user_can_discover_service_categories(): void
    {
        $response = $this->getJson('/api/v1/service-categories');

        $response->assertStatus(200)
            ->assertJson([
                'success' => 'success',
            ]);

        $this->assertIsArray($response->json('data'));
    }

    /**
     * Test 2: User can discover grouped home services
     */
    public function test_user_can_discover_home_services(): void
    {
        $response = $this->getJson('/api/v1/home-services');

        $response->assertStatus(200)
            ->assertJson([
                'success' => 'success',
            ]);
    }

    /**
     * Test 3: Complete End-to-End Home Service Lifecycle:
     * User Books -> Provider Receives -> Provider Accepts -> Provider Starts -> Provider Completes -> User Verifies History
     */
    public function test_complete_home_service_booking_and_completion_lifecycle(): void
    {
        // 1. Setup Test User and Provider IDs
        $testUserId = 999111;
        $testDriverId = 888222;

        // Ensure user exists in tj_user_app for joins
        if (Schema::hasTable('tj_user_app')) {
            DB::table('tj_user_app')->updateOrInsert(
                ['id' => $testUserId],
                [
                    'prenom' => 'Rajesh',
                    'nom' => 'Kumar',
                    'phone' => '+919876543210',
                    'email' => 'rajesh.test@example.com',
                    'statut' => 'yes',
                ]
            );
        }

        // 2. User Books a Home Service (e.g. AC Repair & Maintenance)
        $bookingPayload = [
            'user_id' => $testUserId,
            'service_name' => 'AC Repair & Deep Cleaning',
            'address_type' => 'Flat 402, Green Heights, City Center',
            'lat' => 19.0760,
            'lng' => 72.8777,
            'date' => date('Y-m-d', strtotime('+1 day')),
            'time' => '10:30 AM',
            'description' => 'AC cooling issues and regular filter service required.',
            'booking_mode' => 'home_visit',
            'amount' => 450.00,
        ];

        $bookResponse = $this->postJson('/api/v1/book-service', $bookingPayload);

        $bookResponse->assertStatus(200)
            ->assertJson([
                'success' => 'success',
                'message' => 'Service request booked successfully.',
            ]);

        $bookingData = $bookResponse->json('data');
        $this->assertNotNull($bookingData);
        $bookingId = $bookingData['id'];
        $this->assertNotEmpty($bookingId);

        // Verify Database State: Initial status must be Pending
        $this->assertDatabaseHas('service_requests', [
            'id' => $bookingId,
            'user_id' => $testUserId,
            'service_name' => 'AC Repair & Deep Cleaning',
            'status' => 'Pending',
        ]);

        // 3. Service Provider checks Incoming Bookings feed
        $incomingResponse = $this->getJson("/api/v1/driver/bookings?id_driver={$testDriverId}&status=incoming");

        $incomingResponse->assertStatus(200)
            ->assertJson([
                'success' => 'success',
            ]);

        $incomingList = $incomingResponse->json('data');
        $foundInIncoming = false;
        foreach ($incomingList as $item) {
            if ((string) $item['id'] === (string) $bookingId && $item['type'] === 'service') {
                $foundInIncoming = true;
                $this->assertEquals('incoming', $item['status_group']);
                $this->assertEquals('Pending', $item['status']);
                break;
            }
        }
        $this->assertTrue($foundInIncoming, 'Newly booked home service must appear in provider incoming bookings.');

        // 4. Service Provider Accepts the Home Service Request
        $acceptResponse = $this->postJson('/api/v1/driver/bookings/service-status', [
            'id_driver' => $testDriverId,
            'booking_id' => $bookingId,
            'status' => 'accepted',
        ]);

        $acceptResponse->assertStatus(200)
            ->assertJson([
                'success' => 'success',
                'message' => 'Booking status updated',
            ]);

        // Verify Database State: Assigned to driver and status is Accepted
        $this->assertDatabaseHas('service_requests', [
            'id' => $bookingId,
            'driver_id' => $testDriverId,
            'status' => 'Accepted',
        ]);

        // 5. Provider checks Active Bookings feed
        $activeResponse = $this->getJson("/api/v1/driver/bookings?id_driver={$testDriverId}&status=active");
        $activeResponse->assertStatus(200);
        $activeList = $activeResponse->json('data');
        $foundInActive = false;
        foreach ($activeList as $item) {
            if ((string) $item['id'] === (string) $bookingId) {
                $foundInActive = true;
                $this->assertEquals('active', $item['status_group']);
                break;
            }
        }
        $this->assertTrue($foundInActive, 'Accepted booking must appear in active bookings.');

        // 6. Service Provider Starts the Service (In Progress)
        $startResponse = $this->postJson('/api/v1/driver/bookings/service-status', [
            'id_driver' => $testDriverId,
            'booking_id' => $bookingId,
            'status' => 'in_progress',
        ]);

        $startResponse->assertStatus(200)
            ->assertJson([
                'success' => 'success',
            ]);

        $this->assertDatabaseHas('service_requests', [
            'id' => $bookingId,
            'driver_id' => $testDriverId,
            'status' => 'In Progress',
        ]);

        // 7. Service Provider Completes the Service
        $completeResponse = $this->postJson('/api/v1/driver/bookings/service-status', [
            'id_driver' => $testDriverId,
            'booking_id' => $bookingId,
            'status' => 'completed',
        ]);

        $completeResponse->assertStatus(200)
            ->assertJson([
                'success' => 'success',
                'message' => 'Booking status updated',
            ]);

        // Verify Database State: Status is Completed
        $this->assertDatabaseHas('service_requests', [
            'id' => $bookingId,
            'driver_id' => $testDriverId,
            'status' => 'Completed',
        ]);

        // 8. Provider checks History Bookings feed
        $historyResponse = $this->getJson("/api/v1/driver/bookings?id_driver={$testDriverId}&status=history");
        $historyResponse->assertStatus(200);
        $historyList = $historyResponse->json('data');
        $foundInHistory = false;
        foreach ($historyList as $item) {
            if ((string) $item['id'] === (string) $bookingId) {
                $foundInHistory = true;
                $this->assertEquals('history', $item['status_group']);
                $this->assertEquals('Completed', $item['status']);
                break;
            }
        }
        $this->assertTrue($foundInHistory, 'Completed booking must appear in provider history.');

        // 9. User checks Service History to verify Completed status
        $userHistoryResponse = $this->getJson("/api/v1/service-history?user_id={$testUserId}");
        $userHistoryResponse->assertStatus(200)
            ->assertJson([
                'success' => 'success',
            ]);

        $userHistoryList = $userHistoryResponse->json('data');
        $foundInUserHistory = false;
        foreach ($userHistoryList as $item) {
            if ((string) $item['id'] === (string) $bookingId) {
                $foundInUserHistory = true;
                $this->assertEquals('Completed', $item['status']);
                $this->assertEquals((string) $testDriverId, (string) $item['driver_id']);
                break;
            }
        }
        $this->assertTrue($foundInUserHistory, 'User must see the completed service request in their history.');

        // Clean up test record
        ServiceRequest::where('id', $bookingId)->delete();
        if (Schema::hasTable('tj_user_app')) {
            DB::table('tj_user_app')->where('id', $testUserId)->delete();
        }
    }

    /**
     * Test 4: Concurrency Protection - Another provider cannot accept an already assigned booking
     */
    public function test_another_provider_cannot_hijack_assigned_booking(): void
    {
        $testUserId = 999112;
        $primaryProviderId = 888223;
        $intruderProviderId = 777334;

        // Create booking directly
        $booking = ServiceRequest::create([
            'user_id' => $testUserId,
            'driver_id' => $primaryProviderId,
            'service_name' => 'Electrician Emergency Repair',
            'address_type' => 'Home',
            'lat' => 19.0760,
            'lng' => 72.8777,
            'status' => 'Accepted',
        ]);

        // Intruder tries to accept or update status
        $intruderResponse = $this->postJson('/api/v1/driver/bookings/service-status', [
            'id_driver' => $intruderProviderId,
            'booking_id' => $booking->id,
            'status' => 'completed',
        ]);

        $intruderResponse->assertStatus(200)
            ->assertJson([
                'success' => 'error',
                'message' => 'This booking is assigned to another provider',
            ]);

        // Verify status remains unchanged
        $this->assertDatabaseHas('service_requests', [
            'id' => $booking->id,
            'driver_id' => $primaryProviderId,
            'status' => 'Accepted',
        ]);

        $booking->delete();
    }
}
