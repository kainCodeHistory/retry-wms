<?php

namespace Libs\NXCloud;

use GuzzleHttp\Client;

abstract class Service
{
    /** @var Client $clienct */
    protected $client;

    /** @var String $endpoint */
    protected $endpoint;

    /** @var String $token */
    protected $token;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->endpoint = config('app.nx.cloud.host');
    }

    protected function sendRequest($method, $url, $payload)
    {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('app.nx.cloud.token'),
                'Content-Type' => 'application/json',
                'Origin' => ''
            ]
        ];
        if ($method === 'GET') {
            $options['query'] = $payload;
        } else {
            $options['json'] = $payload;
        }

        $response = $this->client->request($method, $url, $options);

        return json_decode($response->getBody()->getContents(), true);
    }
}
