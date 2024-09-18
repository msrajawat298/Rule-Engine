<?php
namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FileUploadService
{
    private $client;
    private $url;

    public function __construct(Client $client) {
        $this->client = $client;
        $this->url = env('DEBRICKED_API_URL');
    }

    public function uploadFile($file, $token) {
        $fileName = $file->getClientOriginalName();
        try {
            $response = $this->client->post($this->url . '1.0/open/uploads/dependencies/files', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'accept' => '*/*',
                ],
                'multipart' => [
                    [
                        'name' => 'commitName',
                        'contents' => 'testing commit message'
                    ],
                    [
                        'name' => 'productName',
                        'contents' => 'Rule Engine'
                    ],
                    [
                        'name' => 'fileData',
                        'contents' => fopen($file->getPathname(), 'r'),
                        'filename' => $fileName,
                    ],
                ]
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Error uploading file', ['error' => $e->getMessage()]);
            throw new \Exception('Error uploading file: ' . $e->getMessage());
        }
    }
}
