<?php

namespace Tests\Feature\Services\PickingArea\Refill;

use App\Models\StorageBox\StorageBox;
use App\Services\PickingArea\Refill\AddRefillRecordService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\GeneralTestCase;

class AddRefillRecordServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_error_while_add_picking_area_refill_record()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(AddRefillRecordService::class)
            ->setPayload([
                'location' => ''
            ])
            ->exec();
    }

    public function test_it_can_add_picking_area_refill_record()
    {
        $factory = $this->createFactory($this->faker->company);
        $pickingArea = $this->createPickingArea($factory);
        $semiFinishedProductArea = $this->createSemiFinishedProductArea($factory);

        $locationA = $this->createLocation($pickingArea, 'A');
        $locationB = $this->createLocation($semiFinishedProductArea, 'B');

        $material = $this->createMaterial('107MB18501C', '');

        $storageBoxA = $this->createStorageBox($pickingArea, [
            'prefix' => 'A',
            'barcode' => 'A00001',
            'warehouse_id' => $pickingArea->id,
            'location' => $locationA->barcode,
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
            'location' => $locationB->barcode,
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(3),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
        ]);

        $this->createStorageBoxItem($storageBoxA, $material, 5);
        $this->createStorageBoxItem($storageBoxB, $material, 100);

        $jsonString = sprintf(<<<'EOL'
        {
            "location": "%s"
        }
        EOL, $locationA->barcode);
        $payload = json_decode($jsonString, true);

        app(AddRefillRecordService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseHas(
            'picking_area_refill',
            [
                'material_id' => $material->id,
                'material_sku' => $material->sku,
                'warehouse_id' => $pickingArea->id,
                'location' => $locationA->barcode,
                'storage_boxes' => json_encode([$storageBoxA->barcode]),
                'quantity' => 0,
                'fill_type' => 'replace',
                'status' => 'pending'
            ]
        );
    }
}
