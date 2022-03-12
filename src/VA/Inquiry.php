<?php

namespace Esyede\BCA\VA;

use Esyede\BCA\Request\Credential;
use Esyede\BCA\Request\Helper;
use Esyede\BCA\Request\Request;
use Esyede\BCA\Request\Token;
use Esyede\BCA\Exceptions\BCAException;


class Inquiry
{
    private $request;
    private $query;

    /**
     * Constructor.
     *
     * @param Request $request Request instance
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    public function byCompanyCodeAndCustomerNumber($companyCode, $customerAccountNumber)
    {
        $endpoint = '/va/payments?CompanyCode=' . $companyCode . '&CustomerNumber=' . $customerAccountNumber;

        return $this->request->send('GET', $endpoint, []);
    }


    public function byCompanyCodeAndRequestId($companyCode, $requestId)
    {
        $endpoint = '/va/payments?CompanyCode=' . $companyCode . '&RequestID=' . $requestId;

        return $this->request->send('GET', $endpoint, []);
    }
}