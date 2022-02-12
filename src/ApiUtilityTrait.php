<?php

namespace Esyede\BCA;

trait ApiUtilityTrait
{
    /**
     * Ambil base URL api.
     *
     * @return string
     */
    public function apiBaseUrl()
    {
        return 'https://sandbox.bca.co.id:443';
    }

    /**
     * Buat tanggal sekarang (format: ISO 8601).
     *
     * @param string $timezone
     *
     * @return string
     */
    public function dateIso8601($timezone = 'Asia/Jakarta')
    {
        $date = new \DateTime('now', new \DateTimeZone($timezone));

        $ymd = $date->format('Y-m-d\TH:i:s');
        $p = $date->format('P');

        $iso8601 = sprintf('%s.%s%s', $ymd, substr(microtime(), 2, 3), $p);

        return $iso8601;
    }

    /**
     * Buat tanggal transaksi (format: yyyy-MM-dd)
     *
     * @param string $timezone
     *
     * @return string
     */
    public function dateTrx($timezone = 'Asia/Jakarta')
    {
        $date = new \DateTime('now', new \DateTimeZone($timezone));
        return $date->format('y-m-d');
    }
}