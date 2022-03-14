<?php

require dirname(dirname(__DIR__)) . '/src/Request/Credential.php';
require dirname(dirname(__DIR__)) . '/src/Request/Helper.php';
require dirname(dirname(__DIR__)) . '/src/Request/Request.php';
require dirname(dirname(__DIR__)) . '/src/Request/Token.php';
require dirname(dirname(__DIR__)) . '/src/Banking/Banking.php';

use Esyede\BCA\Request\Credential;
use Esyede\BCA\Request\Helper;
use Esyede\BCA\Request\Request;
use Esyede\BCA\Request\Token;
use Esyede\BCA\Banking\Banking;

/*
|--------------------------------------------------------------------------
| Credentials Supplied By BCA
|--------------------------------------------------------------------------
*/

require dirname(__DIR__) . '/config.php';

$credentials = new Credential(
    $environment,
    $corporateId,
    $apiKey,
    $apiSecret,
    $clientId,
    $clientSecret,
    $originDomain
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
| Get Actual Account Statements
|--------------------------------------------------------------------------
*/

$banking = new Banking($request);

$startDate = '2016-01-29';
$endDate = '2017-01-30';

$statements = $banking->getAccountStatements(
    '0613005908',
    $startDate,
    $endDate
);

echo json_encode(compact('token', 'statements'));
