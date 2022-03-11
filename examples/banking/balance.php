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
| Get Account's Balance Info
|--------------------------------------------------------------------------
*/

$banking = new Banking($credentials, $accessToken);
$balanceInfo = $banking->getBalanceInfo([
    // Max. 20 accounts number per request
    '1234567890',
    '0611104625',
    '5544332211',
]);

echo json_encode(compact('token', 'balanceInfo'));
