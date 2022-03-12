<?php

require dirname(dirname(__DIR__)) . '/src/Request/Credential.php';
require dirname(dirname(__DIR__)) . '/src/Request/Helper.php';
require dirname(dirname(__DIR__)) . '/src/Request/Request.php';
require dirname(dirname(__DIR__)) . '/src/Request/Token.php';
require dirname(dirname(__DIR__)) . '/src/VA/Inquiry.php';

use Esyede\BCA\Request\Credential;
use Esyede\BCA\Request\Helper;
use Esyede\BCA\Request\Request;
use Esyede\BCA\Request\Token;
use Esyede\BCA\VA\Inquiry;


require dirname(__DIR__) . '/config.php';

$credentials = new Credential(
    $environment,
    $corporateId,
    $apiKey,
    $apiSecret,
    $clientId,
    $clientSecret
);

/*
|--------------------------------------------------------------------------
| Grant Access Token
|--------------------------------------------------------------------------
*/

$token = (new Token($credentials))->grant();
// echo json_encode($token);
$accessToken = $token->responses->data->access_token;



$request =  new Request($credentials, $accessToken);

/*
|--------------------------------------------------------------------------
| Get Inquiry (By Request ID & By By Customer Number)
|--------------------------------------------------------------------------
*/

$inquiry = new Inquiry($request);


$byRequestId = $inquiry->byCompanyCodeAndRequestId(80888, 1234567890);

$byCustomerNumber = $inquiry->byCompanyCodeAndCustomerNumber(80888, 8161964775);

echo json_encode(compact('byCustomerNumber', 'byRequestId'));
