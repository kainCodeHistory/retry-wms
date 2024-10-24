<?php

namespace Tests\Feature\Services\StorageBox\Output;

use App\Models\StorageBox\StorageBox;
use App\Models\Transaction;
use App\Services\StorageBox\Output\ResetLocationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use Tests\GeneralTestCase;

class ResetLocationServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_exception_while_reset_location()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(ResetLocationService::class)
            ->setPayload([])
            ->exec();
    }

    public function test_it_can_reset_location_in_zone_a()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $location = $warehouse->code . 'A-07-04';

        $material = $this->createMaterial('107MB18501C', '');
        $quantity = $this->faker->randomNumber(3);

        $storageBox = $this->createStorageBox($warehouse, [
            'prefix' => 'F',
            'barcode' => 'F00001',
            'warehouse_id' => $warehouse->id,
            'location' => $location,
            'sku' => $material->sku,
            'initial_quantity' => $quantity,
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

        app(ResetLocationService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseHas(
            'storage_boxes',
            [
                'id' => $storageBox->id,
                'warehouse_id' => null,
                'location' => '',
                'status' => StorageBox::STORAGE
            ]
        );

        $this->assertDatabaseHas(
            'storage_box_items',
            [
                'id' => $storageBoxItem->id,
                'quantity' => $storageBoxItem->quantity
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
                'event' => Transaction::RESET_LOCATION,
                'event_key' => '',
                'user' => $user->id
            ]
        );
    }

    public function test_it_can_reset_location_in_zone_b()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $location = $warehouse->code . 'A-07-04';

        $material = $this->createMaterial('107MB18501C', '');
        $quantity = $this->faker->randomNumber(3);

        $storageBox = $this->createStorageBox($warehouse, [
            'prefix' => 'F',
            'barcode' => 'F00001',
            'warehouse_id' => $warehouse->id,
            'location' => $location,
            'sku' => $material->sku,
            'initial_quantity' => $quantity,
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

        app(ResetLocationService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseHas(
            'storage_boxes',
            [
                'id' => $storageBox->id,
                'warehouse_id' => null,
                'location' => '',
                'status' => StorageBox::STORAGE
            ]
        );

        $this->assertDatabaseHas(
            'storage_box_items',
            [
                'id' => $storageBoxItem->id,
                'quantity' => $storageBoxItem->quantity
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
                'event' => Transaction::RESET_LOCATION,
                'event_key' => '',
                'user' => $user->id
            ]
        );
    }
}
