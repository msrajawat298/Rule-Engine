<?php
namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ScanService
{
    private $client;
    private $url;

    public function __construct(Client $client) {
        $this->client = $client;
        $this->url = env('DEBRICKED_API_URL');
    }

    public function finishUpload($ciUploadId, $token) {
        if (!$ciUploadId) {
            Log::warning('ciUploadId is missing', ['ciUploadId' => $ciUploadId]);
            return;
        }
        $response = $this->client->post($this->url . '1.0/open/finishes/dependencies/files/uploads', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'accept' => 'application/json',
            ],
            'multipart' => [
                [
                    'name' => 'ciUploadId',
                    'contents' => $ciUploadId
                ],
                [
                    'name' => 'returnCommitData',
                    'contents' => false
                ],
            ]
        ]);
        Log::info('File Scanning started', ['Scanning Started' => json_decode($response->getBody(), true)]);
    }

    public function getStatus($ciUploadId, $token) {
        $response = $this->client->get($this->url . '1.0/open/ci/upload/status', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'accept' => '*/*',
            ],
            'query' => [
                'ciUploadId' => $ciUploadId
            ]
        ]);
        return json_decode($response->getBody(), true);
    }
}
