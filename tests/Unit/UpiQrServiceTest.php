<?php

namespace Tests\Unit;

use App\Services\UpiQrService;
use PHPUnit\Framework\TestCase;

class UpiQrServiceTest extends TestCase
{
    public function test_generate_upi_string_for_user()
    {
        $uri = UpiQrService::generateUpiStringForUser('708012345678', 'Rahul Sharma');

        $this->assertStringStartsWith('upi://pay?', $uri);
        $this->assertStringContainsString('tr=708012345678', $uri);
        $this->assertStringContainsString('cu=INR', $uri);
        $this->assertStringContainsString('pa=', $uri);
    }

    public function test_extract_ac_no_from_plain_string()
    {
        $extracted = UpiQrService::extractAcNoFromScannedString('708012345678');
        $this->assertEquals('708012345678', $extracted);
    }

    public function test_extract_ac_no_from_upi_intent_uri()
    {
        $upiUri = 'upi://pay?pa=fiinway@icici&pn=Fiinway+-+Rahul&tr=708098765432&tn=Fiinway+Wallet+708098765432&cu=INR';
        $extracted = UpiQrService::extractAcNoFromScannedString($upiUri);
        $this->assertEquals('708098765432', $extracted);
    }

    public function test_extract_ac_no_from_json_string()
    {
        $json = json_encode(['ac_no' => '706011223344', 'amount' => 100]);
        $extracted = UpiQrService::extractAcNoFromScannedString($json);
        $this->assertEquals('706011223344', $extracted);
    }

    public function test_extract_ac_no_from_arbitrary_string_containing_pocket_number()
    {
        $text = 'Pay to pocket 708055443322 for services';
        $extracted = UpiQrService::extractAcNoFromScannedString($text);
        $this->assertEquals('708055443322', $extracted);
    }
}
