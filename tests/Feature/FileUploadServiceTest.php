<?php
namespace Tests\Unit\Services;

use App\Services\FileUploadService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class FileUploadServiceTest extends TestCase
{
    public function testUploadFileSuccessfully()
    {
        // Mock file data
        $file = $this->createMock(\Illuminate\Http\UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn('testfile.txt');
        $file->method('getPathname')->willReturn(resource_path('test-files/composer.lock'));

        // Mock Guzzle Client
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('post')
            ->willReturn(new Response(200, [], json_encode(['ciUploadId' => '123'])));

        // Instantiate the service with the mocked Client
        $fileUploadService = new FileUploadService($mockClient);

        $response = $fileUploadService->uploadFile($file, 'testToken');

        // Assert that the response contains the expected ciUploadId
        $this->assertEquals('123', $response['ciUploadId']);
    }
}
