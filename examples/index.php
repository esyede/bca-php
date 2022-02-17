<?php

require '../src/Curl.php';
require '../src/ApiUtilityTrait.php';
require '../src/Auth.php';
require '../src/Transfer.php';
require '../src/Inquiry.php';


use Esyede\BCA\Curl;
use Esyede\BCA\ApiUtilityTrait;
use Esyede\BCA\Auth;
use Esyede\BCA\Transfer;
use Esyede\BCA\Inquiry;

$auth = new Auth();


// ---------------------------------------------------------------------------
// Domestic Transfer (BCA ke BCA)
// ---------------------------------------------------------------------------

// Step 1: Mendapatkan access token
$clientId = 'CLIENT-ID-HERE';
$clientSecret = 'CLIENT-SECRET-HERE';

$oauth = $auth->grant($clientId, $clientSecret);
// var_dump($oauth); die;


// Step 2: Membuat signature menggunakan access token yang didapat dari request pertama
$httpMenthod = 'POST';
$relativeUrl = '/banking/corporates/transfers/v2/domestic'; // relative url untuk domestic transfer
$apiSecret = 'API-SECRET-HERE';
$accessToken = $oauth->content->access_token;
$requestBody = [
    'foo' => 'bar',
    'baz' => 'qux',
];

$signature = $auth->sign($httpMenthod, $relativeUrl, $apiSecret, $accessToken, $requestBody);
// var_dump($signature); die;


// Step 3: Melakukan request domestic transfer
$transfer = new Transfer($accessToken, $apiSecret, $signature);
// $transfer->enableDebug(__DIR__ . '/transfer-domestic.txt'); // Aktifkan log debug

$trxId = 'TRANSACTION-ID-HERE';
$senderAccountNumber = 'SENDER-ACCOUNT-NUMBER-HERE';
$receiverAccountNumber = 'RECEIVER-ACCOUNT-NUMBER-HERE';
$receiverBankCode = 'RECEIVER-BANK-CODE-HERE';
$receiverFullname = 'RECEIVER-FULL-NAME-HERE';
$amount = '123.45'; // String, must contains 2 decimal point at the back
$transferType = 'TRANSFER-TYPE-HERE';
$remark1 = 'Lorem ipsum';
$remark2 = 'dolor sit amet';

$resulst = $transfer->domestic(
    $trxId,
    $trxRefId,
    $senderAccountNumber,
    $receiverAccountNumber,
    $receiverBankCode,
    $receiverFullname,
    $amount,
    $transferType,
    $remark1,
    $remark2
);

// var_dump($result); die;



// ---------------------------------------------------------------------------
// Foreign Transfer (BCA ke Bank Lain)
// ---------------------------------------------------------------------------

// Step 1: Mendapatkan access token
$clientId = 'CLIENT-ID-HERE';
$clientSecret = 'CLIENT-SECRET-HERE';

$oauth = $auth->grant($clientId, $clientSecret);
// var_dump($oauth); die;


// Step 2: Membuat signature menggunakan access token yang didapat dari request pertama
$httpMenthod = 'POST';
$relativeUrl = '/banking/corporates/transfers'; // relative url untuk foreign transfer
$apiSecret = 'API-SECRET-HERE';
$accessToken = $oauth->content->access_token;
$requestBody = [
    'foo' => 'bar',
    'baz' => 'qux',
];

$signature = $auth->sign($httpMenthod, $relativeUrl, $apiSecret, $accessToken, $requestBody);
// var_dump($signature); die;


// Step 3: Melakukan request foreign transfer
$transfer = new Transfer($accessToken, $apiSecret, $signature);
// $transfer->enableDebug(__DIR__ . '/transfer-foreign.txt'); // Aktifkan log debug 

$corporateId = 'CORPORATE-ID-HERE';
$senderAccountNumber = 'SENDER-ACCOUNT-NUMBER-HERE';
$trxId = 'TRANSACTION-ID-HERE';
$amount = '543.21'; // String, must contains 2 decimal point at the back
$receiverAccountNumber = 'RECEIVER-ACCOUNT-NUMBER-HERE';

$result = $transfer->foreign(
    $corporateId,
    $senderAccountNumber,
    $trxId,
    $trxRefId,
    $amount,
    $receiverAccountNumber
);

// var_dump($result); die;



// ---------------------------------------------------------------------------
// Inquiry Domestic Account
// ---------------------------------------------------------------------------

// Step 1: Mendapatkan access token
$clientId = 'CLIENT-ID-HERE';
$clientSecret = 'CLIENT-SECRET-HERE';

$oauth = $auth->grant($clientId, $clientSecret);
// var_dump($oauth); die;


// Step 2: Membuat signature menggunakan access token yang didapat dari request pertama
$httpMenthod = 'GET';
$receiverBankCode = 'RECEIVER-BANK-CODE-HERE';
$receiverAccountNumber = 'RECEIVER-ACCOUNT-NUMBER-HERE';
$relativeUrl = '/banking/corporates/transfers/v2/domestic/beneficiaries/banks/' . $receiverBankCode . '/accounts/' . $receiverAccountNumber; // relative url untuk inquiry
$apiSecret = 'API-SECRET-HERE';
$accessToken = $oauth->content->access_token;
$requestBody = [
    'foo' => 'bar',
    'baz' => 'qux',
];

$signature = $auth->sign($httpMenthod, $relativeUrl, $apiSecret, $accessToken, $requestBody);
// var_dump($signature); die;


// Step 3: Melakukan request inquiry
$apiKey = 'API-KEY-HERE';
$inquiry = new Inquiry($accessToken, $apiKey, $signature, $originDomain);
// $transfer->enableDebug(__DIR__ . '/inquiry-domestic.txt'); // Aktifkan log debug 

$corporateId = 'CORPORATE-ID-HERE';
$senderAccountNumber = 'SENDER-ACCOUNT-NUMBER-HERE';
$trxId = 'TRANSACTION-ID-HERE';
$amount = '543.21'; // String, must contains 2 decimal point at the back
$receiverAccountNumber = 'RECEIVER-ACCOUNT-NUMBER-HERE';

$result = $inquiry->domestic(
    $channelId,
    $credentialId,
    $receiverAccountNumber,
    $receiverBankCode
);

// var_dump($result); die;
