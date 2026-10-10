<?php

namespace Tests\Unit;

use App\Services\UpiQrService;
use App\Http\Controllers\API\v1\UserProfileUpdateController;
use PHPUnit\Framework\TestCase;

class UpiQrServiceTest extends TestCase
{
    // ==========================================
    // 1. UPI QR String Generation Test Cases
    // ==========================================

    public function test_generate_upi_string_standard()
    {
        $uri = UpiQrService::generateUpiStringForUser('708012345678', 'Rahul Sharma');

        $this->assertStringStartsWith('upi://pay?', $uri);
        $this->assertStringContainsString('tr=708012345678', $uri);
        $this->assertStringContainsString('cu=INR', $uri);
        $this->assertStringContainsString('pa=', $uri);
        $this->assertStringContainsString('Rahul+Sharma', $uri);
        $this->assertStringContainsString('Fiinway+Wallet+708012345678', $uri);
    }

    public function test_generate_upi_string_without_user_name()
    {
        $uri = UpiQrService::generateUpiStringForUser('706099887766');

        $this->assertStringStartsWith('upi://pay?', $uri);
        $this->assertStringContainsString('tr=706099887766', $uri);
        $this->assertStringContainsString('cu=INR', $uri);
    }

    public function test_generate_upi_string_with_empty_or_null_ac_no()
    {
        $this->assertSame('', UpiQrService::generateUpiStringForUser(''));
        $this->assertSame('', UpiQrService::generateUpiStringForUser(null));
        $this->assertSame('', UpiQrService::generateUpiStringForUser('   '));
    }

    public function test_generate_upi_string_with_special_characters_in_name()
    {
        $uri = UpiQrService::generateUpiStringForUser('708011223344', 'Amit & Sumit Services');

        $this->assertStringStartsWith('upi://pay?', $uri);
        $this->assertStringContainsString('tr=708011223344', $uri);
        // Special characters should be URL encoded
        $this->assertStringNotContainsString(' ', $uri);
    }

    // ==========================================
    // 2. AcNo Extraction Test Cases (Universal Scenarios)
    // ==========================================

    public function test_extract_ac_no_from_plain_12_digit_number()
    {
        $extracted = UpiQrService::extractAcNoFromScannedString('708012345678');
        $this->assertEquals('708012345678', $extracted);
    }

    public function test_extract_ac_no_with_surrounding_whitespace()
    {
        $extracted = UpiQrService::extractAcNoFromScannedString("  708012345678 \n\t ");
        $this->assertEquals('708012345678', $extracted);
    }

    public function test_extract_ac_no_from_phonepe_gpay_upi_uri_with_tr()
    {
        $upiUri = 'upi://pay?pa=fiinway@icici&pn=Fiinway+-+Rahul&tr=708098765432&tn=Fiinway+Wallet+708098765432&cu=INR';
        $extracted = UpiQrService::extractAcNoFromScannedString($upiUri);
        $this->assertEquals('708098765432', $extracted);
    }

    public function test_extract_ac_no_from_upi_uri_with_custom_prefixed_tr()
    {
        $upiUri = 'upi://pay?pa=fiinway@icici&pn=Fiinway&tr=FW708098765432&cu=INR';
        $extracted = UpiQrService::extractAcNoFromScannedString($upiUri);
        $this->assertEquals('708098765432', $extracted);
    }

    public function test_extract_ac_no_from_upi_uri_fallback_to_tn()
    {
        // UPI URI where tr is missing or a gateway order ID, but tn has the account number
        $upiUri = 'upi://pay?pa=fiinway@icici&pn=Fiinway&tr=ORDER123456&tn=Fiinway+Wallet+708033221100&cu=INR';
        $extracted = UpiQrService::extractAcNoFromScannedString($upiUri);
        $this->assertEquals('708033221100', $extracted);
    }

    public function test_extract_ac_no_from_json_string()
    {
        $json = json_encode(['ac_no' => '706011223344', 'amount' => 100]);
        $extracted = UpiQrService::extractAcNoFromScannedString($json);
        $this->assertEquals('706011223344', $extracted);
    }

    public function test_extract_ac_no_from_nested_json()
    {
        $json = json_encode(['data' => ['user' => ['ac_no' => '708088776655']]]);
        // When not direct top-level ac_no, fallback regex finds the 7080 series
        $extracted = UpiQrService::extractAcNoFromScannedString($json);
        $this->assertEquals('708088776655', $extracted);
    }

    public function test_extract_ac_no_from_arbitrary_string()
    {
        $text = 'Pay to pocket 708055443322 for services rendered';
        $extracted = UpiQrService::extractAcNoFromScannedString($text);
        $this->assertEquals('708055443322', $extracted);
    }

    public function test_extract_ac_no_empty_and_null_inputs()
    {
        $this->assertSame('', UpiQrService::extractAcNoFromScannedString(''));
        $this->assertSame('', UpiQrService::extractAcNoFromScannedString(null));
        $this->assertSame('', UpiQrService::extractAcNoFromScannedString('   '));
    }

    public function test_extract_ac_no_with_no_account_number_returns_original()
    {
        $randomText = 'Hello World Simple String';
        $extracted = UpiQrService::extractAcNoFromScannedString($randomText);
        $this->assertEquals($randomText, $extracted);
    }

    // ==========================================
    // 3. Transaction History Enricher Test Cases
    // ==========================================

    public function test_transaction_history_enrichment_for_upi_payment()
    {
        $controller = new UserProfileUpdateController();
        $reflector = new \ReflectionClass($controller);
        $method = $reflector->getMethod('enrichTransactionHistoryRow');

        $fakeRow = (object) [
            'id'             => 105,
            'amount'         => '250.00',
            'id_user_app'    => 12,
            'deduction_type' => 1,
            'payment_method' => 'UPI',
            'payment_status' => 'success',
            'description'    => 'Payment received from Sagar (sagar@okhdfcbank) via UPI',
            'note'           => 'Razorpay UPI QR pay_PxY123456789',
            'type'           => 'credit',
            'creer'          => '2026-10-10 11:00:00',
            'counterparty'   => 'Sagar',
            'txn_id'         => 'pay_PxY123456789',
        ];

        $enriched = $method->invoke($controller, $fakeRow, '12', 'customer');

        $this->assertEquals('UPI Payment Received', $enriched['category_title']);
        $this->assertEquals('From Sagar', $enriched['counterparty']);
        $this->assertEquals('upi', $enriched['icon_type']);
        $this->assertEquals('1', $enriched['deduction_type']);
        $this->assertEquals('Paid', $enriched['status_label']);
        $this->assertEquals('pay_PxY123456789', $enriched['transaction_id']);
        $this->assertEquals('250.00', $enriched['amount']);
    }

    public function test_transaction_history_enrichment_for_internal_wallet_transfer()
    {
        $controller = new UserProfileUpdateController();
        $reflector = new \ReflectionClass($controller);
        $method = $reflector->getMethod('enrichTransactionHistoryRow');

        $fakeRow = (object) [
            'id'             => 106,
            'amount'         => '100.00',
            'id_user_app'    => 12,
            'deduction_type' => 1,
            'payment_method' => 'Wallet',
            'payment_status' => 'success',
            'description'    => 'Received ₹100.00 from Ramesh Kumar',
            'note'           => '',
            'type'           => 'credit',
            'creer'          => '2026-10-10 10:30:00',
            'counterparty'   => 'Ramesh Kumar',
            'txn_id'         => '70801234',
        ];

        $enriched = $method->invoke($controller, $fakeRow, '12', 'customer');

        $this->assertEquals('Money Received', $enriched['category_title']);
        $this->assertEquals('Ramesh Kumar', $enriched['counterparty']);
        $this->assertEquals('transfer', $enriched['icon_type']);
        $this->assertEquals('1', $enriched['deduction_type']);
        $this->assertEquals('Paid', $enriched['status_label']);
    }
}
