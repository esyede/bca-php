<?php

require dirname(dirname(__DIR__)) . '/src/Credential.php';
require dirname(dirname(__DIR__)) . '/src/Helper.php';
require dirname(dirname(__DIR__)) . '/src/Token.php';
require dirname(dirname(__DIR__)) . '/src/Banking.php';

use Esyede\BCA\Credential;
use Esyede\BCA\Helper;
use Esyede\BCA\Token;
use Esyede\BCA\Banking;

/*
|--------------------------------------------------------------------------
| Credentials Supplied By BCA
|--------------------------------------------------------------------------
*/

$environment  = 'development'; // 'development' or 'production'
$corporateId  = 'CORPORATE-ID-HERE';
$apiKey       = 'API-KEY-HERE';
$apiSecret    = 'API-SECRET-HERE';
$clientId     = 'CLIENT-ID-HERE';
$clientSecret = 'CLIENT-SECRET-HERE';

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
    '1122334455',
    '5544332211',
]);

echo json_encode(compact('token', 'balanceInfo'));
