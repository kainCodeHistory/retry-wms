<?php

namespace Tests\Feature\Services\StorageBox;

use App\Models\B2CStockLog;
use App\Models\StorageBox\StorageBox;
use App\Services\StorageBox\UpdateQuantityService;
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

    // public function test_it_can_update_quantity_with_adjust_event()
    // {
    //     $user = $this->createUser([
    //         'email' => 'wmsuser@evolutivelabs.com',
    //         'password' => Hash::make('rhino5hield')
    //     ]);

    //     Auth::loginUsingId($user->id);

    //     $factory = $this->createFactory($this->faker->company);
    //     $warehouse = $this->createPickingArea($factory);

    //     $location = $warehouse->code . 'A-07-04';
    //     $material = $this->createMaterial('SPE01229H4', 'SPE01229H4');
    //     $quantity = $this->faker->randomNumber(2);

    //     $storageBox = $this->createStorageBox($warehouse, [
    //         'prefix' => 'F',
    //         'barcode' => 'F00001',
    //         'warehouse_id' => $warehouse->id,
    //         'location' => $location,
    //         'sku' => $material->check_sku,
    //         'initial_quantity' => $quantity,
    //         'status' => StorageBox::STORAGE,
    //         'is_empty' => false,
    //         'bound_material_at' => Carbon::now(),
    //         'bound_location_at' => Carbon::now(),
    //         'bound_picking_area_at' => Carbon::now()
    //     ]);

    //     $storageBoxItem = $this->createStorageBoxItem($storageBox, $material, $quantity);

    //     $workingDay = Carbon::now();
    //     $this->createB2CStock($material->sku, $storageBoxItem->quantity);
    //     $this->createB2CStockLog($material->sku, $storageBoxItem->quantity, $storageBoxItem->quantity, $workingDay);

    //     $adjustQuantity = 48;
    //     $note = $this->faker->word();

    //     $jsonString = sprintf(<<<'EOL'
    //     {
    //         "adjustQuantity": %s,
    //         "event": "%s",
    //         "storageBox": "%s",
    //         "note": "%s"
    //     }
    //     EOL, $adjustQuantity, B2CStockLog::ADJUST, $storageBox->barcode, $note);
    //     $payload = json_decode($jsonString, true);

    //     app(UpdateQuantityService::class)
    //         ->setPayload($payload)
    //         ->exec();

    //     // $this->assertDatabaseHas(
    //     //     'storage_box_items',
    //     //     [
    //     //         'storage_box_id' => $storageBoxItem->storage_box_id,
    //     //         'material_id' => $material->id,
    //     //         'quantity' => $adjustQuantity
    //     //     ]
    //     // );

    //     $this->assertDatabaseHas(
    //         'b2c_stock',
    //         [
    //             'sku' => $material->sku,
    //             'total_quantity' => $adjustQuantity
    //         ]
    //     );

    //     $this->assertDatabaseHas(
    //         'b2c_stock_logs',
    //         [
    //             'working_day' => $workingDay->format('Y-m-d'),
    //             'sku' => $material->sku,
    //             'quantity' => $adjustQuantity - $storageBoxItem->quantity,
    //             'balance' => $adjustQuantity,
    //             'event' => B2CStockLog::ADJUST,
    //             'event_key' => $storageBox->barcode,
    //             'note' => $note
    //         ]
    //     );
    // }

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
        $quantity = 45;

        $storageBox = $this->createStorageBox($warehouse, [
            'prefix' => 'F',
            'barcode' => 'F00001',
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
        $this->createB2CStock($material->sku, $storageBoxItem->quantity);
        $this->createB2CStockLog($material->sku, $storageBoxItem->quantity, $storageBoxItem->quantity, $workingDay);

        $adjustQuantity = 3;
        $note = $this->faker->word();

        $event = $this->faker->randomElement([B2CStockLog::ITEM_RETURN, B2CStockLog::TRANSFER_INPUT, B2CStockLog::STOCK_INPUT]);
        $jsonString = sprintf(<<<'EOL'
        {
            "adjustQuantity": %s,
            "event": "%s",
            "storageBox": "%s",
            "note": "%s"
        }
        EOL, $adjustQuantity, $event, $storageBox->barcode, $note);
        $payload = json_decode($jsonString, true);

        $mock = $this->mock(ShippingServerService::class);
        $mock->shouldReceive('upsertPickingAreaInventory')
            ->withArgs([$material->sku, $event, $location, 0, $adjustQuantity])
            ->once()
            ->andReturn();

        app(UpdateQuantityService::class)
            ->setPayload($payload)
            ->exec();

        // $this->assertDatabaseHas(
        //     'storage_box_items',
        //     [
        //         'storage_box_id' => $storageBoxItem->storage_box_id,
        //         'material_id' => $material->id,
        //         'quantity' => $storageBoxItem->quantity + $adjustQuantity
        //     ]
        // );

        $this->assertDatabaseHas(
            'b2c_stock',
            [
                'sku' => $material->sku,
                'total_quantity' => $storageBoxItem->quantity + $adjustQuantity
            ]
        );

        $this->assertDatabaseHas(
            'b2c_stock_logs',
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
            'prefix' => 'F',
            'barcode' => 'F00001',
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
        $this->createB2CStock($material->sku, $storageBoxItem->quantity);
        $this->createB2CStockLog($material->sku, $storageBoxItem->quantity, $storageBoxItem->quantity, $workingDay);

        $adjustQuantity = 10;
        $note = $this->faker->word();

        $jsonString = sprintf(<<<'EOL'
        {
            "adjustQuantity": %s,
            "event": "%s",
            "storageBox": "%s",
            "note": "%s"
        }
        EOL, $adjustQuantity, B2CStockLog::TRANSFER_OUTPUT, $storageBox->barcode, $note);
        $payload = json_decode($jsonString, true);

        $mock = $this->mock(ShippingServerService::class);
        $mock->shouldReceive('upsertPickingAreaInventory')
            ->withArgs([$material->sku, B2CStockLog::TRANSFER_OUTPUT, $location, 0, $adjustQuantity])
            ->once()
            ->andReturn();

        app(UpdateQuantityService::class)
            ->setPayload($payload)
            ->exec();

        // $this->assertDatabaseHas(
        //     'storage_box_items',
        //     [
        //         'storage_box_id' => $storageBoxItem->storage_box_id,
        //         'material_id' => $material->id,
        //         'quantity' => $storageBoxItem->quantity - $adjustQuantity
        //     ]
        // );

        $this->assertDatabaseHas(
            'b2c_stock',
            [
                'sku' => $material->sku,
                'total_quantity' => $storageBoxItem->quantity - $adjustQuantity
            ]
        );

        $this->assertDatabaseHas(
            'b2c_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $material->sku,
                'quantity' => $adjustQuantity,
                'balance' => $storageBoxItem->quantity - $adjustQuantity,
                'event' => B2CStockLog::TRANSFER_OUTPUT,
                'event_key' => $storageBox->barcode,
                'note' => $note
            ]
        );
    }

    private function createB2CStock(string $checkSku, int $quantity)
    {
        \App\Models\B2CStock::create([
            'sku' => $checkSku,
            'total_quantity' => $quantity
        ]);
    }

    private function createB2CStockLog(
        string $checkSku,
        int $quantity,
        int $balance,
        $workingDay
    ) {
        \App\Models\B2CStockLog::create([
            'working_day' => $workingDay,
            'sku' => $checkSku,
            'quantity' => $quantity,
            'balance' => $balance,
            'event' => 'initial',
            'event_key' => '',
            'note' => ''
        ]);
    }
}
