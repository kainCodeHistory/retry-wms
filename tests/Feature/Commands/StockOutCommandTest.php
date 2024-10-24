<?php

namespace Tests\Feature\Commands;

use App\Jobs\InventoryDebitJob;
use App\Models\StorageBox\StorageBox;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Tests\GeneralTestCase;

class StockOutCommandTest extends GeneralTestCase
{
    public function test_it_can_stock_out_picked_item()
    {
        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $material = $this->createMaterial('A2-NPB0122956', '');
        $quantity = $this->faker->randomNumber(2);

        $storageBox = $this->createStorageBox($warehouse, [
            'prefix' => 'F',
            'barcode' => 'F00001',
            'warehouse_id' => $warehouse->id,
            'location' => $warehouse->code . 'A-06-18-3',
            'sku' => $material->sku,
            'initial_quantity' => $quantity,
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);

        $storageBoxItem = $this->createStorageBoxItem($storageBox, $material, $quantity);

        $batchKey = '1000-1001';

        $pickedDate = Carbon::now()->format('Y-m-d');
        \App\Models\PickedBatch::create([
            'batch_key' => $batchKey,
            'picked_date' => $pickedDate,
            'redo' => 0
        ]);

        $pickedBatchItem = \App\Models\PickedBatchItem::create([
            'batch_key' => $batchKey,
            'picked_date' => $pickedDate,
            'check_sku' => $material->sku,
            'location' => $storageBox->location,
            'quantity' => 1,
            'is_debited' => 0
        ]);

        $this->artisan("stock:out");

        $this->assertDatabaseHas(
            'picked_batch_items',
            [
                'id' => $pickedBatchItem->id,
                'is_debited' => 1
            ]
        );

        // $this->assertDatabaseHas(
        //     'storage_box_items',
        //     [
        //         'id' => $storageBoxItem->id,
        //         'quantity' => $storageBoxItem->quantity - 1
        //     ]
        // );
    }

    public function test_it_can_dispatch_redo_inventory_debit_job()
    {
        $batchKey = '1000-1001';

        $pickedDate = Carbon::now()->format('Y-m-d');
        \App\Models\PickedBatch::create([
            'batch_key' => $batchKey,
            'picked_date' => $pickedDate,
            'redo' => 1
        ]);

        $payload = [
            'batchIds' => [1000, 1001],
            'pickedItems' => [
                [
                    'check_sku' => 'A2-NPB0122956',
                    'location' => $this->faker->numerify('AA-##-##-#'),
                    'quantity' => $this->faker->randomDigitNotZero()
                ]
            ]
        ];

        Queue::fake();

        $this->mock(Client::class, function ($mock) use ($batchKey, $payload) {
            $mock->shouldReceive('request')
                ->with('GET', sprintf("%s/api/batch/picked-items/%s", config('app.nx.shipping_server.host'), $batchKey), [
                    'headers' => [
                        "Accept" => "application/json",
                        "Content-Type" => "application/json"
                    ],
                    'query' => []
                ])
                ->once()
                ->andReturn(new Response(200, [], json_encode($payload)));
        });

        $this->artisan("stock:out");

        Queue::assertPushed(InventoryDebitJob::class, function ($job) use ($payload) {
            return $job->payload === $payload;
        });
    }
}
