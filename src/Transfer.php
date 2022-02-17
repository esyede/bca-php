<?php

namespace Esyede\BCA;

class Transfer
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
     */
    public function __construct($accessToken, $apiKey, $signature)
    {
        $this->curl = (new Curl())
            ->withHeader('Authorization', 'Bearer ' . $accessToken)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Origin', 'tripay.co.id')
            ->withHeader('X-BCA-Key', $apiKey)
            ->withHeader('X-BCA-Timestamp', $this->dateIso8601())
            ->withHeader('X-BCA-Signature', $signature)
            ->returnResponseObject();
    }

    /**
     * Transfer ke sesama BCA.
     *
     * @param string $trxId                 Transaction ID unique per 90 days (using UTC+07 Time Zone). Format: Number, Must be 8 in length.
     * @param string $trxRefId              Sender’s transaction reference ID
     * @param string $senderAccountNumber   Source of Fund Account Number
     * @param string $receiverAccountNumber Account name to be credited (destination)
     * @param string $receiverBankCode      Bank Code of account to be credited (destination)
     * @param string $receiverFullname      Account name to be credited (destination)
     * @param int    $amount                Transfer amount. Format: Float Number, 13.2
     * @param string $transferType          Choose one: LLG or RTG
     * @param string $receiverAccountType   Choose one: 1 = Personal, 2 = Corporate, 3 = Government
     * @param string $receiverResidence     Choose one: 1 = Resident, 2 = Non Resident
     * @param string $remark1               Transfer remark for receiver (optional)
     * @param string $remark2               Transfer remark for receiver (optional)
     *
     * @return Curl
     */
    public function domestic(
        $trxId,
        $trxRefId,
        $senderAccountNumber,
        $receiverAccountNumber,
        $receiverBankCode,
        $receiverFullname,
        $amount,
        $transferType,
        $receiverAccountType,
        $receiverResidence,
        $remark1 = null,
        $remark2 = null
    ) {
        $request = $this->curl
            ->withHeader('ChannelID', $channelId)
            ->withHeader('CredentialID', $channelId);

        $payloads = [
            'TransactionID' => $trxId,
            'TransactionDate' => $this->dateTrx(),
            'ReferenceID' => $trxRefId,
            'SourceAccountNumber' => $senderAccountNumber,
            'BeneficiaryAccountNumber' => $receiverAccountNumber,
            'BeneficiaryBankCode' => $receiverBankCode,
            'BeneficiaryName' => $receiverFullname,
            'Amount' => $amount . '.00',
            'TransferType' => $transferType, // LLG atau RTG
            'BeneficiaryCustType' => $receiverAccountType, // '1' = Personal, '2' = Corporate, '3' = Goverment
            'BeneficiaryCustResidence' => $receiverResidence, // '1' = Resident, '2' = Non-resident
            'CurrencyCode' => 'idr',
            'Remark1' => is_null($remark1) ? '-' : substr($remark1, 18),
            'Remark2' => is_null($remark2) ? '-' : substr($remark2, 18),
        ];

        $request = $request->withData(json_encode($payloads, JSON_UNESCAPED_SLASHES));

        return $request->to($this->apiBaseUrl() . '/banking/corporates/transfers/v2/domestic')->post();
    }

    /**
     * Transfer ke bank lain.
     *
     * @param string $corporateId            Your KlikBCA Bisnis Corporate ID
     * @param string $senderAccountNumber    Source of Fund Account Number
     * @param string $trxId                  Transcation ID unique per day (using UTC+07 Time Zone). Format: Number
     * @param string $trxRefId               Sender’s transaction reference ID
     * @param int    $amount                 Transfer amount. Format: Float Number, 13.2
     * @param string $receiverAccountNumber  BCA Account number to be credited (Destination)
     *
     * @return Curl
     */
    public function foreign(
        $corporateId,
        $senderAccountNumber,
        $trxId,
        $trxRefId,
        $amount,
        $receiverAccountNumber
    ) {
        $request = $this->curl;

        $payloads = [
            'CorporateID' => $corporateId,
            'SourceAccountNumber' => $senderAccountNumber,
            'TransactionID' => $trxId,
            'TransactionDate' => $this->dateTrx(),
            'ReferenceID' => $trxRefId,
            'CurrencyCode' => 'idr',
            'Amount' => $amount,
            'BeneficiaryAccountNumber' => $receiverAccountNumber,
            'Remark1' => is_null($remark1) ? '-' : substr($remark1, 18),
            'Remark2' => is_null($remark2) ? '-' : substr($remark2, 18),
        ];

        $request = $request->withData(json_encode($payloads, JSON_UNESCAPED_SLASHES));

        return $request->to($this->apiBaseUrl() . '/banking/corporates/transfers')->post();
    }
}
