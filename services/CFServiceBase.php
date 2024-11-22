<?php
/**
 * CFBaseService
 *
 * @author: tuanha
 */
namespace CFBuddy;

use GuzzleHttp\Client;

class CFServiceBase
{
    /**
     * @var GuzzleHttp\Client $client
     */
    protected $client;

    /**
     * Create instance
     */
    public function __construct()
    {
        $this->client = new Client([
            //'verify' => false, // This is not recommended settings => used for temporarily fixing cURL error 60: SSL cert problem: cert has expired
            'base_uri' => $_ENV['CF_BASE_URI'],
            'headers' => [
                'X-Auth-Email' => $_ENV['CF_API_EMAIL'],
                'X-Auth-Key'   => $_ENV['CF_API_KEY'],
                'Authorization' => 'Bearer ' . $_ENV['CF_API_TOKEN'],
                'Content-Type' => 'application/json'
            ]
        ]);
    }
}
