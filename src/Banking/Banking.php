<?php

namespace Esyede\BCA\Banking;

use Esyede\BCA\Request\Credential;
use Esyede\BCA\Request\Helper;
use Esyede\BCA\Request\Request;
use Esyede\BCA\Request\Token;
use Esyede\BCA\Exceptions\BCAException;

class Banking
{
    private $request;

    /**
     * Constructor.
     *
     * @param Request $request Request instance
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
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

        $accountNumbers = implode(',', $accountNumbers);
        $accountNumbers = urlencode($accountNumbers);

        $corporateId = $this->request->getCredential()->getCorporateId();
        $endpoint = '/banking/v3/corporates/' . $corporateId . '/accounts/' . $accountNumbers;

        return $this->request->send('GET', $endpoint, []);
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
        $corporateId = $this->request->getCredential()->getCorporateId();
        $endpoint = '/banking/v3/corporates/' . $corporateId . '/accounts/' . $accountNumber . '/statements';
        $endpoint .= '?EndDate=' . $endDate . '&StartDate=' . $startDate;

        return $this->request->send('GET', $endpoint, []);
    }


    /**
     * Transfer to another account.
     *
     * @param int    $amount             Numeric, positive (ex: 100000)
     * @param string $fromAccountNumber  Sender's account number (ex: 12345678)
     * @param string $toAccountNumber    Receiver's account number (ex: 11223344)
     * @param string $numericTrxId       Numeric only (ex: 00000001)
     * @param string $trxReferenceId     Numeric only (ex: 123)
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
        $trxReferenceId,
        $remark1 = 'N/A',
        $remark2 = 'N/A'
    ) {
        $endpoint = '/banking/corporates/transfers';
        $payloads = [
            'Amount' => $amount . '.00',
            'BeneficiaryAccountNumber' => strval($toAccountNumber),
            'CorporateID' => strval($this->request->getCredential()->getCorporateId()),
            'SourceAccountNumber' => strval($fromAccountNumber),
            'CurrencyCode' => 'IDR',
            'TransactionID' => strval($numericTrxId),
            'TransactionDate' => Helper::dateTrx(),
            'ReferenceID' => strval($trxReferenceId),
            'Remark1' => strval($remark1),
            'Remark2' => strval($remark2),
        ];

        return $this->request->send('POST', $endpoint, $payloads);
    }
}
