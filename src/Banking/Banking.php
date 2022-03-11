<?php

namespace Esyede\BCA\Banking;

use Esyede\BCA\Auth\Credential;
use Esyede\BCA\Auth\Helper;
use Esyede\BCA\Auth\Token;
use Esyede\BCA\Exceptions\BCAException;

class Banking
{
    private $credentials;
    private $accessToken;

    /**
     * Constructor.
     *
     * @param Credential $credentials  Credential instance
     * @param string     $accessToken  Access token
     */
    public function __construct(Credential $credentials, $accessToken)
    {
        $this->credentials = $credentials;
        $this->accessToken = $accessToken;
    }

    /**
     * Get account's balance info.
     *
     * @param array $accountNumbers  Numeric, max. 20 characters (ex: 1234567890)
     *
     * @return \stdClass
     */
    public function getBalanceInfo(array $accountNumbers = [])
    {
        $accountNumbers = array_values($accountNumbers);

        if (count($accountNumbers) > 20) {
            throw new BCAException('Maximum account for checking is 20 per request.');
        }

        if (count($accountNumbers) > 1) {
            $accountNumbers = implode(',', $accountNumbers);
            $accountNumbers = urlencode($accountNumbers);
        }

        $corporateId = $this->credentials->getCorporateId();
        $endpoint = '/banking/v3/corporates/' . $corporateId . '/accounts/' . $accountNumbers;

        return $this->request('GET', $endpoint, []);
    }

    /**
     * Get account statements.
     *
     * @param string $accountNumber  Numeric, max. 20 characters (ex: 12345678)
     * @param string $startDate      Format Y-m-d (ex: 2022-01-01)
     * @param string $endDate        Format Y-m-d (ex: 2022-01-02)
     *
     * @return \stdClass
     */
    public function getAccountStatements($accountNumber, $startDate, $endDate)
    {
        $dateRange = ['StartDate' => $startDate, 'EndDate' => $endDate];

        // Gak mau jalan kalo gak di sortir menurut alfabet
        ksort($dateRange);

        $corporateId = $this->credentials->getCorporateId();
        $endpoint = '/banking/v3/corporates/' . $corporateId . '/accounts/' . $accountNumber . '/statements';
        $endpoint .= '?' . http_build_query($dateRange);

        return $this->request('GET', $endpoint, []);
    }


    /**
     * Transfer ke akun lain.
     *
     * @param int    $amount             Numeric, positive (ex: 100000)
     * @param string $fromAccountNumber  Sender's account number (ex: 12345678)
     * @param string $toAccountNumber    Receiver's account number (ex: 11223344)
     * @param string $numericTrxId       Numeric only (ex: 00000001)
     * @param string $remark1            Optional
     * @param string $remark2            Optional
     *
     * @return \stdClass
     */
    public function transfer(
        $amount,
        $fromAccountNumber,
        $toAccountNumber,
        $numericTrxId,
        $remark1 = 'N/A',
        $remark2 = 'N/A'
    ) {
        $endpoint = '/banking/corporates/transfers';
        $payloads = [
            'Amount' => $amount . '.00',
            'BeneficiaryAccountNumber' => $toAccountNumber,
            'CorporateID' => $this->credentials->getCorporateId(),
            'SourceAccountNumber' => $fromAccountNumber,
            'CurrencyCode' => 'IDR',
            'TransactionID' => $numericTrxId,
            'TransactionDate' => Helper::dateTrx(),
            'ReferenceID' => '123',
            'Remark1' => $remark1,
            'Remark2' => $remark2,
        ];

        return $this->request('POST', $endpoint, $payloads);
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
    private function request($method, $endpoint, array $payloads = [])
    {
        if (count($payloads) > 0) {
            ksort($payloads);
        }

        $method = strtoupper($method);
        $signature = Helper::signature(
            $this->credentials,
            $method,
            $endpoint,
            $this->accessToken,
            $payloads
        );

        $timestamp = Helper::dateIso8601();
        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
            'Origin: localhost',
            'X-BCA-Key: ' . $this->credentials->getApiKey(),
            'X-BCA-Signature: ' . $signature,
            'X-BCA-Timestamp: ' . $timestamp
        ];

        $endpoint = $this->credentials->getBaseEndpoint() . $endpoint;

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
                'credentials' => $this->credentials->toArray(),
                'access_token' => $this->accessToken,
                'signature' => $signature,
            ],
            'responses' => [
                'data' => $decoded,
                'errors' => $errors,
            ],
        ];

        // Force to \stdClass object
        return json_decode(json_encode($results));
    }
}