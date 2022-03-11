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


$amount = 100000;
$fromAccountNumber = '0201245680';
$toAccountNumber = '0201245681';
$numericTrxId = '00000001';
$remark1 = 'Transfer test';
$remark2 = 'For testing only';

/*
|--------------------------------------------------------------------------
| Grant Access Token
|--------------------------------------------------------------------------
*/

$banking = new Banking($credentials, $accessToken);
$transfer = $banking->transfer(
    $amount,
    $fromAccountNumber,
    $toAccountNumber,
    $numericTrxId,
    $remark1,
    $remark2
);

echo json_encode(compact('token', 'transfer'));
