<?php

namespace Libs\Slack;

use Libs\Slack\Service;

class SlackService extends Service
{
    public function sendFile(string $channel, string $fileName, string $filePath, string $comment)
    {
        $payload = [
            'multipart' => [
                [
                    'name' => 'filename',
                    'contents' => $fileName
                ],
                [
                    'name' => 'file',
                    'contents' => fopen($filePath, 'r')
                ],
                [
                    'name' => 'channels',
                    'contents' => $channel
                ],
                [
                    'name' => 'initial_comment',
                    'contents' => $comment
                ]
            ]
        ];

        return $this->sendRequest('/api/files.upload', $payload);
    }

    public function sendMessage(string $channel, string $message)
    {
        $payload = [
            'form_params' => [
                'channel' => $channel,
                'text' => $message
            ]
        ];

        return $this->sendRequest('/api/chat.postMessage', $payload);
    }

    public function sendMessageViaWebhookURL(string $webhookURL, array $blocks, $text = '')
    {
        $response = $this->client->post(
            $webhookURL,
            [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'text' => $text,
                    'blocks' => $blocks
                ]
            ]
        );

        return [
            'status' => $response->getReasonPhrase(),
            'statusCode' => $response->getStatusCode()
        ];
    }
}
