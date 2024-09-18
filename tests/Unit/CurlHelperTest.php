<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;
use App\Helpers\CurlHelper;

class CurlHelperTest extends TestCase
{
    public function test_make_request_successful()
    {
        // Mock the cURL functions
        $curlInit = $this->createMockCurlInit();
        $curlExec = $this->createMockCurlExec('{"success": true}');
        $curlError = $this->createMockCurlError('');
        $curlClose = $this->createMockCurlClose();

        $curlHelper = new CurlHelper($curlInit, $curlExec, $curlError, $curlClose);

        $response = $curlHelper->makeRequest('http://example.com', 'GET', [], []);

        $this->assertEquals('{"success": true}', $response);
    }

    public function test_make_request_throws_exception_on_error()
    {
        // Mock the cURL functions
        $curlInit = $this->createMockCurlInit();
        $curlExec = $this->createMockCurlExec(false);
        $curlError = $this->createMockCurlError('Some cURL error');
        $curlClose = $this->createMockCurlClose();

        $curlHelper = new CurlHelper($curlInit, $curlExec, $curlError, $curlClose);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('cURL Error: Some cURL error');

        $curlHelper->makeRequest('http://example.com', 'GET', [], []);
    }

    private function createMockCurlInit()
    {
        return function() {
            return curl_init();
        };
    }

    private function createMockCurlExec($returnValue)
    {
        return function($curl) use ($returnValue) {
            return $returnValue;
        };
    }

    private function createMockCurlError($returnValue)
    {
        return function($curl) use ($returnValue) {
            return $returnValue;
        };
    }

    private function createMockCurlClose()
    {
        return function($curl) {
            curl_close($curl);
        };
    }
}