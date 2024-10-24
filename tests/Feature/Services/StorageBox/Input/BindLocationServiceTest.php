<?php

namespace Tests\Feature\Services\StorageBox\Input;

use App\Models\StorageBox\StorageBox;
use App\Models\B2CStockLog;
use App\Models\Transaction;
use App\Services\StorageBox\Input\BindLocationService;
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

    public function test_it_can_throw_validation_exception_without_binding_material()
    {
        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $storageBox = $this->creaetEmptyStorageBox($warehouse, 'F00001');

        $this->createMaterial('107MB18501C', '');

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

    public function test_it_can_throw_validation_exception_without_default_location()
    {
        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $material = $this->createMaterial('107MB18501C', '');
        $storageBox = $this->createStorageBox($warehouse, [
            'prefix' => 'F',
            'barcode' => 'F00001',
            'warehouse_id' => null,
            'location' => '',
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(2),
            'status' => StorageBox::BOUND,
            'bound_material_at' => Carbon::now()
        ]);

        $location = $this->createLocation($warehouse, 'A');

        $this->createStorageBoxItem($storageBox, $material, $storageBox->initial_quantity);

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

    public function test_it_can_throw_exception_while_binding_aa_location()
    {
        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $location = $this->createLocation($warehouse, 'A');

        $material = $this->createMaterial('107MB18501C', '');
        $this->createStorageItem($material, $location);
        $storageBox1 = $this->createStorageBox($warehouse, [
            'prefix' => 'A',
            'barcode' => 'A00001',
            'warehouse_id' => $warehouse->id,
            'location' => $location->barcode,
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(2),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);
        $this->createStorageBoxItem($storageBox1, $material, $storageBox1->initial_quantity);

        $storageBox2 = $this->createStorageBox($warehouse, [
            'prefix' => 'A',
            'barcode' => 'A00002',
            'warehouse_id' => $warehouse->id,
            'location' => $location->barcode,
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(2),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);
        $this->createStorageBoxItem($storageBox2, $material, $storageBox2->initial_quantity);

        $storageBox3 = $this->createStorageBox($warehouse, [
            'prefix' => 'A',
            'barcode' => 'A00003',
            'warehouse_id' => null,
            'location' => '',
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(2),
            'status' => StorageBox::BOUND,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
        ]);
        $this->createStorageBoxItem($storageBox3, $material, $storageBox3->initial_quantity);

        $jsonString = sprintf(<<<'EOL'
        {
            "location": "%s",
            "storageBox": "%s"
        }
        EOL, $location->barcode, $storageBox3->barcode);
        $payload = json_decode($jsonString, true);

        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(BindLocationService::class)
            ->setPayload($payload)
            ->exec();
    }

    public function test_it_can_throw_exception_while_binding_ab_location()
    {
        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $location = $this->createLocation($warehouse, 'B');


        $material = $this->createMaterial('107MB18501C', '');
        $this->createStorageItem($material, $location);
        $storageBox1 = $this->createStorageBox($warehouse, [
            'prefix' => 'A',
            'barcode' => 'A00001',
            'warehouse_id' => $warehouse->id,
            'location' => $location->barcode,
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(2),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);
        $this->createStorageBoxItem($storageBox1, $material, $storageBox1->initial_quantity);

        $storageBox2 = $this->createStorageBox($warehouse, [
            'prefix' => 'A',
            'barcode' => 'A00002',
            'warehouse_id' => null,
            'location' => '',
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(2),
            'status' => StorageBox::BOUND,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
        ]);
        $this->createStorageBoxItem($storageBox2, $material, $this->faker->randomNumber(2));

        $jsonString = sprintf(<<<'EOL'
        {
            "location": "%s",
            "storageBox": "%s"
        }
        EOL, $location->barcode, $storageBox2->barcode);
        $payload = json_decode($jsonString, true);

        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(BindLocationService::class)
            ->setPayload($payload)
            ->exec();
    }

    public function test_it_can_throw_exception_without_default_material()
    {
        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $location = $this->createLocation($warehouse, 'A');

        $material = $this->createMaterial('107MB18501C', '');
        $storageBox1 = $this->createStorageBox($warehouse, [
            'prefix' => 'A',
            'barcode' => 'A00001',
            'warehouse_id' => $warehouse->id,
            'location' => $location->barcode,
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(2),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);

        $this->createStorageBoxItem($storageBox1, $material, $storageBox1->initial_quantity);

        $storageBox2 = $this->createStorageBox($warehouse, [
            'prefix' => 'A',
            'barcode' => 'A00002',
            'warehouse_id' => null,
            'location' => '',
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(2),
            'status' => StorageBox::BOUND,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
        ]);
        $this->createStorageBoxItem($storageBox2, $material, $this->faker->randomNumber(2));

        $jsonString = sprintf(<<<'EOL'
        {
            "location": "%s",
            "storageBox": "%s"
        }
        EOL, $location->barcode, $storageBox2->barcode);
        $payload = json_decode($jsonString, true);

        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(BindLocationService::class)
            ->setPayload($payload)
            ->exec();
    }

    public function test_it_can_throw_exception_with_different_material()
    {
        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $material1 = $this->createMaterial('107MB18501C', '');
        $location = $this->createLocation($warehouse, 'A');
        $this->createStorageItem($material1, $location);

        $storageBox1 = $this->createStorageBox($warehouse, [
            'prefix' => 'A',
            'barcode' => 'A00001',
            'warehouse_id' => $warehouse->id,
            'location' => $location->barcode,
            'sku' => $material1->sku,
            'initial_quantity' => $this->faker->randomNumber(2),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);
        $this->createStorageBoxItem($storageBox1, $material1, $storageBox1->initial_quantity);

        $material2 = $this->createMaterial('NPB01148C1T', '');
        $storageBox2 = $this->createStorageBox($warehouse, [
            'prefix' => 'A',
            'barcode' => 'A00002',
            'warehouse_id' => null,
            'location' => '',
            'sku' => $material2->sku,
            'initial_quantity' => $this->faker->randomNumber(2),
            'status' => StorageBox::BOUND,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
        ]);
        $this->createStorageBoxItem($storageBox2, $material2, $storageBox2->initial_quantity);

        $jsonString = sprintf(<<<'EOL'
        {
            "location": "%s",
            "storageBox": "%s"
        }
        EOL, $location->barcode, $storageBox2->barcode);
        $payload = json_decode($jsonString, true);

        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(BindLocationService::class)
            ->setPayload($payload)
            ->exec();
    }

    public function test_it_can_throw_exception_while_binding_ac_location()
    {
        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $location = $this->createLocation($warehouse, 'C');

        $material = $this->createMaterial('107MB18501C', '');
        $this->createStorageItem($material, $location);

        $storageBox1 = $this->createStorageBox($warehouse, [
            'prefix' => 'A',
            'barcode' => 'A00001',
            'warehouse_id' => $warehouse->id,
            'location' => $location->barcode,
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(2),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);

        $this->createStorageBoxItem($storageBox1, $material, $storageBox1->initial_quantity);

        $storageBox2 = $this->createStorageBox($warehouse, [
            'prefix' => 'A',
            'barcode' => 'A00002',
            'warehouse_id' => null,
            'location' => '',
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(2),
            'status' => StorageBox::BOUND,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
        ]);
        $this->createStorageBoxItem($storageBox2, $material, $storageBox2->initial_quantity);

        $jsonString = sprintf(<<<'EOL'
        {
            "location": "%s",
            "storageBox": "%s"
        }
        EOL, $location->barcode, $storageBox2->barcode);
        $payload = json_decode($jsonString, true);

        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(BindLocationService::class)
            ->setPayload($payload)
            ->exec();
    }

    public function test_it_can_bind_location_with_stock_input_event()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $material = $this->createMaterial('107MB18501C', '');

        $storageBox = $this->createStorageBox($warehouse,[
            'prefix' => 'F',
            'barcode' => 'F00001',
            'warehouse_id' => null,
            'location' => '',
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(2),
            'status' => StorageBox::BOUND,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
        ]);

        $quantity = $this->faker->randomNumber(2);
        $this->createStorageBoxItem($storageBox, $material, $quantity);

        $location = $this->createLocation($warehouse, 'A');
        $this->createStorageItem($material, $location);

        $jsonString = sprintf(<<<'EOL'
        {
            "location": "%s",
            "storageBox": "%s"
        }
        EOL, $location->barcode, $storageBox->barcode);
        $payload = json_decode($jsonString, true);

        $mock = $this->mock(ShippingServerService::class);
        $mock->shouldReceive('upsertPickingAreaInventory')
            ->withArgs([$material->sku, Transaction::STORAGE_BOX_INPUT, $location->barcode, $location->priority, $quantity])
            ->once()
            ->andReturn();

        app(BindLocationService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseHas(
            'storage_boxes',
            [
                'id' => $storageBox->id,
                'warehouse_id' => $warehouse->id,
                'location' => $location->barcode,
                'is_empty' => false,
                'status' => StorageBox::STORAGE
            ]
        );

        $this->assertDatabaseHas(
            'transactions',
            [
                'warehouse_id' => $warehouse->id,
                'location' => $location->barcode,
                'storage_box' => $storageBox->barcode,
                'material_id' => $material->id,
                'material_sku' => $material->sku,
                'quantity' => $quantity,
                'in_out' => 'input',
                'event' => Transaction::STORAGE_BOX_INPUT,
                'event_key' => '',
                'user' => Auth::user()->id
            ]
        );

        $this->assertDatabaseHas(
            'b2c_stock',
            [
                'sku' => $material->sku,
                'total_quantity' => $quantity
            ]
        );

        $this->assertDatabaseHas(
            'b2c_stock_logs',
            [
                'working_day' => Carbon::now()->format('Y-m-d'),
                'sku' => $material->sku,
                'quantity' => $quantity,
                'balance' => $quantity,
                'quantity' => $quantity,
                'event' => B2CStockLog::STOCK_INPUT,
                'event_key' => $storageBox->barcode,
                'note' => ''
            ]
        );
    }

    public function test_it_can_bind_location_with_adjust_location_event_01()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $semiFinishedProductArea = $this->createSemiFinishedProductArea($factory);

        $material = $this->createMaterial('107MB18501C', '');

        $storageBox = $this->createStorageBox($semiFinishedProductArea, [
            'prefix' => 'F',
            'barcode' => 'F00001',
            'warehouse_id' => null,
            'location' => '',
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(2),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now()
        ]);

        $quantity = $this->faker->randomNumber(2);
        $this->createStorageBoxItem($storageBox, $material, $quantity);

        $location = $this->createLocation($semiFinishedProductArea, 'A');
        $this->createStorageItem($material, $location);

        $jsonString = sprintf(<<<'EOL'
        {
            "location": "%s",
            "storageBox": "%s"
        }
        EOL, $location->barcode, $storageBox->barcode);

        $payload = json_decode($jsonString, true);

        $mock = $this->mock(ShippingServerService::class);
        $mock->shouldNotReceive('upsertPickingAreaInventory');

        app(BindLocationService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseHas(
            'storage_boxes',
            [
                'id' => $storageBox->id,
                'warehouse_id' => $semiFinishedProductArea->id,
                'location' => $location->barcode,
                'is_empty' => false,
                'status' => StorageBox::STORAGE
            ]
        );

        $this->assertDatabaseHas(
            'transactions',
            [
                'warehouse_id' => $semiFinishedProductArea->id,
                'location' => $location->barcode,
                'storage_box' => $storageBox->barcode,
                'material_id' => $material->id,
                'quantity' => $quantity,
                'in_out' => 'input',
                'event' => Transaction::ADJUST_LOCATION,
                'event_key' => '',
                'user' => $user->id
            ]
        );
    }

    public function test_it_can_bind_location_with_adjust_location_event_02()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $material = $this->createMaterial('107MB18501C', '');
        $storageBox = $this->createStorageBox($warehouse, [
            'prefix' => 'F',
            'barcode' => 'F00001',
            'warehouse_id' => null,
            'location' => '',
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(2),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
        ]);

        $quantity = $this->faker->randomNumber(2);
        $this->createStorageBoxItem($storageBox, $material, $quantity);

        $location = $this->createLocation($warehouse, 'A');
        $this->createStorageItem($material, $location);
        $jsonString = sprintf(<<<'EOL'
        {
            "location": "%s",
            "storageBox": "%s"
        }
        EOL, $location->barcode, $storageBox->barcode);
        $payload = json_decode($jsonString, true);

        $mock = $this->mock(ShippingServerService::class);
        $mock->shouldReceive('upsertPickingAreaInventory')
            ->withArgs([$material->sku, Transaction::ADJUST_LOCATION, $location->barcode, $location->priority, $quantity]);

        app(BindLocationService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseHas(
            'storage_boxes',
            [
                'id' => $storageBox->id,
                'warehouse_id' => $warehouse->id,
                'location' => $location->barcode,
                'is_empty' => false,
                'status' => StorageBox::STORAGE
            ]
        );

        $this->assertDatabaseHas(
            'transactions',
            [
                'warehouse_id' => $warehouse->id,
                'location' => $location->barcode,
                'storage_box' => $storageBox->barcode,
                'material_id' => $material->id,
                'quantity' => $quantity,
                'in_out' => 'input',
                'event' => Transaction::ADJUST_LOCATION,
                'event_key' => '',
                'user' => $user->id
            ]
        );
    }

    public function test_it_can_throw_validation_while_3F_bind_5F_material()
    {
        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $material = $this->createMaterial('107MB18501C', '');

        $storageBox = $this->createStorageBox($warehouse,[
            'prefix' => 'K',
            'barcode' => 'K00001',
            'warehouse_id' => null,
            'location' => '',
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(2),
            'status' => StorageBox::BOUND,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
        ]);

        $this->createStorageBoxItem($storageBox, $material, $this->faker->randomNumber(2));

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
