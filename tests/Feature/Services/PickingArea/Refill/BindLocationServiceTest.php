<?php

namespace Tests\Feature\Services\PickingArea\Refill;

use App\Models\StorageBox\StorageBox;
use App\Models\Transaction;
use App\Services\PickingArea\Refill\BindLocationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Libs\ShippingServer\ShippingServerService;

use Tests\GeneralTestCase;

class BindLocationServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_exception_while_bind_location()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(BindLocationService::class)
            ->setPayload([
                'location' => ''
            ])
            ->exec();
    }

    public function test_it_can_bind_location_with_single_storage_box()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $pickingArea = $this->createPickingArea($factory);
        $semiFinishedProductArea = $this->createSemiFinishedProductArea($factory);

        $material = $this->createMaterial('107MB18501C', '');

        $locationA = $this->createLocation($pickingArea, 'A');
        $locationB = $this->createLocation($semiFinishedProductArea, 'A');
        $this->createStorageItem($material, $locationA);
        $this->createStorageItem($material, $locationB);

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

        $storageBoxItemA = $this->createStorageBoxItem($storageBoxA, $material, $this->faker->randomNumber(1));
        $storageBoxItemB = $this->createStorageBoxItem($storageBoxB, $material, $storageBoxB->initial_quantity);

        $refill = $this->createRefillRecord($storageBoxA, $storageBoxItemA, $storageBoxB, $storageBoxItemB, $storageBoxItemB->quantity, 'replace', 'processing');

        $jsonString = sprintf(<<<'EOL'
        {
            "location": "%s",
            "releaseBoxes": %s,
            "storageBox": "%s"
        }
        EOL, $locationA->barcode, json_encode([$storageBoxA->barcode]), $storageBoxB->barcode);
        $payload = json_decode($jsonString, true);


        $mock = $this->mock(ShippingServerService::class);
        $mock->shouldReceive('upsertPickingAreaInventory')
            ->withArgs([$material->sku, Transaction::REFILL_INPUT, $locationA->barcode, $locationA->priority, $storageBoxB->initial_quantity])
            ->once()
            ->andReturn();

        app(BindLocationService::class)
            ->setPayload($payload)
            ->exec();

        // set status as completed
        $this->assertDatabaseHas(
            'picking_area_refill',
            [
                'id' => $refill->id,
                'status' => 'completed'
            ]
        );

        // storeage boxes
        $this->assertDatabaseHas(
            'storage_boxes',
            [
                'barcode' => $storageBoxB->barcode,
                'warehouse_id' => $pickingArea->id,
                'location' => $locationA->barcode,
                'is_empty' => 0,
                'status' => StorageBox::STORAGE
            ]
        );

        $this->assertDatabaseHas(
            'storage_boxes',
            [
                'barcode' => $storageBoxA->barcode,
                'warehouse_id' => null,
                'location' => '',
                'is_empty' => 1,
                'status' => ''
            ]
        );

        // set quantity
        $this->assertDatabaseHas(
            'storage_box_items',
            [
                'storage_box_id' => $storageBoxB->id,
                'material_id' => $material->id,
                'quantity' => $storageBoxItemA->quantity + $storageBoxB->initial_quantity
            ]
        );

        $this->assertDatabaseMissing(
            'storage_box_items',
            [
                'storage_box_id' => $storageBoxA->id
            ]
        );

        // transaction
        $this->assertDatabaseHas(
            'transactions',
            [
                'warehouse_id' => $pickingArea->id,
                'location' => $locationA->barcode,
                'storage_box' => $storageBoxA->barcode,
                'material_id' => $material->id,
                'quantity' => $storageBoxItemA->quantity,
                'in_out' => 'output',
                'event' => Transaction::REFILL_OUTPUT,
                'event_key' => $refill->id,
                'user' => $user->id
            ]
        );

        $this->assertDatabaseHas(
            'transactions',
            [
                'warehouse_id' => $pickingArea->id,
                'location' => $locationA->barcode,
                'storage_box' => $storageBoxB->barcode,
                'material_id' => $material->id,
                'quantity' => $storageBoxItemB->quantity,
                'in_out' => 'input',
                'event' => Transaction::REFILL_INPUT,
                'event_key' => $refill->id,
                'user' => $user->id
            ]
        );
    }

    public function test_it_can_bind_location_with_replace_type_and_two_storage_boxes()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
        ]);
        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $pickingArea = $this->createPickingArea($factory);
        $semiFinishedProductArea = $this->createSemiFinishedProductArea($factory);

        $material = $this->createMaterial('107MB18501C', '');

        $locationA = $this->createLocation($pickingArea, 'A');
        $locationB = $this->createLocation($semiFinishedProductArea, 'A');
        $this->createStorageItem($material, $locationA);
        $this->createStorageItem($material, $locationB);

        $storageBoxA1 = $this->createStorageBox($pickingArea, [
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

        $storageBoxA2 = $this->createStorageBox($pickingArea, [
            'prefix' => 'A',
            'barcode' => 'A00002',
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
            'barcode' => 'A00003',
            'warehouse_id' => $semiFinishedProductArea->id,
            'location' => $locationB,
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(3),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
        ]);

        $storageBoxItemA1 = $this->createStorageBoxItem($storageBoxA1, $material, $this->faker->randomNumber(1));
        $storageBoxItemA2 = $this->createStorageBoxItem($storageBoxA2, $material, $storageBoxA2->initial_quantity);
        $storageBoxItemB = $this->createStorageBoxItem($storageBoxB, $material, $storageBoxB->initial_quantity);

        $refill = $this->createRefillRecord($storageBoxA1, $storageBoxItemA1, $storageBoxB, $storageBoxItemB, $storageBoxItemB->quantity, 'replace', 'processing');

        $jsonString = sprintf(<<<'EOL'
        {
            "location": "%s",
            "releaseBoxes": %s,
            "storageBox": "%s"
        }
        EOL, $locationA->barcode, json_encode([$storageBoxA2->barcode]), $storageBoxB->barcode);
        $payload = json_decode($jsonString, true);

        $mock = $this->mock(ShippingServerService::class);
        $mock->shouldReceive('upsertPickingAreaInventory')
            ->withArgs([$material->sku, Transaction::REFILL_INPUT, $locationA->barcode, $locationA->priority, $storageBoxB->initial_quantity])
            ->once()
            ->andReturn();

        app(BindLocationService::class)
            ->setPayload($payload)
            ->exec();

        // set status as completed
        $this->assertDatabaseHas(
            'picking_area_refill',
            [
                'id' => $refill->id,
                'status' => 'completed'
            ]
        );

        // switch location
        $storageBoxB = \App\Models\StorageBox\StorageBox::where('id', $storageBoxB->id)->first();
        $this->assertEquals($pickingArea->id, $storageBoxB->warehouse_id);
        $this->assertEquals($locationA->barcode, $storageBoxB->location);
        $this->assertEquals(StorageBox::STORAGE, $storageBoxB->status);
        $this->assertNotEmpty($storageBoxB->bound_picking_area_at);

        $this->assertDatabaseHas(
            'storage_boxes',
            [
                'barcode' => $storageBoxA2->barcode,
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
            'storage_boxes',
            [
                'barcode' => $storageBoxA1->barcode,
                'warehouse_id' => $pickingArea->id,
                'location' => $locationA->barcode,
                'is_empty' => 0,
                'status' => StorageBox::STORAGE
            ]
        );

        // set quantity
        $this->assertDatabaseHas(
            'storage_box_items',
            [
                'storage_box_id' => $storageBoxA1->id,
                'material_id' => $material->id,
                'quantity' => $storageBoxItemA1->quantity + $storageBoxA2->initial_quantity
            ]
        );

        $this->assertDatabaseHas(
            'storage_box_items',
            [
                'storage_box_id' => $storageBoxB->id,
                'material_id' => $material->id,
                'quantity' => $storageBoxB->initial_quantity
            ]
        );

        $this->assertDatabaseMissing(
            'storage_box_items',
            [
                'storage_box_id' => $storageBoxA2->id
            ]
        );

        // transaction
        $this->assertDatabaseHas(
            'transactions',
            [
                'warehouse_id' => $pickingArea->id,
                'location' => $locationA->barcode,
                'storage_box' => $storageBoxA2->barcode,
                'material_id' => $material->id,
                'quantity' => $storageBoxItemA2->quantity,
                'in_out' => 'output',
                'event' => Transaction::REFILL_OUTPUT,
                'event_key' => $refill->id,
                'user' => $user->id
            ]
        );

        $this->assertDatabaseHas(
            'transactions',
            [
                'warehouse_id' => $pickingArea->id,
                'location' => $locationA->barcode,
                'storage_box' => $storageBoxB->barcode,
                'material_id' => $material->id,
                'quantity' => $refill->repl_quantity,
                'in_out' => 'input',
                'event' => Transaction::REFILL_INPUT,
                'event_key' => $refill->id,
                'user' => $user->id
            ]
        );
    }
}
