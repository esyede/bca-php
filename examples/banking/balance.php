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
| Get Account's Balance Info
|--------------------------------------------------------------------------
*/

$banking = new Banking($request);
$balanceInfo = $banking->getBalanceInfo([
    // Max. 10 accounts number per request
    '0613005908',
    '0613005878',
    '0613005827',
    '0613005860',
    '0613005916',
    '0611115813',
    '0611112504',
    '0611112466',
    '0611112512',
    '0611112555',
]);

echo json_encode(compact('token', 'balanceInfo'));
