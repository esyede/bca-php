<?php

namespace Esyede\BCA\Request;

class Token
{
    private $credentials;

    /**
     * Constructor.
     *
     * @param Credential $credentials  Credential instance
     */
    public function __construct(Credential $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * Grant access token.
     *
     * @return \stdClass
     */
    public function grant()
    {
        $endpoint = $this->credentials->getBaseEndpoint() . '/api/oauth/token';
        $creds = $this->credentials->getClientId() . ':' . $this->credentials->getClientSecret();
        $headers = [
            'Authorization: Basic ' . base64_encode($creds),
            'Content-Type: application/x-www-form-urlencoded',
            // 'Host: devapi.klikbca.com',
        ];

        $payloads = [
            'grant_type' => 'client_credentials',
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_HEADER => false,
            CURLOPT_FAILONERROR => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => http_build_query($payloads),
        ]);

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
                'credentials' => $this->credentials->toArray(),
            ],
            'responses' => [
                'data' => $decoded,
                'errors' => $errors
            ],
        ];

        // Force to \stdClass object
        return json_decode(json_encode($results));
    }
}