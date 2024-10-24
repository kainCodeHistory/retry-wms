<?php

namespace Tests\Feature\Services\B2CStock;

use Illuminate\Support\Facades\Auth;
use Tests\GeneralTestCase;

class GetStockServiceTest extends GeneralTestCase
{
    public function test_it_can_return_status_code_200()
    {
        $user = $this->createUser([
            'name' => 'user',
            'email' => 'user@evolutivelabs.com'
        ]);

        Auth::loginUsingId($user->id);

        $ma88 = $this->createMaterial('MA88', 'MA88', [
            'display_name' => 'Type C-TypeC TPE充電線 黑色 2M',
            'ean' => $this->faker->randomNumber(6)
        ]);

        $stock = \App\Models\B2CStock::create([
            'sku' => $ma88->sku,
            'total_quantity' => $this->faker->randomNumber(2)
        ]);

        $response = $this->get(sprintf("/api/stock/%s", $ma88->ean));
        $this->assertEquals(200, $response->getStatusCode());

        $payload = json_decode($response->getContent(), true);
        $this->assertEquals($ma88->sku, $payload['sku']);
        $this->assertEquals($ma88->display_name, $payload['product_title']);
        $this->assertEquals($stock->total_quantity, $payload['current_quantity']);
    }

    public function test_it_can_return_status_code_404()
    {
        $user = $this->createUser([
            'name' => 'user',
            'email' => 'user@evolutivelabs.com'
        ]);

        Auth::loginUsingId($user->id);

        $response = $this->get(sprintf("/api/stock/%s", 'MA88'));
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals("無此 EAN/SKU (MA88)", $response->getContent());
    }
}
