<?php

namespace Libs\Slack\tests;

use Tests\GeneralTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Libs\Slack\SlackService;

class SlackServiceTest extends GeneralTestCase
{
    public function test_it_can_send_file()
    {
        $channel = $this->faker->bothify('???##??###');
        $fileName = "missing_sku.csv";
        $filePath = base_path('libs/Slack/tests/' . $fileName);
        $comment = $this->faker->paragraph();

        $expectData = [
            'status' => "OK",
            'statusCode' => 200
        ];

        $mock = $this->mock(Client::class);
        $mock->shouldReceive('post')
            ->once()
            ->andReturn(new Response(200, [], json_encode($expectData)));
        $data = app(SlackService::class)
            ->sendFile($channel, $fileName, $filePath, $comment);
        $this->assertEquals($data, $expectData);
    }

    public function test_it_can_send_message()
    {
        $channel = $this->faker->bothify('???##??###');
        $message = $this->faker->paragraph();

        $expectData = [
            'status' => 'OK',
            'statusCode' => 200
        ];

        $mock = $this->mock(Client::class);
        $mock->shouldReceive('post')
            ->once()
            ->andReturn(new Response(200, [], json_encode($expectData)));

        $data = app(SlackService::class)
            ->sendMessage($channel, $message);

        $this->assertEquals($data, $expectData);
    }
}
