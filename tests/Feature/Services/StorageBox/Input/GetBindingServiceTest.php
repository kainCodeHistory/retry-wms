<?php

namespace Tests\Feature\Services\StorageBox\Input;

use App\Models\StorageBox\StorageBox;
use App\Services\StorageBox\Input\GetBindingService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

use Tests\GeneralTestCase;

class GetBindingServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_exception()
    {
        $this->withExceptionHandling();
        $this->expectException(ValidationException::class);

        app(GetBindingService::class)
            ->setStorageBox('F00001')
            ->exec();
    }

    public function test_it_can_get_bz_01_01_as_recommend_location()
    {
        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $material = $this->createMaterial('107MB18501C', '');

        $storageBox = $this->createStorageBox($warehouse, [
            'prefix' => 'F',
            'barcode' => 'F00001',
            'warehouse_id' => null,
            'location' => '',
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(3),
            'status' => StorageBox::BOUND,
            'is_empty' => false,
            'bound_material_at' => Carbon::now()
        ]);

        $aaLocation = $this->createLocation($warehouse, 'A');
        $abLocation = $this->createLocation($warehouse, 'B');
        $this->createStorageItem($material, $aaLocation);
        $this->createStorageItem($material, $abLocation);

        $quantity = $this->faker->randomNumber(2);
        $this->createStorageBoxItem($storageBox, $material, $quantity);

        $data = app(GetBindingService::class)
            ->setStorageBox($storageBox->barcode)
            ->exec();

        $this->assertEquals(
            $data,
            [
                'location' => 'BZ-01-01',
                'pickLocation' => [$aaLocation->barcode, $abLocation->barcode]
            ]
        );
    }

    public function test_it_can_get_recommend_location()
    {
        $factory = $this->createFactory($this->faker->company);
        $pickingArea = $this->createPickingArea($factory);
        $semiFinishedProductArea = $this->createSemiFinishedProductArea($factory);

        $material = $this->createMaterial('107MB18501C', '');

        $aaLocation = $this->createLocation($pickingArea, 'A');
        $abLocation = $this->createLocation($pickingArea, 'B');

        $storageBox01 = $this->createStorageBox($pickingArea, [
            'prefix' => 'F',
            'barcode' => 'F00001',
            'warehouse_id' => null,
            'location' => '',
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(3),
            'status' => StorageBox::BOUND,
            'is_empty' => false,
            'bound_material_at' => Carbon::now()
        ]);

        $storageBox02 = $this->createStorageBox($semiFinishedProductArea, [
            'prefix' => 'F',
            'barcode' => 'F00002',
            'warehouse_id' => $semiFinishedProductArea->id,
            'location' => $this->faker->numerify('BA-##-##'),
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(3),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);

        $this->createStorageItem($material, $aaLocation);
        $this->createStorageItem($material, $abLocation);

        $quantity = $this->faker->randomNumber(2);
        $this->createStorageBoxItem($storageBox01, $material, $quantity);
        $this->createStorageBoxItem($storageBox02, $material, $quantity);

        $data = app(GetBindingService::class)
            ->setStorageBox($storageBox01->barcode)
            ->exec();

        $this->assertEquals(
            $data,
            [
                'location' => $storageBox02->location,
                'pickLocation' => [$aaLocation->barcode, $abLocation->barcode]
            ]
        );
    }
}
