<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use App\Services\TokenService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

/**
 * Class CheckScanStatus
 *
 * This job is responsible for checking the scan status.
 * It implements the ShouldQueue interface, indicating that it should be queued for background processing.
 *
 * @package App\Jobs
 */
class CheckScanStatus implements ShouldQueue
{
    use Queueable, Dispatchable;

    private $url;
    private $ciUploadId;
    private $tokenService;

    /**
     * Constructor for the CheckScanStatus job.
     *
     * @param int $ciUploadId The ID of the CI upload to check the scan status for.
     */
    public function __construct(TokenService $tokenService, $ciUploadId)
    {
        $this->url        = env('DEBRICKED_API_URL');
        $this->ciUploadId = $ciUploadId;
        $this->tokenService = $tokenService;
    }

    /**
     * Handles the job to check the scan status.
     *
     * This method sends a GET request to the specified URL to check the status of a scan.
     * It logs the scan status and sends an email if the number of vulnerabilities found exceeds 5.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();
        $response = $client->get($this->url.'1.0/open/ci/upload/status', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->tokenService->getToken(),
                'accept' => '*/*',
            ],
            'query' => [
                'ciUploadId' => $this->ciUploadId
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        Log::info('Scan status', ['data' => $data]);
        if ($data['vulnerabilitiesFound'] > 5) {
            // Send an email using MailHog
            Mail::raw("Scan completed with {$data['vulnerabilitiesFound']} vulnerabilities.", function ($message) {
                $message->to('help@opentext.com')->subject('Vulnerability Scan Completed');
            });
        }
    }
}
