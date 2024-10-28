<?php

namespace Tests\Feature\Services\B2B;

use App\Models\B2BStockLog;
use App\Models\StorageBox\StorageBox;
use App\Services\B2B\B2BInventoryDebitService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Tests\GeneralTestCase;

class B2BInventoryDebitServiceTest extends GeneralTestCase
{
    public function test_it_can_save_batches()
    {
        $jsonString = sprintf(<<<EOL
        {
            "batchIds": [%s, %s],
            "pickedItems": [
                {
                    "sku": "A2-NPB0122956",
                    "location": "AA-06-18-3",
                    "quantity": %s,
                    "employeeNo": "123"
                },
                {
                    "sku": "SPF0314300",
                    "location": "AC-04-09-1",
                    "quantity": %s,
                    "employeeNo":"456"
                }
            ]
        }
        EOL, $this->faker->randomNumber(6), $this->faker->randomNumber(6), $this->faker->randomDigitNotZero(), $this->faker->randomDigitNotZero());
        $payload = json_decode($jsonString, true);


        app(B2BInventoryDebitService::class)
            ->setPayload($payload);

        $batchIds = $payload['batchIds'];
        sort($batchIds);
        $batchKey = implode("-", $batchIds);


        $workingDay = Carbon::now();
        foreach ($payload['pickedItems'] as $pickedItem) {
            $this->assertDatabaseHas(
                'b2b_picked_items',
                [
                    'batch_key' => $batchKey,
                    'picked_date' => $workingDay->format('Y-m-d'),
                    'sku' => $pickedItem['sku'],
                    'quantity' => $pickedItem['quantity']
                ]
            );
        }
    }


    public function test_it_can_inventory_debit_in_zone_xa()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@tests.com',
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
        $this->createFakeB2BStock($material->sku, $subtotal);
        $this->createFakeB2BStockLog($material->sku, $subtotal, $subtotal, $workingDay->format('Y-m-d'));

        $jsonString = sprintf(<<<EOL
        {
            "batchIds": [100201, 100202, 100203, 100204],
            "pickedItems": [
                {
                    "sku": "A2-NPB0122956",
                    "location": "AA-06-18-3",
                    "quantity": 1,
                    "employeeNo": "123"
                }
            ]
        }
        EOL);
        $payload = json_decode($jsonString, true);

        app(B2BInventoryDebitService::class)
            ->setPayload($payload)
            ->exec();

        $batchIds = $payload['batchIds'];
        sort($batchIds);

        $batchKey = implode("-", $batchIds);

        $sysTZ = config('app.timezone', 'UTC');
        $today = Carbon::now($sysTZ)->timezone('Asia/Taipei')->toDateString();

        foreach ($payload['pickedItems'] as $pickedItem) {
            $this->assertDatabaseHas(
                'b2b_picked_items',
                [
                    'batch_key' => $batchKey,
                    'picked_date' => $today,
                    'sku' => $pickedItem['sku'],
                    'quantity' => $pickedItem['quantity'],
                    'is_debited' => 1
                ]
            );
        }


        $this->assertDatabaseHas(
            'b2b_stock',
            [
                'sku' => $material->sku,
                'total_quantity' => $subtotal - 1
            ]
        );

        $this->assertDatabaseHas(
            'b2b_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $material->sku,
                'quantity' => 1,
                'balance' => $subtotal - 1,
                'event' => B2BStockLog::ITEM_PICK,
                'event_key' => $batchKey,
                'note' => ''
            ]
        );
    }

    public function test_it_can_inventory_debit_in_zone_xb()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@tests.com',
            'password' => Hash::make('rhino5hield')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $location1 =  'XB-06-18-3';
        $location2 =  'XC-07-03-1';

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
        $this->createFakeB2BStock($material1->sku, $quantity1);
        $this->createFakeB2BStockLog($material1->sku, $quantity1, $quantity1, $workingDay->format('Y-m-d'));

        $this->createFakeB2BStock($material2->sku, $quantity2);
        $this->createFakeB2BStockLog($material2->sku, $quantity2, $quantity2, $workingDay->format('Y-m-d'));

        $jsonString = file_get_contents(base_path('tests/Stub/b2bInventoryDebit.json'));
        $payload = json_decode($jsonString, true);

        app(B2BInventoryDebitService::class)
            ->setPayload($payload)
            ->exec();

        $batchIds = $payload['batchIds'];
        sort($batchIds);

        $batchKey = implode("-", $batchIds);

        $sysTZ = config('app.timezone', 'UTC');
        $today = Carbon::now($sysTZ)->timezone('Asia/Taipei')->toDateString();


        foreach ($payload['pickedItems'] as $pickedItem) {
            $this->assertDatabaseHas(
                'b2b_picked_items',
                [
                    'batch_key' => $batchKey,
                    'picked_date' => $today,
                    'sku' => $pickedItem['sku'],
                    'quantity' => $pickedItem['quantity'],
                    'is_debited' => 1
                ]
            );
        }

        $this->assertDatabaseHas(
            'b2b_stock',
            [
                'sku' => $material1->sku,
                'total_quantity' => $quantity1 - 2
            ]
        );

        $this->assertDatabaseHas(
            'b2b_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $material1->sku,
                'quantity' => 2,
                'balance' => $quantity1 - 2,
                'event' => B2BStockLog::ITEM_PICK,
                'event_key' => $batchKey,
                'note' => ''
            ]
        );


        $this->assertDatabaseHas(
            'b2b_stock',
            [
                'sku' => $material2->sku,
                'total_quantity' => $quantity2 - 1
            ]
        );

        $this->assertDatabaseHas(
            'b2b_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $material2->sku,
                'quantity' => 1,
                'balance' => $quantity2 - 1,
                'event' => B2BStockLog::ITEM_PICK,
                'event_key' => $batchKey,
                'note' => ''
            ]
        );
    }


    private function createFakeB2BStock(string $sku, int $quantity)
    {
        \App\Models\B2BStock::create([
            'sku' => $sku,
            'total_quantity' => $quantity
        ]);
    }

    private function createFakeB2BStockLog(string $sku, int $quantity, int $balance, $workingDay)
    {
        \App\Models\B2BStockLog::create([
            'working_day' => $workingDay,
            'sku' => $sku,
            'quantity' => $quantity,
            'balance' => $quantity,
            'event' => 'initial',
            'event_key' => '',
            'note' => ''
        ]);
    }

}
