<?php

namespace Libs\NXLocal\tests;

use Libs\NXCloud\MaterialStockService;
use Tests\LibsTestCase;
use GuzzleHttp\Client;

class MaterialStockServiceTest extends LibsTestCase
{
    public function test_it_can_update_material_stock()
    {
        $jsonString = file_get_contents(base_path('tests/Stub/material-stock.json'));
        $payload = json_decode($jsonString, true);

        $domain = config('app.nx.cloud.host');
        $mock = $this->mock(Client::class);

        $result = [
            'result'=> 'ok'
        ];

        $mock->shouldReceive('request')
            ->with('POST', $domain . "/material/stock", [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('app.nx.cloud.token'),
                    'Content-Type' => 'application/json',
                    'Origin' => ''
                ],
                'json' => $payload

            ])
            ->once()
            ->andReturn($this->getMockResponse([
                'result' => 'ok'
            ]));

        $data = app(MaterialStockService::class)
            ->updateMaterialStock($payload);

        $this->assertEquals($result, $data);
    }
}
