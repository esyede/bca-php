<?php

namespace Esyede\BCA;

use DateTime;
use DateTimeZone;
use DateTimeInterface;
use Unirest\Request as UnirestRequest;
use Unirest\Request\Body as UnirestBody;

class Request
{
    private static $timezone = 'Asia/Jakarta';
    private static $port = 443;
    private static $hostName = 'sandbox.bca.co.id';
    private static $scheme = 'https';
    private static $timeOut = 60;
    private static $curlOptions = [
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSLVERSION => 6,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 60,
    ];

    /**
     * Default BCA Settings.
     *
     * @var array
     */
    protected $settings = [
        'corp_id' => '',
        'client_id' => '',
        'client_secret' => '',
        'api_key' => '',
        'secret_key' => '',
        'curl_options' => [],
        'options' => [
            'host' => 'sandbox.bca.co.id',
            'scheme' => 'https',
            'timeout' => 60,
            'port' => 443,
            'timezone' => 'Asia/Jakarta'
        ],
    ];

    /**
     * Konstruktor.
     *
     * @param string $corpId       Corporate ID
     * @param string $clientId     Client ID
     * @param string $clientSecret Client secret
     * @param string $apiKey       API key
     * @param string $secretKey    Secret key
     * @param array  $options      Curl option tambahan
     */
    public function __construct($corpId, $clientId, $clientSecret, $apiKey, $secretKey, array $options = [])
    {
        // Required parameters.
        $this->settings['corp_id'] = $corpId;
        $this->settings['client_id'] = $clientId;
        $this->settings['client_secret'] = $clientSecret;
        $this->settings['api_key'] = $apiKey;
        $this->settings['secret_key'] = $secretKey;
        $this->settings['host'] = preg_replace('/http[s]?\:\/\//', '', $this->settings['host'], 1);

        foreach ($options as $key => $value) {
            if (isset($this->settings[$key])) {
                $this->settings[$key] = $value;
            }
        }

        if (isset($options['scheme'])) {
            $this->settings['options']['scheme'] = $options['scheme'];
        } else {
            $this->settings['options']['scheme'] = static::getScheme();
        }

        if (isset($options['host'])) {
            $this->settings['options']['host'] = $options['host'];
        } else {
            $this->settings['options']['host'] = static::getHostName();
        }

        if (isset($options['port'])) {
            $this->settings['options']['port'] = $options['port'];
        } else {
            $this->settings['options']['port'] = static::getPort();
        }

        if (isset($options['timezone'])) {
            $this->settings['options']['timezone'] = $options['timezone'];
        } else {
            $this->settings['options']['timezone'] = static::getTimeZone();
        }

        if (isset($options['timeout'])) {
            $this->settings['options']['timeout'] = $options['timeout'];
        } else {
            $this->settings['options']['timeout'] = static::getTimeOut();
        }

        UnirestRequest::curlOpts(static::$curlOptions);

        if (! empty($this->settings['curl_options'])) {
            $data = static::mergeCurlOptions(static::$curlOptions, $this->settings['curl_options']);
            UnirestRequest::curlOpts($data);
        }
    }

    /**
     * Get setting array.
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }


    /**
     * Build ddn domain
     *
     * @return string
     */
    private function ddnDomain()
    {
        return $this->settings['scheme'] . '://' . $this->settings['host'] . ':' . $this->settings['port'] . '/';
    }

    /**
     * Generate oauth ke server.
     *
     * @return UnirestResponse
     */
    public function authenticate()
    {
        $clientId = $this->settings['client_id'];
        $clientSecret = $this->settings['client_secret'];
        $token = base64_encode($clientId . ':' . $clientSecret);

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . $token,
        ];

        $endpoint = $this->ddnDomain() . 'api/oauth/token';
        $body = UnirestBody::form(['grant_type' => 'client_credentials']);

        $response = UnirestRequest::post($endpoint, $headers, $body);

        return $response;
    }

    /**
     * Ambil informasi saldo berdasarkan nomor akun BCA.
     *
     * @param  string $oauthToken      Nilai token yang didapatkan setelah login
     * @param  array  $sourceAccountId Nomor akun yang akan dicek
     *
     * @return UnirestRequest
     */
    public function getBalanceInfo($oauthToken, $sourceAccountId = [])
    {
        $corpId = $this->settings['corp_id'];

        $this->validateArray($sourceAccountId);

        ksort($sourceAccountId);

        $accountIds = implode(',', $sourceAccountId);
        $accountIds = urlencode($accountIds);

        $uriSign = 'GET:/banking/v3/corporates/' . $corpId . '/accounts/' . $accountIds;
        $isoTime = static::makeIsoTime();
        $signature = static::makeSignature($uriSign, $oauthToken, $this->settings['secret_key'], $isoTime, null);

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $oauthToken,
            'X-BCA-Key' => $this->settings['api_key'],
            'X-BCA-Timestamp' => $isoTime,
            'X-BCA-Signature' => $signature,
        ];

        $endpoint = $this->ddnDomain() . 'banking/v3/corporates/' . $corpId . '/accounts/' . $accountIds;
        $body = UnirestBody::form(['grant_type' => 'client_credentials']);

        $response = UnirestRequest::get($endpoint, $headers, $body);

        return $response;
    }

    /**
     * Ambil daftar transaksi per tanggal.
     *
     * @param  string $oauthToken    Nilai token yang didapatkan setelah login
     * @param  array  $sourceAccount Nomor akun yang akan dicek
     * @param  string $startDate     Tanggal awal
     * @param  string $endDate       Tanggal akhir
     *
     * @return UnirestResponse
     */
    public function getAccountStatement($oauthToken, $sourceAccount, $startDate, $endDate)
    {
        $corpId = $this->settings['corp_id'];
        $uriSign = 'GET:/banking/v3/corporates/' . $corpId . '/accounts/' . $sourceAccount . '/statements?EndDate=' . $endDate . '&StartDate=' . $startDate;

        $isoTime = static::makeIsoTime();
        $signature = static::makeSignature($uriSign, $oauthToken, $this->settings['secret_key'], $isoTime, null);

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $oauthToken,
            'X-BCA-Key' => $this->settings['api_key'],
            'X-BCA-Timestamp' => $isoTime,
            'X-BCA-Signature' => $signature,
        ];

        $endpoint = $this->ddnDomain() . 'banking/v3/corporates/' . $corpId . '/accounts/' . $sourceAccount . '/statements?EndDate=' . $endDate . '&StartDate=' . $startDate;
        $body = UnirestBody::form(['grant_type' => 'client_credentials']);

        $response = UnirestRequest::get($endpoint, $headers, $body);

        return $response;
    }

    /**
     * Ambil informasi ATM berdasarkan lokasi GEO.
     *
     * @param  string $oauthToken  Nilai token yang didapatkan setelah login
     * @param  string $latitude    Titik garis lintang GPS
     * @param  string $longitude   Titik garis bujur GPS
     * @param  string $count       Jumlah ATM BCA yang akan ditampilkan
     * @param  string $radius      Nilai radius dari lokasi GEO
     *
     * @return UnirestResponse
     */
    public function getAtmLocation($oauthToken, $latitude, $longitude, $count = '10', $radius = '20')
    {

        $params = [
            'SearchBy' => 'Distance',
            'Latitude' => $latitude,
            'Longitude' => $longitude,
            'Count' => $count,
            'Radius' => $radius,
        ];

        ksort($params);

        $authQuery = static::implodes('=', '&', $params);
        $uriSign = 'GET:/general/info-bca/atm?' . $authQuery;
        $isoTime = static::makeIsoTime();
        $signature = static::makeSignature($uriSign, $oauthToken, $this->settings['secret_key'], $isoTime, null);

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $oauthToken,
            'X-BCA-Key' => $this->settings['api_key'],
            'X-BCA-Timestamp' => $isoTime,
            'X-BCA-Signature' => $signature,
        ];

        $endpoint = $this->ddnDomain() . 'general/info-bca/atm?SearchBy=Distance&Latitude=' . $latitude . '&Longitude=' . $longitude . '&Count=' . $count . '&Radius=' . $radius;
        $body = UnirestBody::form(['grant_type' => 'client_credentials']);

        $response = UnirestRequest::get($endpoint, $headers, $body);

        return $response;
    }

    /**
     * Transfer dana kepada akun yang berbeda bank dengan jumlah nominal tertentu.
     *
     * @param string $oauthToken                 Nilai token yang telah didapatkan setelah login.
     * @param string $channelId                  Unknown description.
     * @param int    $amount                     Nilai dana dalam RUPIAH yang akan ditransfer, Format: 13.2
     * @param string $sourceAccountNumber        Source of Fund Account Number
     * @param string $beneficiaryAccountNumber   BCA Account number to be credited (Destination)
     * @param string $beneficiaryBankCode        Kode Bank to be credited (Destination)
     * @param string $beneficiaryCustResidence   1 = Resident 2 = Non Resident *mandatory, if transfer_type = LLG/RTG
     * @param string $beneficiaryCustType        1 = Personal 2 = Corporate 3 = Government *mandatory, if transfer_type = LLG/RTG
     * @param string $beneficiaryName            Nama penerima.
     * @param string $beneficiaryEmail           Email penerima.
     * @param string $transactionID              Transcation ID unique per day (using UTC+07 Time Zone). Format: Number
     * @param string $transactionType            ONL (Switching) ; LLG; RTG (RTGS)
     * @param string $remark1                    Transfer remark for receiver
     * @param string $remark2                    Transfer remark for receiver
     * @param string $currencyCode               Kode mata uang (optional)
     *
     * @return UnirestResponse
     */
    public function fundTransfersDomestic(
        $oauthToken,
        $channelId,
        $amount,
        $sourceAccountNumber,
        $beneficiaryAccountNumber,
        $beneficiaryBankCode,
        $beneficiaryCustResidence,
        $beneficiaryCustType,
        $beneficiaryName,
        $beneficiaryEmail,
        $transactionID,
        $transactionType,
        $remark1,
        $remark2,
        $currencyCode = 'IDR'
    )
    {
        $uriSign = 'POST:/banking/corporates/transfers/v2/domestic';
        $isoTime = static::makeIsoTime();

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $oauthToken,
            'X-BCA-Key' => $this->settings['api_key'],
            'X-BCA-Timestamp' => $isoTime,
            'channel-id' => $channelId,
            'credential-id' => $this->settings['corp_id'],
        ];

        $endpoint = $this->ddnDomain() . 'banking/corporates/transfers/v2/domestic';

        $bodies = [
            'amount' => $amount,
            'beneficiary_account_number' => strtolower(str_replace(' ', '', $beneficiaryAccountNumber)),
            'beneficiary_bank_code' => strtolower(str_replace(' ', '', $beneficiaryBankCode)),
            'beneficiary_cust_residence' => $beneficiaryCustResidence,
            'beneficiary_cust_type' => $beneficiaryCustType,
            'beneficiary_name' => strtolower(str_replace(' ', '', $beneficiaryName)),
        ];

        if (empty($beneficiaryEmail) || $beneficiaryEmail === '') {
            $bodies['beneficiary_email'] = '';
        } else {
            $bodies['beneficiary_email'] = strtolower(str_replace(' ', '', $beneficiaryEmail));
        }

        $bodies['currency_code'] = $currencyCode;
        $bodies['remark1'] = empty($remark1) ? '' : strtolower(str_replace(' ', '', $remark1));
        $bodies['remark1'] = empty($remark2) ? '' : strtolower(str_replace(' ', '', $remark2));
        $bodies['source_account_number'] = strtolower(str_replace(' ', '', $sourceAccountNumber));
        $bodies['transaction_date'] = static::makeTransactionDate();
        $bodies['transaction_id'] = strtolower(str_replace(' ', '', $transactionID));
        $bodies['transfer_type'] = strtoupper(str_replace(' ', '', $transactionType));

        // Harus disort agar mudah kalkulasi HMAC
        ksort($bodies);

        $signature = static::makeSignature($uriSign, $oauthToken, $this->settings['secret_key'], $isoTime, $bodies);
        $headers['X-BCA-Signature'] = $signature;

        // Supaya jangan strip 'ReferenceID', '/' jadi '/\' karena HMAC akan menjadi tidak cocok
        $json = json_encode($bodies, JSON_UNESCAPED_SLASHES);
        $body = UnirestBody::form($json);

        $response = UnirestRequest::post($endpoint, $headers, $body);

        return $response;
    }

    /**
     * Ambil KURS mata uang.
     *
     * @param string $oauthToken  Nilai token yang telah didapatkan setelah login
     * @param string $rateType    Type rate
     * @param string $currency    Mata uang
     *
     * @return UnirestResponse
     */
    public function getForexRate($oauthToken, $rateType = 'e-rate', $currency = 'USD')
    {
        $params = [
            'RateType' => strtoupper($rateType),
            'Currency' => strtoupper($currency),
        ];

        ksort($params);

        $authQuery = static::implodes('=', '&', $params);

        $uriSign = 'GET:/general/rate/forex?' . $authQuery;
        $isoTime = static::makeIsoTime();
        $signature = static::makeSignature($uriSign, $oauthToken, $this->settings['secret_key'], $isoTime, null);

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $oauthToken,
            'X-BCA-Key' => $this->settings['api_key'],
            'X-BCA-Timestamp' => $isoTime,
            'X-BCA-Signature' => $signature,
        ];

        $endpoint = $this->ddnDomain() . 'general/rate/forex?' . $authQuery;
        $body = UnirestBody::form(['grant_type' => 'client_credentials']);

        $response = UnirestRequest::get($endpoint, $headers, $body);

        return $response;
    }

    /**
     * Transfer dana kepada akun lain dengan jumlah nominal tertentu.
     *
     * @param string $oauthToken               Nilai token yang telah didapatkan setelah login
     * @param int    $amount                   Nilai dana dalam RUPIAH yang akan ditransfer, Format: 13.2
     * @param string $beneficiaryAccountNumber BCA Account number to be credited (Destination)
     * @param string $referenceID              Sender's transaction reference ID
     * @param string $remark1                  Transfer remark for receiver
     * @param string $remark2                  Transfer remark for receiver
     * @param string $sourceAccountNumber      Source of Fund Account Number
     * @param string $transactionID            Transcation ID unique per day (using UTC+07 Time Zone). Format: Number
     * @param string $currencyCode             Kode mata uang (optional)
     *
     * @return UnirestResponse
     */
    public function fundTransfers(
        $oauthToken,
        $amount,
        $sourceAccountNumber,
        $beneficiaryAccountNumber,
        $referenceID,
        $remark1,
        $remark2,
        $transactionID,
        $currencyCode = 'idr'
    )
    {
        $uriSign = 'POST:/banking/corporates/transfers';
        $isoTime = static::makeIsoTime();

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $oauthToken,
            'X-BCA-Key' => $this->settings['api_key'],
            'X-BCA-Timestamp' => $isoTime,
        ];

        $endpoint = $this->ddnDomain() . 'banking/corporates/transfers';

        $bodies = [
            'Amount' => $amount,
            'BeneficiaryAccountNumber' => strtolower(str_replace(' ', '', $beneficiaryAccountNumber)),
            'CorporateID' => strtolower(str_replace(' ', '', $this->settings['corp_id'])),
            'CurrencyCode' => $currencyCode,
            'ReferenceID' => strtolower(str_replace(' ', '', $referenceID)),
            'Remark1' => strtolower(str_replace(' ', '', $remark1)),
            'Remark2' => strtolower(str_replace(' ', '', $remark2)),
            'SourceAccountNumber' => strtolower(str_replace(' ', '', $sourceAccountNumber)),
            'TransactionDate' => $isoTime,
            'TransactionID' => strtolower(str_replace(' ', '', $transactionID)),
        ];

        // Harus disort agar mudah kalkulasi HMAC
        ksort($bodies);

        $signature = static::makeSignature($uriSign, $oauthToken, $this->settings['secret_key'], $isoTime, $bodies);
        $headers['X-BCA-Signature'] = $signature;

        // Supaya jangan strip 'ReferenceID', '/' jadi '/\' karena HMAC akan menjadi tidak cocok
        $json = json_encode($bodies, JSON_UNESCAPED_SLASHES);
        $body = UnirestBody::form($json);

        $response = UnirestRequest::post($endpoint, $headers, $body);

        return $response;
    }

    /**
     * Realtime deposit untuk produk BCA.
     *
     * @param string $oauthToken Nilai token yang telah didapatkan setelah login
     *
     * @return UnirestResponse
     */
    public function getDepositRate($oauthToken)
    {
        $uriSign = 'GET:/general/rate/deposit';
        $isoTime = static::makeIsoTime();
        $signature = static::makeSignature($uriSign, $oauthToken, $this->settings['secret_key'], $isoTime, null);

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $oauthToken,
            'X-BCA-Key' => $this->settings['api_key'],
            'X-BCA-Timestamp' => $isoTime,
            'X-BCA-Signature' => $signature,
        ];

        $endpoint = $this->ddnDomain() . 'general/rate/deposit';
        $body = UnirestBody::form(['grant_type' => 'client_credentials']);

        $response = UnirestRequest::get($endpoint, $headers, $body);

        return $response;
    }

    /**
     * Generate signature.
     *
     * @param string $url             Url yang akan disign.
     * @param string $auth_token      Nilai token dari login.
     * @param string $secretKey       Secret key yang telah diberikan oleh BCA.
     * @param string $isoTime         Waktu ISO8601.
     * @param array|mixed $bodyToHash Body yang akan dikirimkan ke server BCA.
     *
     * @return string
     */
    public static function makeSignature($url, $auth_token, $secretKey, $isoTime, $bodyToHash = [])
    {
        $hash = hash('sha256', '');

        if (is_array($bodyToHash)) {
            ksort($bodyToHash);

            $json = json_encode($bodyToHash, JSON_UNESCAPED_SLASHES);
            $hash = hash('sha256', $json);
        }

        $stringToSign = $url . ':' . $auth_token . ':' . $hash . ':' . $isoTime;
        $result = hash_hmac('sha256', $stringToSign, $secretKey, false);

        return $result;
    }

    /**
     * Set time zone.
     *
     * @param string $timeZone Time yang akan dipergunakan.
     *
     * @return string
     */
    public static function setTimeZone($timeZone)
    {
        static::$timezone = $timeZone;
        return static::$timezone;
    }

    /**
     * Get timezone.
     *
     * @return string
     */
    public static function getTimeZone()
    {
        return static::$timezone;
    }

    /**
     * Set nama domain BCA yang akan dipergunakan.
     *
     * @param string $hostName nama domain BCA yang akan dipergunakan.
     *
     * @return string
     */
    public static function setHostName($hostName)
    {
        static::$hostName = $hostName;
        return static::$hostName;
    }

    /**
     * Ambil nama domain BCA yang akan dipergunakan.
     *
     * @return string
     */
    public static function getHostName()
    {
        return static::$hostName;
    }

    /**
     * Ambil maximum execution time.
     *
     * @return string
     */
    public static function getTimeOut()
    {
        return static::$timeOut;
    }

    /**
     * Ambil nama domain BCA yang akan dipergunakan.
     *
     * @return string
     */
    public static function getCurlOptions()
    {
        return static::$curlOptions;
    }

    /**
     * Setup curl options.
     *
     * @param array $curlOpts Curl options
     *
     * @return array
     */
    public static function setCurlOptions(array $curlOpts = [])
    {
        $data = static::mergeCurlOptions(static::$curlOptions, $curlOpts);
        static::$curlOptions = $data;
        return static::$curlOptions;
    }

    /**
     * Set Ambil maximum execution time.
     *
     * @param int $timeOut Timeout in milisecond.
     *
     * @return string
     */
    public static function setTimeOut($timeOut)
    {
        static::$timeOut = $timeOut;
        return static::$timeOut;
    }

    /**
     * Set BCA port
     *
     * @param int $port Port yang akan dipergunakan
     *
     * @return int
     */
    public static function setPort($port)
    {
        static::$port = $port;
        return static::$port;
    }

    /**
     * Get BCA port
     *
     * @return int
     */
    public static function getPort()
    {
        return static::$port;
    }

    /**
     * Set BCA Schema
     *
     * @param int $scheme Scheme yang akan dipergunakan
     *
     * @return string
     */
    public static function setScheme($scheme)
    {
        static::$scheme = $scheme;
        return static::$scheme;
    }

    /**
     * Get BCA Schema
     *
     * @return string
     */
    public static function getScheme()
    {
        return static::$scheme;
    }

    /**
     * Generate ISO 8601 Time.
     *
     * @return string
     */
    public static function makeIsoTime()
    {
        $date = new DateTime('now', new DateTimeZone(static::getTimeZone()));

        date_default_timezone_set(static::getTimeZone());

        $fmt = $date->format('Y-m-d\TH:i:s');
        $iso8601 = sprintf('%s.%s%s', $fmt, substr(microtime(), 2, 3), date('P'));

        return $iso8601;
    }

    /**
     * Generate ISO 8601 time.
     *
     * @return string
     */
    public static function makeTransactionDate()
    {
        $date = new DateTime('now', new DateTimeZone(static::getTimeZone()));

        date_default_timezone_set(static::getTimeZone());

        $fmt = $date->format('Y-m-d');

        return $fmt;
    }

    /**
     * Merge from existing array.
     *
     * @param array $existing_options
     * @param array $new_options
     *
     * @return array
     */
    private static function mergeCurlOptions(&$existing_options, $new_options)
    {
        $existing_options = $new_options + $existing_options;
        return $existing_options;
    }

    /**
     * Validasi jika clientsecret telah didefinsikan.
     *
     * @param array $sourceAccountId
     *
     * @return bool
     */
    private function validateArray($sourceAccountId = [])
    {
        if (! is_array($sourceAccountId)) {
            throw new BcaRequestException('Data harus array.');
        }

        if (empty($sourceAccountId)) {
            throw new BcaRequestException('AccountNumber cannot be empty.');
        } else {
            if (count($sourceAccountId) > 20) {
                throw new BcaRequestException('Maksimal Account Number is 20');
            }
        }

        return true;
    }

    /**
     * Implode an array with the key and value pair giving
     * a glue, a separator between pairs and the array to implode.
     *
     * @param string $glue      The glue between key and value
     * @param string $separator Separator between pairs
     * @param array  $array     The array to implode
     *
     * @return string
     */
    public static function implodes($glue, $separator, $array = [])
    {
        if (! is_array($array)) {
            throw new BcaRequestException('Data should be an array.');
        }

        if (empty($array)) {
            throw new BcaRequestException('The $array parameters must be a non-empty array.');
        }

        foreach ($array as $key => $val) {
            $val = is_array($val) ? implode(',', $val) : $val;
            $string[] = "{$key}{$glue}{$val}";
        }

        return implode($separator, $string);
    }
}