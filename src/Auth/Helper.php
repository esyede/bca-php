<?php

namespace Esyede\BCA\Auth;

use DateTime;
use DateTimeZone;
use Esyede\BCA\Exceptions\BCAException;

class Helper
{
    /**
     * Create signature
     *
     * @param Credential $credentials  Credential instance
     * @param string     $httpMethod   HTTP request method (GET or POST)
     * @param string     $relativeURL  Relative endpoint URL (ex: '/banking/corporates/transfers')
     * @param string     $accessToken  Access token from first request
     * @param array      $payloads     Associative array of request bodies
     *
     * @return string
     */
    public static function signature(
        Credential $credentials,
        $httpMethod,
        $relativeURL,
        $accessToken,
        array $payloads = []
    ) {
        $httpMethod = strtoupper($httpMethod);
        $relativeURL = '/' . ltrim($relativeURL, '/');
        $hash = '';

        if (! empty($payloads)) {
            ksort($payloads);
            $hash = json_encode($payloads);
        }

        $hash = hash('sha256', $hash);
        $hash = strtolower($hash);

        $timestamp = static::dateIso8601();

        $data = $httpMethod . ':' . $relativeURL . ':' . $accessToken . ':' . $hash . ':' . $timestamp;
        $apiSecret = $credentials->getApiSecret();

        $signature = hash_hmac('sha256', $data, $apiSecret);

        return $signature;
    }

    /**
     * Generate ISO8601 date.
     *
     * @param string $time      Datetime string
     * @param string $timezone  Time zone
     *
     * @return string
     */
    public static function dateIso8601($time = 'now', $timezone = 'UTC')
    {
        return (new DateTime($time, new DateTimeZone($timezone)))->format('Y-m-d\TH:i:s.vP');

    }

    /**
     * Generate transaction date.
     *
     * @param string $time      Datetime string
     * @param string $timezone  Time zone
     *
     * @return string
     */
    public static function dateTrx($time = 'now', $timezone = 'Asia/Jakarta')
    {
        return (new DateTime($time, new DateTimeZone($timezone)))->format('Y-m-d');
    }
}