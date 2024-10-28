<?php

namespace Tests\Feature\Services\PickingArea\Refill;

use App\Models\StorageBox\StorageBox;
use App\Services\PickingArea\Refill\GetACLocationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use Tests\GeneralTestCase;

class GetACLocationServiceTest extends GeneralTestCase
{
    public function test_it_can_get_picking_item()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@tests.com',
            'password' => Hash::make('123456')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $pickingArea = $this->createPickingArea($factory);

        $material = $this->createMaterial('107MB18501C', '');
        $storageBox = $this->createStorageBox($pickingArea, [
            'prefix' => 'D',
            'barcode' => 'D00001',
            'warehouse_id' => $pickingArea->id,
            'location' => sprintf('%sC-07-03-1', $pickingArea->code),
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(3),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);

        $quantity = $this->faker->randomNumber(1);
        $storageBoxItem = $this->createStorageBoxItem($storageBox, $material, $storageBox->initial_quantity);

        $data = app(GetACLocationService::class)
            ->setStorageBox($storageBox->barcode)
            ->exec();

        $this->assertEquals(
            [
                'storageBox' => $storageBox->barcode,
                'location' => $storageBox->location,
                'sku' => $storageBoxItem->material_sku,
                'materialName' => $storageBoxItem->material_name,
                'batchNo' => $storageBoxItem->batch_no,
                'quantity' => $storageBoxItem->quantity
            ],
            $data
        );
    }

    public function test_it_can_get_location_with_default_storage_box()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@tests.com',
            'password' => Hash::make('123456')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $pickingArea = $this->createPickingArea($factory);

        $location = $this->createLocation($pickingArea, 'C', $this->faker->randomNumber(2), 'D00001');

        $storageBox = $this->creaetEmptyStorageBox($pickingArea, 'D00001');

        $data = app(GetACLocationService::class)
            ->setStorageBox($storageBox->barcode)
            ->exec();

        $this->assertEquals(
            [
                'storageBox' => $storageBox->barcode,
                'location' => $location->barcode,
                'sku' => '',
                'materialName' => '',
                'batchNo' => '',
                'quantity' => 0
            ],
            $data
        );
    }

    public function test_it_can_throw_validation_exception()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@tests.com',
            'password' => Hash::make('123456')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $pickingArea = $this->createPickingArea($factory);

        $this->createLocation($pickingArea, 'C');
        $storageBox = $this->creaetEmptyStorageBox($pickingArea, 'D00001');

        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(GetACLocationService::class)
            ->setStorageBox($storageBox->barcode)
            ->exec();
    }
}
