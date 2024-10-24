<?php

namespace Libs\Slack;

use GuzzleHttp\Client;

abstract class Service
{
    /** @var Client $client */
    protected $client;
    protected $endPoint = 'https://slack.com';
    protected $token;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->token = config('app.slack.bot.token');
    }

    protected function sendRequest(string $url, $payload)
    {
        $payload['headers'] = [
            'Authorization' => 'Bearer ' . $this->token
        ];
        $response = $this->client->post($this->endPoint . $url, $payload);

        return [
            'status' => $response->getReasonPhrase(),
            'statusCode' => $response->getStatusCode()
        ];
    }
}
