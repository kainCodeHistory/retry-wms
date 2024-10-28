<?php

namespace Tests\Feature\Services\StorageBox;

use App\Models\StorageBox\StorageBox;
use App\Models\Transaction;
use App\Services\StorageBox\ResetService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use Tests\GeneralTestCase;

class ResetServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_exception_while_reset_storage_box()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(ResetService::class)
            ->setPayload([])
            ->exec();
    }

    public function test_it_can_reset_storage_box_in_zone_aa()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@tests.com',
            'password' => Hash::make('123456')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $location = $warehouse->code . 'A-07-04';
        $material = $this->createMaterial('107MB18501C', '');
        $quantity1 = $this->faker->randomDigitNotZero();
        $quantity2 = $this->faker->randomNumber(3);

        $storageBox1 = $this->createStorageBox($warehouse, [
            'prefix' => 'F',
            'barcode' => 'F00001',
            'warehouse_id' => $warehouse->id,
            'location' => $location,
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(2),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);

        $storageBox2 = $this->createStorageBox($warehouse, [
            'prefix' => 'F',
            'barcode' => 'F00002',
            'warehouse_id' => $warehouse->id,
            'location' => $location,
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(3),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);

        $storageBoxItem1 = $this->createStorageBoxItem($storageBox1, $material, $quantity1);
        $storageBoxItem2 = $this->createStorageBoxItem($storageBox2, $material, $quantity2);

        $jsonString = sprintf(<<<'EOL'
        {
            "storageBox": "%s"
        }
        EOL, $storageBox1->barcode);
        $payload = json_decode($jsonString, true);

        app(ResetService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseMissing(
            'storage_box_items',
            [
                'storage_box' => $storageBox1->barcode
            ]
        );

        $this->assertDatabaseHas(
            'storage_box_items',
            [
                'storage_box' => $storageBox2->barcode,
                'quantity' => $storageBoxItem1->quantity + $storageBoxItem2->quantity
            ]
        );

        $this->assertDatabaseHas(
            'storage_boxes',
            [
                'barcode' => $storageBox1->barcode,
                'warehouse_id' => null,
                'location' => '',
                'is_empty' => 1,
                'status' => '',
                'sku' => '',
                'initial_quantity' => 0,
                'bound_material_at' => null,
                'bound_location_at' => null,
                'bound_picking_area_at' => null
            ]
        );

        $this->assertDatabaseHas(
            'transactions',
            [
                'warehouse_id' => $warehouse->id,
                'location' => $location,
                'storage_box' => $storageBox1->barcode,
                'material_id' => $material->id,
                'batch_no' => $storageBoxItem1->batch_no,
                'quantity' => $storageBoxItem1->quantity,
                'in_out' => 'output',
                'event' => Transaction::STORAGE_BOX_RESET,
                'user' => $user->id
            ]
        );
    }

    public function test_it_can_reset_storage_box_in_zone_ab_ac()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@tests.com',
            'password' => Hash::make('123456')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $location = $warehouse->code . 'B-07-04';
        $material = $this->createMaterial('107MB18501C', '');
        $quantity = $this->faker->randomNumber(2);

        $storageBox = $this->createStorageBox($warehouse, [
            'prefix' => 'F',
            'barcode' => 'F00001',
            'warehouse_id' => $warehouse->id,
            'location' => $location,
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(3),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);

        $storageBoxItem = $this->createStorageBoxItem($storageBox, $material, $quantity);

        $jsonString = sprintf(<<<'EOL'
        {
            "storageBox": "%s"
        }
        EOL, $storageBox->barcode);
        $payload = json_decode($jsonString, true);

        app(ResetService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseMissing(
            'storage_box_items',
            [
                'storage_box' => $storageBox->barcode
            ]
        );

        $this->assertDatabaseHas(
            'storage_boxes',
            [
                'barcode' => $storageBox->barcode,
                'warehouse_id' => null,
                'location' => '',
                'is_empty' => 1,
                'status' => '',
                'sku' => '',
                'initial_quantity' => 0,
                'bound_material_at' => null,
                'bound_location_at' => null,
                'bound_picking_area_at' => null
            ]
        );

        $this->assertDatabaseHas(
            'transactions',
            [
                'warehouse_id' => $warehouse->id,
                'location' => $location,
                'storage_box' => $storageBox->barcode,
                'material_id' => $material->id,
                'batch_no' => $storageBoxItem->batch_no,
                'quantity' => $storageBoxItem->quantity,
                'in_out' => 'output',
                'event' => Transaction::STORAGE_BOX_RESET,
                'user' => $user->id
            ]
        );
    }
}
