<?php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Helpers\CurlHelper;

class TokenService
{
    /**
     * Retrieve the token.
     *
     * This private method is responsible for obtaining the token.
     *
     * @return string The token.
     */
    public function getToken()
    {
        if (Cache::has('debricked_token') && !$this->isTokenExpired()) {
            return Cache::get('debricked_token');
        }

        $curlHelper = new CurlHelper();
        $response = $curlHelper->makeRequest(
            env('DEBRICKED_API_URL').'login_check',
            'POST',
            [
                '_username' => env('DEBRICKED_USERNAME'),
                '_password' => env('DEBRICKED_PASSWORD'),
            ],
            [
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Bearer '.env('DEBRICKED_API_KEY'),
            ]
        );
    
        $responseArray = json_decode($response, true);
        $token = $responseArray['token'] ?? null;

        if ($token) {
            // Assuming the token expires in 1 hour
            Cache::put('debricked_token', $token, now()->addHour());
        }

        return $token;
    }

    /**
     * Checks if the token has expired.
     *
     * This private method determines whether the token is no longer valid based on its expiration time.
     *
     * @return bool Returns true if the token has expired, false otherwise.
     */
    private function isTokenExpired()
    {
        // Check if the token is expired
        // Assuming the token is stored with an expiration time
        return !Cache::has('debricked_token');
    }
}
