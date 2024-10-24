<?php

namespace Tests\Feature\Services\PickingArea\Refill;

use App\Models\StorageBox\StorageBox;
use App\Services\PickingArea\Refill\DeleteRefillRecordService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\GeneralTestCase;

class DeleteRefillRecordServiceTest extends GeneralTestCase
{
    public function test_it_can_delete_picking_area_refill_record()
    {
        $refill = $this->createMockData('pending');

        app(DeleteRefillRecordService::class)
            ->setRecordId($refill->id)
            ->exec();

        $this->assertDatabaseMissing(
            'picking_area_refill',
            [
                'id' => $refill->id
            ]
        );
    }

    public function test_it_can_throw_validation_exception_while_delete_picking_area_refill_record()
    {
        $refill = $this->createMockData('processing');

        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(DeleteRefillRecordService::class)
            ->setRecordId($refill->id)
            ->exec();
    }

    private function createMockData(string $status)
    {
        $factory = $this->createFactory($this->faker->company);
        $pickingArea = $this->createPickingArea($factory);
        $semiFinishedProductArea = $this->createSemiFinishedProductArea($factory);

        $material = $this->createMaterial('107MB18501C', '');
        $storageBoxA = $this->createStorageBox($pickingArea, [
            'prefix' => 'A',
            'barcode' => 'A00001',
            'warehouse_id' => $pickingArea->id,
            'location' => sprintf('%s%s', $pickingArea->code, $this->faker->numerify('A-##-##-#')),
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
            'location' => sprintf('%s%s', $semiFinishedProductArea->code, $this->faker->numerify('B-##-##')),
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(3),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);

        $storageBoxItemA = $this->createStorageBoxItem($storageBoxA, $material, 5);
        $storageBoxItemB = $this->createStorageBoxItem($storageBoxB, $material, $storageBoxB->initial_quantity);

        return $this->createRefillRecord($storageBoxA, $storageBoxItemA, $storageBoxB, $storageBoxItemB, $this->faker->randomDigitNotZero(), 'replace', $status);
    }
}
