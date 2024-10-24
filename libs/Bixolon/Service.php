<?php

namespace Libs\Bixolon;

use GuzzleHttp\Client;

abstract class Service
{
    /** @var Client $client */
    protected $client;

    protected $endpoint;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->endpoint = config('app.nx.bixolon.host');
    }

    protected function sendRequest($method, $url, $form)
    {
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ];
        if ($method === 'GET') {
            $options['query'] = $form;
        } else {
            $options['json'] = $form;
        }
        $response = $this->client->request($method, $url, $options);
        return json_decode($response->getBody()->getContents(), true);
    }
}
