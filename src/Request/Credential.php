<?php

namespace Esyede\BCA\Request;

use Esyede\BCA\Exceptions\BCAException;

class Credential
{
    private $environment;
    private $baseEndpont;
    private $corporateId;
    private $apiKey;
    private $apiSecret;
    private $clientId;
    private $clientSecret;

    /**
     * Constructor.
     *
     * @param string $environment   Environment type ('development' or 'production')
     * @param string $corporateId   Corporate ID (supplied by BCA)
     * @param string $apiKey        Api key (supplied by BCA)
     * @param string $apiSecret     Secret key (supplied by BCA)
     * @param string $clientId      Client ID (supplied by BCA)
     * @param string $clientSecret  Client secret (supplied by BCA)
     */
    public function __construct($environment, $corporateId, $apiKey, $apiSecret, $clientId, $clientSecret)
    {
        $environment = strtolower(strval($environment));

        if ($environment !== 'development' && $environment !== 'production') {
            throw new BCAException('Environment type should be either DEVELOPMENT or PRODUCTION.');
        }

        $this->environment = $environment;
        $this->baseEndpont = ($environment === 'production')
            ? 'https://api.klikbca.com:9443'
            : 'https://devapi.klikbca.com:9443';

        $this->corporateId = $corporateId;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /*
    |--------------------------------------------------------------------------
    | Getters
    |--------------------------------------------------------------------------
    */

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function getBaseEndpoint()
    {
        return $this->baseEndpont;
    }

    public function getCorporateId()
    {
        return $this->corporateId;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function getApiSecret()
    {
        return $this->apiSecret;
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    public function toArray()
    {
        return [
            'environment' => $this->getEnvironment(),
            'base_endpoint' => $this->getBaseEndpoint(),
            'corporate_id' => $this->getCorporateId(),
            'api_key' => $this->getApiKey(),
            'api_secret' => $this->getApiSecret(),
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
