<?php

namespace Tests\Feature\Services\Material;

use App\Models\StorageBox\StorageBox;
use App\Services\Material\CheckLocationService;;
use Carbon\Carbon;

use Tests\GeneralTestCase;

class CheckLocationServiceTest extends GeneralTestCase
{
    public function test_it_can_check_location()
    {
        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $checkMaterial = $this->createMaterial('107MB18501C', '');
        $this->createMaterial($this->faker->bothify('###???????'), $checkMaterial->sku);

        $location = $this->createLocation($warehouse, 'A');
        $storageBox = $this->createStorageBox($warehouse, [
            'prefix' => 'A',
            'barcode' => 'A00001',
            'warehouse_id' => $warehouse->id,
            'location' => $location->barcode,
            'sku' => $checkMaterial->sku,
            'initial_quantity' => $this->faker->randomNumber(3),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);
        $this->createStorageBoxItem($storageBox, $checkMaterial, $storageBox->initial_quantity);

        $data = app(CheckLocationService::class)
            ->setLocation($location->barcode)
            ->setStorageBox($storageBox->barcode)
            ->exec();
        $this->assertEquals([
            'status' => true,
            'sku' => $checkMaterial->sku,
            'location' => $location->barcode,
            'storageBoxes' => [$storageBox->barcode]
        ], $data);

        $data = app(CheckLocationService::class)
            ->setLocation($location->barcode)
            ->setStorageBox($this->faker->bothify('#?????'))
            ->exec();
        $this->assertEquals([
            'status' => false,
            'sku' => '',
            'location' => $location->barcode,
            'storageBoxes' => []
        ], $data);
    }
}
