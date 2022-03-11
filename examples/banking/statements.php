<?php

require dirname(dirname(__DIR__)) . '/src/Auth/Credential.php';
require dirname(dirname(__DIR__)) . '/src/Auth/Helper.php';
require dirname(dirname(__DIR__)) . '/src/Auth/Token.php';
require dirname(dirname(__DIR__)) . '/src/Banking/Banking.php';

use Esyede\BCA\Auth\Credential;
use Esyede\BCA\Auth\Helper;
use Esyede\BCA\Auth\Token;
use Esyede\BCA\Banking\Banking;

/*
|--------------------------------------------------------------------------
| Credentials Supplied By BCA
|--------------------------------------------------------------------------
*/

$environment  = 'development'; // 'development' or 'production'
$corporateId  = 'CORP_ID';
$apiKey       = 'API_KEY';
$apiSecret    = 'API_SECRET';
$clientId     = 'CLIENT_ID';
$clientSecret = 'CLIENT_SECRET';

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


/*
|--------------------------------------------------------------------------
| Get Actual Account Statements
|--------------------------------------------------------------------------
*/

$banking = new Banking($credentials, $accessToken);

$startDate = '2022-02-28';
$endDate = '2022-03-10';

$statements = $banking->getAccountStatements(
    '0611104625',
    $startDate,
    $endDate
);

echo json_encode(compact('token', 'statements'));
