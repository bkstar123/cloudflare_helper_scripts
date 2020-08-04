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
            'base_uri' => $_ENV['CF_BASE_URI'],
            'headers' => [
                'X-Auth-Email' => $_ENV['CF_API_EMAIL'],
                'X-Auth-Key'   => $_ENV['CF_API_KEY'],
                'Content-Type' => 'application/json'
            ]
        ]);
    }
}
