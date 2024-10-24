<?php

namespace Tests\Feature\Services\B2CStock;

use App\Services\B2CStock\AdjustStockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\GeneralTestCase;

class AdjustStockServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_errors()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(AdjustStockService::class)
            ->setPayload([])
            ->exec();
    }

    public function test_it_can_throw_material_not_exists_error()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        $jsonString = sprintf(<<<JSON
        {
            "ean_sku": "MA88",
            "current_quantity": %s,
            "adjusted_quantity": %s
        }
        JSON, $this->faker->randomNumber(2), $this->faker->randomNumber(2));

        $payload = json_decode($jsonString, true);
        app(AdjustStockService::class)
            ->setPayload($payload)
            ->exec();
    }

    public function test_it_can_adjust_stock()
    {
        $currentQuantity = $this->faker->randomNumber(2);
        $adjustedQuantity = $this->faker->randomNumber(2);
        $note = $this->faker->word();

        $ma88 = $this->createMaterial('MA88', 'MA88', [
            'ean' => $this->faker->randomNumber(6)
        ]);

        \App\Models\B2CStock::create([
            'sku' => $ma88->sku,
            'current_quantity' => $currentQuantity
        ]);

        \App\Models\B2CStockLog::create([
            'working_day' => Carbon::now()->format('Y-m-d'),
            'sku' => $ma88->sku,
            'quantity' => $currentQuantity,
            'balance' => $currentQuantity,
            'event' => 'initial',
            'event_key' => '',
            'note' => '',
        ]);

        $user = $this->createUser([
            'name' => 'user',
            'email' => 'user@evolutivelabs.com'
        ]);

        Auth::loginUsingId($user->id);

        $response = $this->put(
            sprintf("/api/stock/%s", $ma88->ean),
            [
                'current_quantity' => $currentQuantity,
                'adjusted_quantity' => $adjustedQuantity,
                'note' => $note
            ]
        );
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertDatabaseHas(
            'b2c_stock',
            [
                'sku' => $ma88->sku,
                'total_quantity' => $adjustedQuantity
            ]
        );

        $this->assertDatabaseHas(
            'b2c_stock_logs',
            [
                'working_day' => Carbon::now()->format('Y-m-d'),
                'sku' => $ma88->sku,
                'quantity' => $adjustedQuantity - $currentQuantity,
                'balance' => $adjustedQuantity,
                'event' => 'adjust',
                'event_key' => '',
                'note' => $note,
                'user_name' => $user->name
            ]
        );
    }
}
