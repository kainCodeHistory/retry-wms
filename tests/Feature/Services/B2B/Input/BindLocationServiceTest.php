<?php

namespace Tests\Feature\Services\B2B\Input;

use App\Models\Transaction;
use App\Models\StorageBox\StorageBox;
use App\Services\B2B\StorageBox\Input\BindLocationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Libs\ShippingServer\ShippingServerService;
use Tests\GeneralTestCase;

class BindLocationServiceTest extends GeneralTestCase
{
    public function test_it_can_bind_5F_location_with_storage_box_input_event()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory, 'X');

        $material = $this->createMaterial('107MB18501C', '');
        $quantity = $this->faker->randomNumber(2);

        $storageBox = $this->createStorageBox($warehouse, [
            'prefix' => 'K',
            'barcode' => 'K00001',
            'warehouse_id' => $warehouse->id,
            'location' => '',
            'sku' => $material->sku,
            'initial_quantity' => $quantity,
            'status' => StorageBox::BOUND,
            'is_empty' => false,
            'bound_material_at' => Carbon::now()
        ]);

        $this->createStorageBoxItem($storageBox, $material, $quantity);

        $location = $this->createLocation($warehouse, 'A');
        $jsonString = sprintf(<<<'EOL'
        {
            "location": "%s",
            "storageBox": "%s"
        }
        EOL, $location->barcode, $storageBox->barcode);
        $payload = json_decode($jsonString, true);

        //TODO B2B
        //寫入shipping_server b2b_stock/b2b_picking_area_inventory相關
        // $mock = $this->mock(ShippingServerService::class);
        // $mock->shouldReceive('upsertB2BPickingAreaInventory')
        //     ->withArgs([$material->sku, Transaction::STORAGE_BOX_INPUT, $location->barcode, $location->priority, $quantity])
        //     ->once()
        //     ->andReturn();


        app(BindLocationService::class)
            ->setPayload($payload)
            ->exec();


        $storageBox = \App\Models\StorageBox\StorageBox::where('id', '=', $storageBox->id)
            ->first();
        $this->assertEquals($location->barcode, $storageBox->location);
        $this->assertEquals(StorageBox::STORAGE, $storageBox->status);
        $this->assertNotEmpty($storageBox->bound_location_at);

        $this->assertDatabaseHas(
            'transactions',
            [
                'warehouse_id' => $warehouse->id,
                'location' => $location->barcode,
                'storage_box' => $storageBox->barcode,
                'material_id' => $material->id,
                'quantity' => $quantity,
                'in_out' => 'input',
                'event' => Transaction::STORAGE_BOX_INPUT,
                'event_key' => '',
                'user' => $user->id
            ]
        );
    }

    public function test_it_can_throw_validation_while_5F_bind_3F_material()
    {
        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $material = $this->createMaterial('107MB18501C', '');
        $quantity = $this->faker->randomNumber(2);

        $storageBox = $this->createStorageBox($warehouse, [
            'prefix' => 'F',
            'barcode' => 'F00001',
            'warehouse_id' => $warehouse->id,
            'location' => '',
            'sku' => $material->sku,
            'initial_quantity' => $quantity,
            'status' => StorageBox::BOUND,
            'is_empty' => false,
            'bound_material_at' => Carbon::now()
        ]);

        $this->createStorageBoxItem($storageBox, $material, $quantity);

        $location = $this->createLocation($warehouse, 'A');
        $jsonString = sprintf(<<<'EOL'
        {
            "location": "%s",
            "storageBox": "%s"
        }
        EOL, $location->barcode, $storageBox->barcode);
        $payload = json_decode($jsonString, true);

        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(BindLocationService::class)
            ->setPayload($payload)
            ->exec();
    }
}
