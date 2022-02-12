<?php

require '../src/Curl.php';
require '../src/ApiUtilityTrait.php';
require '../src/Auth.php';
require '../src/Transfer.php';
require '../src/Inquiry.php';



$auth = new Esyede\BCA\Auth();


// ---------------------------------------------------------------------------
// Ambil oauth token
// ---------------------------------------------------------------------------

$clientId = 'client-id';
$clientSecret = 'client-secret';

$oauth = $auth->grant($clientId, $clientSecret);



// TODO: Kasih contoh bro!

// ---------------------------------------------------------------------------
// BCA mengharuskan pembuatan signature secara unik setiap UNTUK endpointnya.
// ---------------------------------------------------------------------------

// Contoh signature untuk transfer dari BCA
$httpMenthod = 'POST';
$relativeUrl = '/banking/corporates/transfers/v2/domestic';
$apiSecret = 'client-secret';
$accessToken = $oauth->content->access_token;
$requestBody = [
    'foo' => 'bar',
    'baz' => 'qux',
];

$signature = $auth->sign($httpMenthod, $relativeUrl, $apiSecret, $accessToken, $requestBody);