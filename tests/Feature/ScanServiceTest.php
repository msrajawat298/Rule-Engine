<?php

namespace Tests\Unit\Services;

use App\Services\ScanService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Container\Attributes\Log;
use PHPUnit\Framework\TestCase;

class ScanServiceTest extends TestCase
{
    public function testGetStatusSuccessfully()
    {
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('get')
            ->willReturn(new Response(200, [], json_encode([
                'progress' => 50,
                'vulnerabilitiesFound' => 2,
                'unaffectedVulnerabilitiesFound' => 1,
                'detailsUrl' => 'http://example.com/details'
            ])));

        $scanService = new ScanService($mockClient);
        $status = $scanService->getStatus('123', 'testToken');

        $this->assertEquals(50, $status['progress']);
        $this->assertEquals(2, $status['vulnerabilitiesFound']);
    }
}
