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
     * @param string $accessToken
     * @param string $apiKey
     * @param string $signature
     * @param string $originDomain
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
     * @param string $channelId
     * @param string $credentialId
     * @param string $receiverAccountNumber
     * @param string $receiverBankCode
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