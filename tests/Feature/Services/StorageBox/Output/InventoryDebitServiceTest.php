<?php

namespace Tests\Feature\Services\StorageBox\Output;

use App\Models\B2CStockLog;
use App\Models\StorageBox\StorageBox;
use App\Services\StorageBox\Output\InventoryDebitService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Libs\Slack\SlackService;

use Tests\GeneralTestCase;

class InventoryDebitServiceTest extends GeneralTestCase
{
    public function test_it_can_save_batches()
    {
        $jsonString = sprintf(<<<EOL
        {
            "batchIds": [%s, %s],
            "pickedItems": [
                {
                    "check_sku": "A2-NPB0122956",
                    "location": "AA-06-18-3",
                    "quantity": %s
                },
                {
                    "check_sku": "SPF0314300",
                    "location": "AC-04-09-1",
                    "quantity": %s
                }
            ]
        }
        EOL, $this->faker->randomNumber(6), $this->faker->randomNumber(6), $this->faker->randomDigitNotZero(), $this->faker->randomDigitNotZero());
        $payload = json_decode($jsonString, true);

        app(InventoryDebitService::class)
            ->setPayload($payload);

        $batchIds = $payload['batchIds'];
        sort($batchIds);
        $batchKey = implode("-", $batchIds);

        $this->assertDatabaseHas(
            'picked_batches',
            [
                'batch_key' => $batchKey,
                'redo' => 0
            ]
        );

        $workingDay = Carbon::now();
        foreach ($payload['pickedItems'] as $pickedItem) {
            $this->assertDatabaseHas(
                'picked_batch_items',
                [
                    'batch_key' => $batchKey,
                    'picked_date' => $workingDay->format('Y-m-d'),
                    'check_sku' => $pickedItem['check_sku'],
                    'location' => $pickedItem['location'],
                    'quantity' => $pickedItem['quantity']
                ]
            );
        }
    }

    public function test_it_can_inventory_debit_in_zone_aa01()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $location = $warehouse->code . 'A-06-18-3';
        $material = $this->createMaterial('A2-NPB0122956', '');

        $storageBox1 = $this->createStorageBox($warehouse, [
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

        $quantity1 = $this->faker->randomDigitNotZero();
        $quantity2 = $this->faker->randomNumber(3);

        $storageBoxItem1 = $this->createStorageBoxItem($storageBox1, $material, $quantity1);
        $storageBoxItem2 = $this->createStorageBoxItem($storageBox2, $material, $quantity2);

        $workingDay = Carbon::now();
        $subtotal = $quantity1 + $quantity2;
        $this->createFakeB2CStock($material->sku, $subtotal);
        $this->createFakeB2CStockLog($material->sku, $subtotal, $subtotal, $workingDay->format('Y-m-d'));

        $jsonString = sprintf(<<<EOL
        {
            "batchIds": [100201, 100202, 100203, 100204],
            "pickedItems": [
                {
                    "check_sku": "A2-NPB0122956",
                    "location": "AA-06-18-3",
                    "quantity": 1
                }
            ]
        }
        EOL);
        $payload = json_decode($jsonString, true);

        app(InventoryDebitService::class)
            ->setPayload($payload)
            ->exec();

        $batchIds = $payload['batchIds'];
        sort($batchIds);

        $batchKey = implode("-", $batchIds);

        $sysTZ = config('app.timezone', 'UTC');
        $today = Carbon::now($sysTZ)->timezone('Asia/Taipei')->toDateString();

        $this->assertDatabaseHas(
            'picked_batches',
            [
                'batch_key' => $batchKey,
                'picked_date' => $today,
            ]
        );

        foreach ($payload['pickedItems'] as $pickedItem) {
            $this->assertDatabaseHas(
                'picked_batch_items',
                [
                    'batch_key' => $batchKey,
                    'picked_date' => $today,
                    'check_sku' => $pickedItem['check_sku'],
                    'location' => $pickedItem['location'],
                    'quantity' => $pickedItem['quantity'],
                    'is_debited' => 1
                ]
            );
        }

        // $this->assertDatabaseHas(
        //     'storage_box_items',
        //     [
        //         'storage_box' => $storageBoxItem1->storage_box,
        //         'quantity' => $storageBoxItem1->quantity - 1
        //     ]
        // );

        // $this->assertDatabaseHas(
        //     'storage_box_items',
        //     [
        //         'storage_box' => $storageBoxItem2->storage_box,
        //         'quantity' => $storageBoxItem2->quantity
        //     ]
        // );

        $this->assertDatabaseHas(
            'b2c_stock',
            [
                'sku' => $material->sku,
                'total_quantity' => $subtotal - 1
            ]
        );

        $this->assertDatabaseHas(
            'b2c_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $material->sku,
                'quantity' => 1,
                'balance' => $subtotal - 1,
                'event' => B2CStockLog::ITEM_PICK,
                'event_key' => $batchKey,
                'note' => ''
            ]
        );
    }

    public function test_it_can_inventory_debit_in_zone_aa02()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $location = $warehouse->code . 'A-06-18-3';
        $material = $this->createMaterial('A2-NPB0122956', '');

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

        $quantity = $this->faker->randomDigitNotZero();
        $storageBoxItem = $this->createStorageBoxItem($storageBox, $material, $quantity);

        $workingDay = Carbon::now();
        $this->createFakeB2CStock($material->sku, $quantity);
        $this->createFakeB2CStockLog($material->sku, $quantity, $quantity, $workingDay->format('Y-m-d'));

        $jsonString = sprintf(<<<EOL
        {
            "batchIds": [100201, 100202, 100203, 100204],
            "pickedItems": [
                {
                    "check_sku": "A2-NPB0122956",
                    "location": "AA-06-18-3",
                    "quantity": 10
                }
            ]
        }
        EOL);
        $payload = json_decode($jsonString, true);

        $slackBlocks = $this->createSlackBlocks(B2CStockLog::ITEM_PICK, $material->sku, 10, sprintf("庫存不足 (原庫存: %s)。", $quantity));
        $this->mock(SlackService::class, function ($mock) use ($slackBlocks) {
            $mock->shouldReceive('sendMessageViaWebhookURL')
                ->withArgs([
                    config('app.slack.channel.nxl_logger'),
                    $slackBlocks
                ])
                ->once()
                ->andReturnNull();

            // $mock->shouldReceive('sendMessage')
            //     ->once()
            //     ->andReturnNull();
        });

        app(InventoryDebitService::class)
            ->setPayload($payload)
            ->exec();

        $batchIds = $payload['batchIds'];
        sort($batchIds);

        $batchKey = implode("-", $batchIds);

        $sysTZ = config('app.timezone', 'UTC');
        $today = Carbon::now($sysTZ)->timezone('Asia/Taipei')->toDateString();

        $this->assertDatabaseHas(
            'picked_batches',
            [
                'batch_key' => $batchKey,
                'picked_date' => $today,
            ]
        );

        foreach ($payload['pickedItems'] as $pickedItem) {
            $this->assertDatabaseHas(
                'picked_batch_items',
                [
                    'batch_key' => $batchKey,
                    'picked_date' => $today,
                    'check_sku' => $pickedItem['check_sku'],
                    'location' => $pickedItem['location'],
                    'quantity' => $pickedItem['quantity'],
                    'is_debited' => 1
                ]
            );
        }

        // $this->assertDatabaseHas(
        //     'storage_box_items',
        //     [
        //         'storage_box' => $storageBoxItem->storage_box,
        //         'quantity' => $storageBoxItem->quantity - 10
        //     ]
        // );

        $this->assertDatabaseHas(
            'b2c_stock',
            [
                'sku' => $material->sku,
                'total_quantity' => $quantity - 10
            ]
        );

        $this->assertDatabaseHas(
            'b2c_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $material->sku,
                'quantity' => 10,
                'balance' => $quantity - 10,
                'event' => B2CStockLog::ITEM_PICK,
                'event_key' => $batchKey,
                'note' => ''
            ]
        );
    }

    public function test_it_can_inventory_debit_in_zone_aa03()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $location = $warehouse->code . 'A-06-18-3';
        $material = $this->createMaterial('A2-NPB0122956', '');

        $storageBox1 = $this->createStorageBox($warehouse, [
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

        $quantity1 = $this->faker->randomDigitNotZero();
        $quantity2 = $this->faker->randomNumber(3);

        $storageBoxItem1 = $this->createStorageBoxItem($storageBox1, $material, $quantity1);
        $storageBoxItem2 = $this->createStorageBoxItem($storageBox2, $material, $quantity2);

        $subtotal = $quantity1 + $quantity2;
        $workingDay = Carbon::now();
        $this->createFakeB2CStock($material->sku, $subtotal);
        $this->createFakeB2CStockLog($material->sku, $subtotal, $subtotal, $workingDay->format('Y-m-d'));

        $jsonString = sprintf(<<<EOL
        {
            "batchIds": [100201, 100202, 100203, 100204],
            "pickedItems": [
                {
                    "check_sku": "A2-NPB0122956",
                    "location": "AA-06-18-3",
                    "quantity": 10
                }
            ]
        }
        EOL);
        $payload = json_decode($jsonString, true);

        app(InventoryDebitService::class)
            ->setPayload($payload)
            ->exec();

        $batchIds = $payload['batchIds'];
        sort($batchIds);

        $batchKey = implode("-", $batchIds);

        $sysTZ = config('app.timezone', 'UTC');
        $today = Carbon::now($sysTZ)->timezone('Asia/Taipei')->toDateString();

        $this->assertDatabaseHas(
            'picked_batches',
            [
                'batch_key' => $batchKey,
                'picked_date' => $today,
            ]
        );

        foreach ($payload['pickedItems'] as $pickedItem) {
            $this->assertDatabaseHas(
                'picked_batch_items',
                [
                    'batch_key' => $batchKey,
                    'picked_date' => $today,
                    'check_sku' => $pickedItem['check_sku'],
                    'location' => $pickedItem['location'],
                    'quantity' => $pickedItem['quantity'],
                    'is_debited' => 1
                ]
            );
        }

        // $this->assertDatabaseHas(
        //     'storage_box_items',
        //     [
        //         'storage_box' => $storageBoxItem1->storage_box,
        //         'quantity' => 0
        //     ]
        // );

        // $this->assertDatabaseHas(
        //     'storage_box_items',
        //     [
        //         'storage_box' => $storageBoxItem2->storage_box,
        //         'quantity' => $storageBoxItem2->quantity - (10 - $storageBoxItem1->quantity)
        //     ]
        // );

        $this->assertDatabaseHas(
            'b2c_stock',
            [
                'sku' => $material->sku,
                'total_quantity' => $subtotal - 10
            ]
        );

        $this->assertDatabaseHas(
            'b2c_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $material->sku,
                'quantity' => 10,
                'balance' => $subtotal - 10,
                'event' => B2CStockLog::ITEM_PICK,
                'event_key' => $batchKey,
                'note' => ''
            ]
        );
    }

    public function test_it_can_inventory_debit_in_zone_ab_ac()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $location1 = $warehouse->code . 'B-06-18-3';
        $location2 = $warehouse->code . 'C-07-03-1';

        $material1 = $this->createMaterial('A2-NPB0122956', '');
        $material2 = $this->createMaterial('A2-NPB01086J5', '');

        $storageBox1 = $this->createStorageBox($warehouse, [
            'prefix' => 'F',
            'barcode' => 'F00001',
            'warehouse_id' => $warehouse->id,
            'location' => $location1,
            'sku' => $material1->sku,
            'initial_quantity' => $this->faker->randomNumber(3),
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
            'location' => $location2,
            'sku' => $material2->sku,
            'initial_quantity' => $this->faker->randomNumber(3),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);

        $quantity1 = $this->faker->randomNumber(3);
        $quantity2 = $this->faker->randomNumber(2);

        $storageBoxItem1 = $this->createStorageBoxItem($storageBox1, $material1, $quantity1);
        $storageBoxItem2 = $this->createStorageBoxItem($storageBox2, $material2, $quantity2);

        $workingDay = Carbon::now();
        $this->createFakeB2CStock($material1->sku, $quantity1);
        $this->createFakeB2CStockLog($material1->sku, $quantity1, $quantity1, $workingDay->format('Y-m-d'));

        $this->createFakeB2CStock($material2->sku, $quantity2);
        $this->createFakeB2CStockLog($material2->sku, $quantity2, $quantity2, $workingDay->format('Y-m-d'));

        $jsonString = file_get_contents(base_path('tests/Stub/inventoryDebit.json'));
        $payload = json_decode($jsonString, true);

        app(InventoryDebitService::class)
            ->setPayload($payload)
            ->exec();

        $batchIds = $payload['batchIds'];
        sort($batchIds);

        $batchKey = implode("-", $batchIds);

        $sysTZ = config('app.timezone', 'UTC');
        $today = Carbon::now($sysTZ)->timezone('Asia/Taipei')->toDateString();

        $this->assertDatabaseHas(
            'picked_batches',
            [
                'batch_key' => $batchKey,
                'picked_date' => $today,
            ]
        );

        foreach ($payload['pickedItems'] as $pickedItem) {
            $this->assertDatabaseHas(
                'picked_batch_items',
                [
                    'batch_key' => $batchKey,
                    'picked_date' => $today,
                    'check_sku' => $pickedItem['check_sku'],
                    'location' => $pickedItem['location'],
                    'quantity' => $pickedItem['quantity'],
                    'is_debited' => 1
                ]
            );
        }

        // $this->assertDatabaseHas(
        //     'storage_box_items',
        //     [
        //         'storage_box' => $storageBoxItem1->storage_box,
        //         'quantity' => $storageBoxItem1->quantity - 2
        //     ]
        // );

        $this->assertDatabaseHas(
            'b2c_stock',
            [
                'sku' => $material1->sku,
                'total_quantity' => $quantity1 - 2
            ]
        );

        $this->assertDatabaseHas(
            'b2c_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $material1->sku,
                'quantity' => 2,
                'balance' => $quantity1 - 2,
                'event' => B2CStockLog::ITEM_PICK,
                'event_key' => $batchKey,
                'note' => ''
            ]
        );

        // $this->assertDatabaseHas(
        //     'storage_box_items',
        //     [
        //         'storage_box' => $storageBoxItem2->storage_box,
        //         'quantity' => $storageBoxItem2->quantity - 1
        //     ]
        // );

        $this->assertDatabaseHas(
            'b2c_stock',
            [
                'sku' => $material2->sku,
                'total_quantity' => $quantity2 - 1
            ]
        );

        $this->assertDatabaseHas(
            'b2c_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $material2->sku,
                'quantity' => 1,
                'balance' => $quantity2 - 1,
                'event' => B2CStockLog::ITEM_PICK,
                'event_key' => $batchKey,
                'note' => ''
            ]
        );
    }

    // public function test_it_can_send_slack_message()
    // {
    //     $user = $this->createUser([
    //         'email' => 'wmsuser@evolutivelabs.com',
    //         'password' => Hash::make('rhino5hield')
    //     ]);

    //     Auth::loginUsingId($user->id);

    //     $factory = $this->createFactory($this->faker->company);
    //     $warehouse = $this->createPickingArea($factory);

    //     $location = $warehouse->code . 'A-06-18-3';
    //     $material = $this->createMaterial('A2-NPB0122956', '');

    //     $storageBox1 = $this->createStorageBox($warehouse, [
    //         'prefix' => 'F',
    //         'barcode' => 'F00001',
    //         'warehouse_id' => $warehouse->id,
    //         'location' => $location,
    //         'sku' => $material->sku,
    //         'initial_quantity' => $this->faker->randomNumber(3),
    //         'status' => StorageBox::STORAGE,
    //         'is_empty' => false,
    //         'bound_material_at' => Carbon::now(),
    //         'bound_location_at' => Carbon::now(),
    //         'bound_picking_area_at' => Carbon::now()
    //     ]);

    //     $storageBox2 = $this->createStorageBox($warehouse, [
    //         'prefix' => 'F',
    //         'barcode' => 'F00002',
    //         'warehouse_id' => $warehouse->id,
    //         'location' => $location,
    //         'sku' => $material->sku,
    //         'initial_quantity' => $this->faker->randomNumber(3),
    //         'status' => StorageBox::STORAGE,
    //         'is_empty' => false,
    //         'bound_material_at' => Carbon::now(),
    //         'bound_location_at' => Carbon::now(),
    //         'bound_picking_area_at' => Carbon::now()
    //     ]);

    //     $quantity1 = $this->faker->randomDigitNotZero();
    //     $quantity2 = $this->faker->randomNumber(3);

    //     $this->createStorageBoxItem($storageBox1, $material, $quantity1);
    //     $this->createStorageBoxItem($storageBox2, $material, $quantity2);

    //     $jsonString = sprintf(<<<EOL
    //     {
    //         "batchIds": [100201, 100202, 100203, 100204],
    //         "pickedItems": [
    //             {
    //                 "check_sku": "A2-NPB0122956",
    //                 "location": "AB-06-18-3",
    //                 "quantity": 1
    //             }
    //         ]
    //     }
    //     EOL);
    //     $payload = json_decode($jsonString, true);

    //     $mock = $this->mock(SlackService::class);
    //     $mock->shouldReceive('sendMessage')
    //         ->once()
    //         ->andReturn([
    //             'status' => 'OK',
    //             'statusCode' => 200
    //         ]);

    //     app(InventoryDebitService::class)
    //         ->setPayload($payload)
    //         ->exec();

    //     $this->assertTrue(true);
    // }

    public function test_it_can_redo_inventory_debit()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $location = $warehouse->code . 'A-06-18-3';
        $material = $this->createMaterial('A2-NPB0122956', '');
        $quantity = $this->faker->randomNumber(2);

        $storageBox = $this->createStorageBox($warehouse, [
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

        $storageBoxItem = $this->createStorageBoxItem($storageBox, $material, $quantity);

        $workingDay = Carbon::now();
        $this->createFakeB2CStock($material->sku, $quantity);
        $this->createFakeB2CStockLog($material->sku, $quantity, $quantity, $workingDay->format('Y-m-d'));

        $batchId01 = 1000;
        $batchId02 = 1001;
        $batchKey = sprintf("%s-%s", $batchId01, $batchId02);

        \App\Models\PickedBatch::create([
            'batch_key' => $batchKey,
            'picked_date' => Carbon::now()->format('Y-m-d'),
            'redo' => 1
        ]);

        $jsonString = sprintf(<<<EOL
        {
            "batchIds": [%s, %s],
            "pickedItems": [
                {
                    "check_sku": "A2-NPB0122956",
                    "location": "AA-06-18-3",
                    "quantity": 1
                }
            ]
        }
        EOL, $batchId01, $batchId02);
        $payload = json_decode($jsonString, true);

        app(InventoryDebitService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseHas(
            'picked_batches',
            [
                'batch_key' => '1000-1001',
                'redo' => 0
            ]
        );

        $this->assertDatabaseHas(
            'picked_batch_items',
            [
                'batch_key' => '1000-1001',
                'check_sku' => 'A2-NPB0122956',
                'location' => 'AA-06-18-3',
                'quantity' => 1
            ]
        );

        // $this->assertDatabaseHas(
        //     'storage_box_items',
        //     [
        //         'id' => $storageBoxItem->id,
        //         'quantity' => $storageBoxItem->quantity - 1
        //     ]
        // );

        $this->assertDatabaseHas(
            'b2c_stock',
            [
                'sku' => $material->sku,
                'total_quantity' => $quantity - 1
            ]
        );

        $this->assertDatabaseHas(
            'b2c_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $material->sku,
                'quantity' => 1,
                'balance' => $quantity - 1,
                'event' => B2CStockLog::ITEM_PICK,
                'event_key' => $batchKey,
                'note' => ''
            ]
        );
    }

    private function createFakeB2CStock(string $sku, int $quantity)
    {
        \App\Models\B2CStock::create([
            'sku' => $sku,
            'total_quantity' => $quantity
        ]);
    }

    private function createFakeB2CStockLog(string $sku, int $quantity, int $balance, $workingDay)
    {
        \App\Models\B2CStockLog::create([
            'working_day' => $workingDay,
            'sku' => $sku,
            'quantity' => $quantity,
            'balance' => $quantity,
            'event' => 'initial',
            'event_key' => '',
            'note' => ''
        ]);
    }

    private function createSlackBlocks(string $event, string $sku, int $quantity, string $note): array
    {
        $blocks = [];
        array_push($blocks, [
            "type" => "header",
            "text" => [
                "type" => "plain_text",
                "text" => 'B2C 負庫存。'
            ]
        ]);

        array_push($blocks, [
            "type" => "section",
            "text" => [
                "type" => "mrkdwn",
                "text" => sprintf("*SKU*: `%s`", $sku)
            ]
        ]);

        array_push($blocks, [
            "type" => "section",
            "text" => [
                "type" => "mrkdwn",
                "text" => sprintf("*異動數量*: `%s`", $quantity)
            ]
        ]);

        array_push($blocks, [
            "type" => "section",
            "text" => [
                "type" => "mrkdwn",
                "text" => sprintf("*事件代碼*: `%s`", $event)
            ]
        ]);

        array_push($blocks, [
            "type" => "section",
            "text" => [
                "type" => "mrkdwn",
                "text" => sprintf("*錯誤訊息*: `%s`", $note)
            ]
        ]);

        return $blocks;
    }
}
