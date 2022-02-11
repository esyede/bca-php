# bca-php


### Install

```bash
composer require esyede/php-bca
```

### Setting dan koneksi

Sebelum masuk ke tahap ```LOGIN``` pastikan seluruh kebutuhan seperti ```CORP_ID, CLIENT_KEY, CLIENT_SECRET, APIKEY, SECRETKEY``` telah diketahui.

```php
$options = [
    'scheme'      => 'https',
    'port'        => 443,
    'host'        => 'sandbox.bca.co.id',
    'timezone'    => 'Asia/Jakarta',
    'timeout'     => 30,
    'debug'       => true,
    'development' => true
];

// Setting default timezone Anda
Esyede\BCA\Request::setTimeZone('Asia/Jakarta');

// ATAU

// Esyede\BCA\Request::setTimeZone('Asia/Singapore');

$corpId       = 'BCAAPI2016';
$clientKey    = 'NILAI-CLIENT-KEY-ANDA';
$clientSecret = 'NILAI-CLIENT-SECRET-ANDA';
$apikey       = 'NILAI-APIKEY-ANDA';
$secret       = 'SECRETKEY-ANDA';

$bca = new Esyede\BCA\Request($corpId, $clientKey, $clientSecret, $apikey, $secret);

// ATAU

$bca = new Esyede\BCA\Request($corpId, $clientKey, $clientSecret, $apikey, $secret, $options);
```

Menggunakan custom **Curl Options**

```php
$options = [
    'curl_options'  => [
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSLVERSION     => 6,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 60,
    ],
    'scheme'        => 'https',
    'port'          => 443,
    'host'          => 'sandbox.bca.co.id',
    'timezone'      => 'Asia/Jakarta',
    'timeout'       => 30,
    'debug'         => true,
    'development'   => true
];

// Setting default timezone Anda
Esyede\BCA\Request::setTimeZone('Asia/Jakarta');

// ATAU

// Esyede\BCA\Request::setTimeZone('Asia/Singapore');

$corpId       = 'BCAAPI2016';
$clientKey    = 'NILAI-CLIENT-KEY-ANDA';
$clientSecret = 'NILAI-CLIENT-SECRET-ANDA';
$apikey       = 'NILAI-APIKEY-ANDA';
$secret       = 'SECRETKEY-ANDA';

$bca = new Esyede\BCA\Request($corpId, $clientKey, $clientSecret, $apikey, $secret, $options);
```

### LOGIN

```php
$corpId       = 'CORP_ID-ANDA';
$clientKey    = 'NILAI-CLIENT-KEY-ANDA';
$clientSecret = 'NILAI-CLIENT-SECRET-ANDA';
$apikey       = 'NILAI-APIKEY-ANDA';
$secret       = 'SECRETKEY-ANDA';

$bca = new Esyede\BCA\Request($corpId, $clientKey, $clientSecret, $apikey, $secret);

// Request Login dan dapatkan nilai OAUTH
$response = $bca->authenticate();

// Cek hasil response berhasil atau tidak
echo json_encode($response);
```

Setelah Login berhasil pastikan anda menyimpan nilai ```TOKEN``` di tempat yang aman, karena nilai ```TOKEN``` tersebut agar digunakan untuk tugas tugas berikutnya.

### BALANCE INFORMATION

Pastikan anda mendapatkan nilai ```TOKEN``` dan ```TOKEN``` tersebut masih berlaku (Tidak Expired).

```php
// Ini adalah nilai token yang dihasilkan saat login
$token = 'MvXPqa5bQs5U09Bbn8uejBE79BjI3NNCwXrtMnjdu52heeZmw9oXgB';

//Nomor akun yang akan di ambil informasi saldonya, menggunakan ARRAY
$arrayAccNumber = ['0201245680', '0063001004', '1111111111'];

$response = $bca->getBalanceInfo($token, $arrayAccNumber);

// Cek hasil response berhasil atau tidak
echo json_encode($response);
```

### FUND TRANSFER (UPDATED)

Pastikan anda mendapatkan nilai ```TOKEN``` dan ```TOKEN``` tersebut masih berlaku (Tidak Expired).

```php
// Ini adalah nilai token yang dihasilkan saat login
$token = 'MvXPqa5bQs5U09Bbn8uejBE79BjI3NNCwXrtMnjdu52heeZmw9oXgB';
$amount = '50000.00';

// Nilai akun bank anda
$nomorakun = '0201245680';

// Nilai akun bank yang akan ditransfer
$nomordestinasi = '0201245681';

// Nomor PO, silahkan sesuaikan
$nomorPO = '12345/PO/2017';

// Nomor Transaksi anda, Silahkan generate sesuai kebutuhan anda
$nomorTransaksiID = '00000001';

$remark1 = 'Transfer Test';
$remark2 = 'Online Transfer Test';

// value hanya support idr dan usd
$mataUang = 'idr';

$response = $bca->fundTransfers(
    $token,
    $amount,
    $nomorakun,
    $nomordestinasi,
    $nomorPO,
    $remark1,
    $remark2,
    $nomorTransaksiID,
    $mataUang
);

// Cek hasil response berhasil atau tidak
echo json_encode($response);
```

Untuk data ```remark1```, ```remark2```, ```nomorPO``` akan di replace menjadi ```lowercase``` dan dihapus ```whitespace```

### ACCOUNT STATEMENT

Pastikan anda mendapatkan nilai ```TOKEN``` dan ```TOKEN``` tersebut masih berlaku (Tidak Expired).

```php
// Ini adalah nilai token yang dihasilkan saat login
$token = 'MvXPqa5bQs5U09Bbn8uejBE79BjI3NNCwXrtMnjdu52heeZmw9oXgB';

// Nilai akun bank anda
$nomorakun = '0201245680';

// Tanggal start transaksi anda
$startdate = '2016-08-29';

// Tanggal akhir transaksi anda
$enddate = '2016-09-01';

$response = $bca->getAccountStatement($token, $nomorakun, $startdate, $enddate);

// Cek hasil response berhasil atau tidak
echo json_encode($response);
```

### FOREIGN EXCHANGE RATE

```php
// Tipe rate :  bn, e-rate, tt, tc
$rateType = 'e-rate';
$mataUang = 'usd';
$response = $bca->getForexRate($token, $rateType, $mataUang);

// Cek hasil response berhasil atau tidak
echo json_encode($response);
```

### NEAREST ATM LOCATOR

```php
$latitude = '-6.1900718';
$longitude = '106.797190';
$totalAtmShow = '10';
$radius = '20';

$response = $bca->getAtmLocation($token, $latitude, $longitude, $totalAtmShow, $radius);

// Cek hasil response berhasil atau tidak
echo json_encode($response);
```

### DEPOSIT RATE

Pastikan anda mendapatkan nilai ```TOKEN``` dan ```TOKEN``` tersebut masih berlaku (Tidak Expired).

```php
// Ini adalah nilai token yang dihasilkan saat login
$token    = 'MvXPqa5bQs5U09Bbn8uejBE79BjI3NNCwXrtMnjdu52heeZmw9oXgB';
$response = $bca->getDepositRate($token);

// Cek hasil response berhasil atau tidak
echo json_encode($response);
```

### GENERATE SIGNATURE

Saat berguna untuk keperluan testing.

```php

$secret = 'NILAI-SECRET-ANDA';

// Ini adalah nilai token yang dihasilkan saat login
$token = 'MvXPqa5bQs5U09Bbn8uejBE79BjI3NNCwXrtMnjdu52heeZmw9oXgB';

$uriSign = 'GET:/general/info-bca/atm';

// Format timestamp harus dalam ISO8601 format (yyyy-MM-ddTHH:mm:ss.SSSTZD)
$isoTime = '2016-02-03T10:00:00.000+07:00';

$bodies = [];
// Nilai body anda disini
$bodies['a'] = 'BLAAA-BLLLAA';
$bodies['b'] = 'BLEHH-BLLLAA';

// Ketentuan BCA array harus disort terlebih dahulu
ksort($bodies);

$authSignature = Esyede\BCA\Request::makeSignature($uriSign, $token, $secret, $isoTime, $bodies);

echo $authSignature;
```