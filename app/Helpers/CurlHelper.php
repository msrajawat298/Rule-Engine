<?php

namespace App\Helpers;

/**
 * Class CurlHelper
 *
 * A helper class to make HTTP requests using cURL.
 */
class CurlHelper
{
    protected $curlInit;
    protected $curlExec;
    protected $curlError;
    protected $curlClose;

    public function __construct($curlInit = 'curl_init', $curlExec = 'curl_exec', $curlError = 'curl_error', $curlClose = 'curl_close')
    {
        $this->curlInit = $curlInit;
        $this->curlExec = $curlExec;
        $this->curlError = $curlError;
        $this->curlClose = $curlClose;
    }

    /**
     * Makes an HTTP request using cURL.
     *
     * @param string $url The URL to which the request is made.
     * @param string $method The HTTP method to use for the request (default is 'GET').
     * @param array $data The data to send with the request (default is an empty array).
     * @param array $headers The headers to send with the request (default is an empty array).
     *
     * @return string The response from the cURL request.
     *
     * @throws \Exception If there is an error during the cURL request.
     */
    public function makeRequest($url, $method = 'GET', $data = [], $headers = [])
    {
        $curl = call_user_func($this->curlInit);
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = call_user_func($this->curlExec, $curl);
        $error = call_user_func($this->curlError, $curl);
        call_user_func($this->curlClose, $curl);

        if ($error) {
            throw new \Exception("cURL Error: $error");
        }

        return $response;
    }
}