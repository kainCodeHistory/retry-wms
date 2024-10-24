<?php

namespace Tests\Feature\Services\B2B;

use App\Models\B2BStockLog;
use App\Models\StorageBox\StorageBox;
use App\Services\B2B\UpdateQuantityService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Libs\ShippingServer\ShippingServerService;

use Tests\GeneralTestCase;

class UpdateQuantityServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_exception_while_update_quantity()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(UpdateQuantityService::class)
            ->setPayload([])
            ->exec();
    }

    public function test_it_can_update_quantity_with_adjust_event()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $location = $warehouse->code . 'A-07-04';
        $material = $this->createMaterial('SPE01229H4', 'SPE01229H4');
        $quantity = $this->faker->randomNumber(2);

        $storageBox = $this->createStorageBox($warehouse, [
            'prefix' => 'K',
            'barcode' => 'K00001',
            'warehouse_id' => $warehouse->id,
            'location' => $location,
            'sku' => $material->check_sku,
            'initial_quantity' => $quantity,
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);

        $storageBoxItem = $this->createStorageBoxItem($storageBox, $material, $quantity);

        $workingDay = Carbon::now();
        $this->createB2BStock($material->sku, $storageBoxItem->quantity);
        $this->createB2BStockLog($material->sku, $storageBoxItem->quantity, $storageBoxItem->quantity, $workingDay);

        $adjustQuantity = 48;
        $note = $this->faker->word();

        $jsonString = sprintf(<<<'EOL'
        {
            "adjustQuantity": %s,
            "event": "%s",
            "storageBox": "%s",
            "note": "%s",
            "sku": "%s"
        }
        EOL, $adjustQuantity, B2BStockLog::ADJUST, $storageBox->barcode, $note, $material->sku);
        $payload = json_decode($jsonString, true);

        app(UpdateQuantityService::class)
            ->setPayload($payload)
            ->exec();



        $this->assertDatabaseHas(
            'b2b_stock',
            [
                'sku' => $material->sku,
                'total_quantity' => $adjustQuantity
            ]
        );

        $this->assertDatabaseHas(
            'b2b_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $material->sku,
                'quantity' => $adjustQuantity - $storageBoxItem->quantity,
                'balance' => $adjustQuantity,
                'event' => B2BStockLog::ADJUST,
                'event_key' => $storageBox->barcode,
                'note' => $note
            ]
        );
    }

    public function test_it_can_update_quantity_with_input_event()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $location = $warehouse->code . 'A-07-04';
        $material = $this->createMaterial('SPE01229H4', 'SPE01229H4');
        $quantity = $this->faker->randomNumber(2);

        $storageBox = $this->createStorageBox($warehouse, [
            'prefix' => 'K',
            'barcode' => 'K00001',
            'warehouse_id' => $warehouse->id,
            'location' => $location,
            'sku' => $material->check_sku,
            'initial_quantity' => $quantity,
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);

        $storageBoxItem = $this->createStorageBoxItem($storageBox, $material, $quantity);

        $workingDay = Carbon::now();
        $this->createB2BStock($material->sku, $storageBoxItem->quantity);
        $this->createB2BStockLog($material->sku, $storageBoxItem->quantity, $storageBoxItem->quantity, $workingDay);

        $adjustQuantity = 3;
        $note = $this->faker->word();

        $event = $this->faker->randomElement([B2BStockLog::ITEM_RETURN, B2BStockLog::TRANSFER_INPUT, B2BStockLog::STOCK_INPUT]);
        $jsonString = sprintf(<<<'EOL'
        {
            "adjustQuantity": %s,
            "event": "%s",
            "storageBox": "%s",
            "note": "%s",
            "sku": "%s"
        }
        EOL, $adjustQuantity, $event, $storageBox->barcode, $note, $material->sku);
        $payload = json_decode($jsonString, true);

        //TODO B2B
        //寫入shipping_server b2b_stock/b2b_picking_area_inventory相關
        // $mock = $this->mock(ShippingServerService::class);
        // $mock->shouldReceive('upsertB2BPickingAreaInventory')
        //     ->withArgs([$material->sku, $event, $location, 0, $adjustQuantity])
        //     ->once()
        //     ->andReturn();

        app(UpdateQuantityService::class)
            ->setPayload($payload)
            ->exec();



        $this->assertDatabaseHas(
            'b2b_stock',
            [
                'sku' => $material->sku,
                'total_quantity' => $storageBoxItem->quantity + $adjustQuantity
            ]
        );

        $this->assertDatabaseHas(
            'b2b_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $material->sku,
                'quantity' => $adjustQuantity,
                'balance' => $storageBoxItem->quantity + $adjustQuantity,
                'event' => $event,
                'event_key' => $storageBox->barcode,
                'note' => $note
            ]
        );
    }

    public function test_it_can_update_quantity_with_transfer_output_event()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $location = $warehouse->code . 'A-07-04';
        $material = $this->createMaterial('SPE01229H4', 'SPE01229H4');
        $quantity = $this->faker->randomNumber(3);
        $storageBox = $this->createStorageBox($warehouse, [
            'prefix' => 'K',
            'barcode' => 'K00001',
            'warehouse_id' => $warehouse->id,
            'location' => $location,
            'sku' => $material->check_sku,
            'initial_quantity' => $quantity,
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);

        $storageBoxItem = $this->createStorageBoxItem($storageBox, $material, $quantity);

        $workingDay = Carbon::now();
        $this->createB2BStock($material->sku, $storageBoxItem->quantity);
        $this->createB2BStockLog($material->sku, $storageBoxItem->quantity, $storageBoxItem->quantity, $workingDay);

        $adjustQuantity = 10;
        $note = $this->faker->word();

        $jsonString = sprintf(<<<'EOL'
        {
            "adjustQuantity": %s,
            "event": "%s",
            "storageBox": "%s",
            "note": "%s",
            "sku": "%s"
        }
        EOL, $adjustQuantity, B2BStockLog::TRANSFER_OUTPUT, $storageBox->barcode, $note, $material->sku);
        $payload = json_decode($jsonString, true);

        //TODO B2B
        //寫入shipping_server b2b_stock/b2b_picking_area_inventory相關
        // $mock = $this->mock(ShippingServerService::class);
        // $mock->shouldReceive('upsertB2BPickingAreaInventory')
        //     ->withArgs([$material->sku, B2BStockLog::TRANSFER_OUTPUT, $location, 0, $adjustQuantity])
        //     ->once()
        //     ->andReturn();

        app(UpdateQuantityService::class)
            ->setPayload($payload)
            ->exec();



        $this->assertDatabaseHas(
            'b2b_stock',
            [
                'sku' => $material->sku,
                'total_quantity' => $storageBoxItem->quantity - $adjustQuantity
            ]
        );

        $this->assertDatabaseHas(
            'b2b_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $material->sku,
                'quantity' => $adjustQuantity,
                'balance' => $storageBoxItem->quantity - $adjustQuantity,
                'event' => B2BStockLog::TRANSFER_OUTPUT,
                'event_key' => $storageBox->barcode,
                'note' => $note
            ]
        );
    }
}
