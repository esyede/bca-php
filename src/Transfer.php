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
     * @param string $accessToken
     * @param string $apiKey
     * @param string $signature
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
     * @param string $trxId
     * @param string $trxRefId
     * @param string $senderAccountNumber
     * @param string $receiverAccountNumber
     * @param string $receiverBankCode
     * @param string $receiverFullname
     * @param int    $amount
     * @param string $transferType
     * @param string $remark1
     * @param string $remark2
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
     * @param string $corporateId
     * @param string $senderAccountNumber
     * @param string $trxId
     * @param string $trxRefId
     * @param int    $amount
     * @param string $receiverAccountNumber
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