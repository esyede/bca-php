<?php

namespace Esyede\BCA\Request;

class Request
{
    private $credentials;
    private $accessToken;
    private $dateIso8601;

    /**
     * Constructor.
     *
     * @param Credential $credentials
     * @param string     $accessToken
     */
    public function __construct(Credential $credentials, $accessToken)
    {
        $this->credentials = $credentials;
        $this->accessToken = $accessToken;
        $this->dateIso8601 = Helper::dateIso8601();
    }

    /**
     * Get Credential object.
     *
     * @return Credential
     */
    public function getCredential()
    {
        return $this->credentials;
    }

    /**
     * Get access token.
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Get IS08601 date.
     *
     * @return string
     */
    public function getDateIso8601()
    {
        return $this->dateIso8601;
    }

    /**
     * Send a GET request.
     *
     * @param string $endpoint
     * @param array  $payloads
     *
     * @return \stdClass
     */
    public function get($endpoint, array $payloads = [], $is_balance_info = false)
    {
        return $this->send('GET', $endpoint, $payloads, $is_balance_info);
    }

    /**
     * Send a POST request.
     *
     * @param string $endpoint
     * @param array  $payloads
     *
     * @return \stdClass
     */
    public function post($endpoint, array $payloads = [])
    {
        return $this->send('POST', $endpoint, $payloads);
    }

    /**
     * Send the request.
     *
     * @param string $method    Request method (GET or POST)
     * @param string $endpoint  Relative endpoint URL (ex: '/banking/corporates/transfers')
     * @param array  $payloads  Associative array of request bodies
     *
     * @return \stdClass
     */
    private function send($method, $endpoint, array $payloads = [], $is_balance_info = false)
    {
        if ($is_balance_info) {
            $payloads_signature = [];
        } else {
            $payloads_signature = $payloads;
        }

        if (count($payloads) > 0) {
            ksort($payloads);
        }

        $method = strtoupper($method);
        $signature = Helper::signature(
            $this->getCredential(),
            $method,
            $endpoint,
            $this->getAccessToken(),
            $this->getDateIso8601(),
            $payloads_signature
        );

        $headers = [
            'Authorization: Bearer ' . $this->getAccessToken(),
            'Content-Type: application/json',
            'Origin: ' . $this->getCredential()->getOriginDomain(),
            'X-BCA-Key: ' . $this->getCredential()->getApiKey(),
            'X-BCA-Signature: ' . $signature,
            'X-BCA-Timestamp: ' . $this->getDateIso8601(),
        ];

        $endpoint = $this->getCredential()->getBaseEndpoint() . $endpoint;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FAILONERROR => false,
        ]);

        if ('POST' === $method) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payloads));
        }

        $responses = curl_exec($ch);
        $errors = curl_error($ch);

        curl_close($ch);

        $decoded = json_decode($responses);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $decoded = false;
            $errors = 'Unable to decode json response.';
        }

        $results = [
            'endpoint' => $endpoint,
            'requests' => [
                'headers' => $headers,
                'payloads' => $payloads,
                'credentials' => $this->getCredential()->toArray(),
                'access_token' => $this->getAccessToken(),
                'signature' => $signature,
            ],
            'responses' => [
                'data' => $decoded,
                'errors' => $errors,
                'raw' => $responses
            ],
        ];

        // Force to \stdClass object
        return json_decode(json_encode($results));
    }
}
