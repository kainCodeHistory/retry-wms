<?php

namespace Tests\Feature\Services\PickingArea\Refill;

use App\Models\StorageBox\StorageBox;
use App\Services\PickingArea\Refill\GetPendingRefillRecordService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\GeneralTestCase;

class GetPendingRefillRecordServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_exception_with_no_pending_replace_record()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(GetPendingRefillRecordService::class)
            ->exec();
    }

    public function test_it_can_get_pending_replace_record()
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

        $data = app(GetPendingRefillRecordService::class)
            ->exec();

        $this->assertEquals(
            [
                'id' => $refill->id,
                'sku' => $material->sku,
                'name' => $material->display_name,
                'locations' => [
                    [
                        'warehouse' => $semiFinishedProductArea->name,
                        'location' => $storageBoxB->location,
                        'storageBox' => $storageBoxB->barcode,
                        'quantity' => $storageBoxB->initial_quantity,
                        'batchNo' => $storageBoxItemB->batch_no,
                        'boundAt' => date("Y-m-d")
                    ]
                ]
            ],
            $data
        );
    }
}
