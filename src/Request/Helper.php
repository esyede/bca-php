<?php

namespace Esyede\BCA\Request;

use DateTime;
use DateTimeZone;
use Esyede\BCA\Exceptions\BCAException;

class Helper
{
    /**
     * Create signature
     *
     * @param Credential $credentials  Credential instance
     * @param string     $method       HTTP request method (GET or POST)
     * @param string     $relativeURL  Relative endpoint URL (ex: '/banking/corporates/transfers')
     * @param string     $accessToken  Access token from first request
     * @param array      $payloads     Associative array of request bodies
     *
     * @return string
     */
    public static function signature(
        Credential $credentials,
        $method,
        $relativeURL,
        $accessToken,
        array $payloads = []
    ) {
        $method = strtoupper($method);
        $relativeURL = '/' . ltrim($relativeURL, '/');
        $hash = '';

        if (count($payloads) > 0) {
            ksort($payloads);
            $hash = json_encode($payloads);
        }

        $hash = hash('sha256', $hash);
        $hash = strtolower($hash);

        $timestamp = static::dateIso8601();

        $stringToSign = $method . ':' . $relativeURL . ':' . $accessToken . ':' . $hash . ':' . $timestamp;
        $signature = hash_hmac('sha256', $stringToSign, $credentials->getApiSecret());

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

    /**
     * Get server's domain name.
     *
     * @param string $fallback
     *
     * @return string
     */
    public static function getDomain($fallback = 'localhost')
    {
        if (! isset($_SERVER['REQUEST_URI'])) {
            return $fallback;
        }

        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_HOST);
    }
}
