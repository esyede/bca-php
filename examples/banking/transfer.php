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


$amount = 100000;
$fromAccountNumber = '0613005827';
$toAccountNumber = '0613005878';
$numericTrxId = rand(11111111, 99999999);
$trxReferenceId = '20000001';
$remark1 = 'Note one';
$remark2 = 'Note two';

/*
|--------------------------------------------------------------------------
| Grant Access Token
|--------------------------------------------------------------------------
*/

$banking = new Banking($request);
$transfer = $banking->transfer(
    $amount,
    $fromAccountNumber,
    $toAccountNumber,
    $numericTrxId,
    $trxReferenceId,
    $remark1,
    $remark2
);

echo json_encode(compact('token', 'transfer'));
