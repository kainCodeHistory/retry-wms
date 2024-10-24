<?php

namespace Tests\Feature\Services\PickingArea\Refill;

use App\Models\StorageBox\StorageBox;
use App\Services\PickingArea\Refill\UpdateRefillRecordService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

use Tests\GeneralTestCase;

class UpdateRefillRecordServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_error_while_update_picking_area_refill_record()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(UpdateRefillRecordService::class)
            ->setPayload([
                'type' => 'replace'
            ])
            ->exec();
    }

    public function test_it_can_update_replace_record()
    {
        $factory = $this->createFactory($this->faker->company);
        $pickingArea = $this->createPickingArea($factory);
        $semiFinishedProductArea = $this->createSemiFinishedProductArea($factory);

        $material = $this->createMaterial('107MB18501C', '');
        $storageBoxA = $this->createStorageBox($pickingArea, [
            'prefix' => 'A',
            'barcode' => 'A00001',
            'warehouse_id' => $pickingArea->id,
            'location' => sprintf('%sA-07-03-1', $pickingArea->code),
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(3),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);

        $storageBoxB = $this->createStorageBox($semiFinishedProductArea, [
            'prefix' => 'A',
            'barcode' => 'A00002',
            'warehouse_id' => $semiFinishedProductArea->id,
            'location' => sprintf('%sB-09-02', $semiFinishedProductArea->code),
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(3),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
        ]);

        $storageBoxItemA = $this->createStorageBoxItem($storageBoxA, $material, 5);
        $storageBoxItemB = $this->createStorageBoxItem($storageBoxB, $material, $storageBoxB->initial_quantity);

        $refill = $this->createRefillRecord($storageBoxA, $storageBoxItemA, $storageBoxB, $storageBoxItemB, $storageBoxItemB->quantity, 'replace');

        $jsonString = sprintf(<<<'EOL'
        {
            "id": %s,
            "outputQuantity": %s,
            "storageBox": "%s"
        }
        EOL, $refill->id, $storageBoxItemB->quantity, $storageBoxB->barcode);
        $payload = json_decode($jsonString, true);

        app(UpdateRefillRecordService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseHas(
            'picking_area_refill',
            [
                'id' => $refill->id,
                'repl_location' => $storageBoxB->location,
                'repl_storage_box' => $storageBoxB->barcode,
                'repl_quantity' => $storageBoxItemB->quantity,
                'status' => 'processing'
            ]
        );
    }
}
