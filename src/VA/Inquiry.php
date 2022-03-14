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
     * Get inquiry info by company code and customer account number.
     *
     * @param string $companyCode
     * @param string $customerAccountNumber
     *
     * @return \stdClass
     */
    public function byCompanyCodeAndCustomerNumber($companyCode, $customerAccountNumber)
    {
        $endpoint = '/va/payments?CompanyCode=' . $companyCode . '&CustomerNumber=' . $customerAccountNumber;

        return $this->request->get($endpoint, []);
    }

    /**
     * Get inquiry info by company code and request id (supplied by BCA).
     *
     * @param string $companyCode
     * @param string $requestId
     *
     * @return \stdClass
     */
    public function byCompanyCodeAndRequestId($companyCode, $requestId)
    {
        $endpoint = '/va/payments?CompanyCode=' . $companyCode . '&RequestID=' . $requestId;

        return $this->request->get($endpoint, []);
    }
}
