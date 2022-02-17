<?php

namespace Esyede\BCA;

class Inquiry
{
    use ApiUtilityTrait;

    /**
     * Curl object.
     *
     * @var Curl
     */
    private $curl;

    /**
     * Konstruktor.
     *
     * @param string $accessToken  Access token from OAuth2 request response
     * @param string $apiKey       Api Key
     * @param string $signature    Signature created from the first request
     * @param string $originDomain Origin requester domain, ex: yourdomain.com
     */
    public function __construct($accessToken, $apiKey, $signature, $originDomain)
    {
        $this->curl = (new Curl())
            ->withHeader('Authorization', 'Bearer ' . $accessToken)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Origin', $originDomain)
            ->withHeader('X-BCA-Key', $apiKey)
            ->withHeader('X-BCA-Timestamp', $this->dateIso8601())
            ->withHeader('X-BCA-Signature', $signature)
            ->returnResponseObject();
    }

    /**
     * Ambi informasi akun bank penerima transfer.
     *
     * @param string $channelId             Channel Identification Number (Ex: 95051 for KlikBCA Bisnis)
     * @param string $credentialId          Your Channel Identity (ex: Your KlikBCA Bisnis CorporateID)
     * @param string $receiverAccountNumber Beneficiary (Receiver) Account Number
     * @param string $receiverBankCode      Beneficiary (Receiver) Bank Code 
     *
     * @return Curl
     */
    public function domestic($channelId, $credentialId, $receiverAccountNumber, $receiverBankCode)
    {
        $endpoint = $this->apiBaseUrl() . '/banking/corporates/transfers/v2/domestic/beneficiaries/banks/' .
            $receiverBankCode . '/accounts/' . $receiverAccountNumber;

        return $this->curl
            ->withHeader('ChannelID', $channelId)
            ->withHeader('CredentialID', $credentialId)
            ->to($endpoint)
            ->returnResponseObject()
            ->get();
    }
}
