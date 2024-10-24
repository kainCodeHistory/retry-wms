<?php

namespace Tests\Feature\Services\PickingArea\Refill;

use App\Services\PickingArea\RolloverRecordService;

use Tests\GeneralTestCase;

class RolloverRecordServiceTest extends GeneralTestCase
{

    public function test_it_can_insert_rollover_record()
    {
        $factory = $this->createFactory($this->faker->company);
        $this->createPickingArea($factory);
        $this->createSemiFinishedProductArea($factory);

        $material = $this->createMaterial('107MB18501C', '');

        $quantity = $this->faker->randomDigitNot(0);

        $jsonString = sprintf(<<<EOL
        {
            "quantity": %s,
            "sku": "%s",
            "note": "%s"
        }
        EOL,  $quantity, $material->sku,"123");
        $payload = json_decode($jsonString, true);
        app(RolloverRecordService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseHas(
            'rollover',
            [
                'sku' => $material->sku,
                'quantity' => $quantity,
            ]
        );
    }
}
