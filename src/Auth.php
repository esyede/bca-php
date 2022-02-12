<?php

namespace Esyede\BCA;

class Auth
{
    use ApiUtilityTrait;

    /**
     * Dapatkan oauth token.
     *
     * @param string $clientId
     * @param string $clientSecret
     *
     * @return Curl
     */
    public function grant($clientId, $clientSecret)
    {
        $endpoint = $this->apiBaseUrl() . '/api/oauth/token';

        $response = (new Curl())
            ->to($endpoint)
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withHeader('Authorization', 'Basic ' . base64_encode($clientId . ':' . $clientSecret))
            ->withData(['grant_type' => 'client_credentials'])
            ->returnResponseObject()
            ->post();
    }

    /**
     * Buat signature.
     *
     * @param string       $httpMenthod
     * @param string       $relativeUrl
     * @param string       $apiSecret
     * @param string       $accessToken
     * @param array|string $requestBody
     *
     * @return string
     */
    public function sign($httpMenthod, $relativeUrl, $apiSecret, $accessToken, $requestBody = [])
    {
        $hash = hash('sha256', '');

        if (is_array($requestBody)) {
            ksort($requestBody);
            $json = json_encode($requestBody, JSON_UNESCAPED_SLASHES);
            $hash = hash('sha256', $json);
        }

        $stringToSign = strtoupper($httpMenthod) . ':' . $relativeUrl . ':' . $accessToken . ':' . strtolower($hash);
        $stringToSign = str_replace(' ', '', $stringToSign);

        $result = hash_hmac('sha256', $stringToSign, $apiSecret, false);
        $timestamp = $this->dateIso8601();

        return $result;
    }
}