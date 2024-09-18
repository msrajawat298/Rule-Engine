<?php

namespace App\Http\Controllers;

use App\Jobs\CheckScanStatus;
use App\Services\FileUploadService;
use App\Services\ScanService;
use App\Services\TokenService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Class UploadController
 *
 * This controller handles the file upload functionality.
 * It extends the base Controller class provided by the framework.
 *
 * @package App\Http\Controllers
 */

class UploadController extends Controller
{
    private $fileUploadService;
    private $scanService;
    private $token;
    private $tokenService;

    public function __construct(
        FileUploadService $fileUploadService,
        ScanService $scanService,
        TokenService $tokenService
    ) {
        $this->fileUploadService = $fileUploadService;
        $this->scanService = $scanService;
        $this->tokenService = $tokenService;
        $this->token = $tokenService->getToken();
    }

    /**
     * Handle the file upload request.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the file to be uploaded.
     * @return \Illuminate\Http\Response The response after processing the file upload.
     */
    public function upload(Request $request) {
        $request->validate([
            'files.*' => 'required|file',
        ]);

        $ciUploadIds = [];
        foreach ($request->file('files') as $file) {
            try {
                $uploadResponse = $this->fileUploadService->uploadFile($file, $this->token);

                if (isset($uploadResponse['ciUploadId'])) {
                    $ciUploadId = $uploadResponse['ciUploadId'];
                    $ciUploadIds[$file->getClientOriginalName()] = $ciUploadId;

                    $this->scanService->finishUpload($ciUploadId, $this->token);

                    CheckScanStatus::dispatch($this->tokenService, $ciUploadId)->delay(now()->addMinutes(1));
                }
            } catch (\Exception $e) {
                Mail::raw('Error uploading file: ' . $e->getMessage(), function ($message) {
                    $message->to('user@opentext.com')->subject('Error Uploading File');
                });
                return response()->json([
                    'message' => 'Error uploading file',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        Log::info('Files uploaded successfully', ['ciUploadId' => $ciUploadIds]);
        return response()->json([
            'message' => 'Files uploaded successfully, scan started',
            'ciUploadId' => $ciUploadIds
        ]);
    }
}