<?php

namespace Tests;

use App\Models\B2BStock;
use App\Models\B2BStockLog;
use App\Models\Factory;
use App\Models\Location;
use App\Models\Material;
use App\Models\PickingArea\Refill;
use App\Models\StorageBox\StorageBox;
use App\Models\StorageBox\StorageBoxItem;
use App\Models\StorageItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

abstract class GeneralTestCase extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function createUser(array $payload): User
    {
        return User::factory()->create($payload);
    }

    protected function createFactory(string $name): Factory
    {
        return Factory::create([
            'name' => $name
        ]);
    }

    // 揀料倉
    protected function createPickingArea(Factory $factory, string $code = 'A'): Warehouse
    {
        $payload = [
            'factory_id' => $factory->id,
            'name' => $this->faker->company,
            'code' => $code,
            'tt_code' => 'ED02',
            'is_picking_area' => 1,
            'activate' => 1
        ];
        return Warehouse::create($payload);
    }

    // 預備倉
    protected function createSemiFinishedProductArea(Factory $factory, string $code = 'B'): Warehouse
    {
        $payload = [
            'factory_id' => $factory->id,
            'name' => $this->faker->company,
            'code' => $code,
            'tt_code' => 'ED02',
            'is_picking_area' => 0,
            'activate' => 1
        ];
        return Warehouse::create($payload);
    }

    protected function createMaterial(string $sku, string $checkSku = '', array $payload = []): Material
    {

        return Material::create(array_merge([
            'sku' => $sku,
            'display_name' => $this->faker->name,
            'full_name' => $this->faker->name,
            'check_sku' => $checkSku,
            'check_for_leash' => false
        ], $payload));
    }

    protected function creaetEmptyStorageBox(Warehouse $warehouse, string $barcode)
    {
        $payload = [
            'factory_id' => $warehouse->factory_id,
            'prefix' => substr($barcode, 0, 1),
            'barcode' => $barcode,
            'is_empty' => true,
            'status' => '',
            'sku' => '',
            'initial_quantity' => 0
        ];

        return StorageBox::create($payload);
    }
    protected function createStorageBox(Warehouse $warehouse, array $payload): StorageBox
    {
        return StorageBox::create(array_merge([
            'factory_id' => $warehouse->factory_id
        ], $payload));
    }

    protected function createStorageBoxItem(StorageBox $storageBox, Material $material, int $quantity): StorageBoxItem
    {
        return StorageBoxItem::create([
            'storage_box_id' => $storageBox->id,
            'storage_box' => $storageBox->barcode,
            'material_id' => $material->id,
            'material_sku' => $material->sku,
            'material_name' => $material->display_name,
            'batch_no' => $this->faker->bothify('?#?#?#'),
            'quantity' => $quantity
        ]);
    }

    protected function createRefillRecord(StorageBox $storageBox, StorageBoxItem $storageBoxItem, StorageBox $replStorageBox, StorageBoxItem $replStorageBoxItem, int $pickQuantity, string $type = 'replace', string $status = 'pending'): Refill
    {
        return Refill::create([
            'material_id' => $storageBoxItem->material_id,
            'material_sku' => $storageBoxItem->material_sku,
            'warehouse_id' => $storageBox->warehouse_id,
            'location' => $storageBox->location,
            'storage_boxes' => json_encode([$storageBox->barcode]),
            'quantity' => $storageBoxItem->quantity,
            'fill_type' => $type,
            'repl_warehouse_id' => $replStorageBox->warehouse_id,
            'repl_location' => $replStorageBox->location,
            'repl_storage_box' => $replStorageBox->barcode,
            'repl_quantity' => $pickQuantity,
            'status' => $status
        ]);
    }

    protected function createLocation(Warehouse $warehouse, string $zone, int $priority = 0, string $defaultStorageBox = ''): Location
    {
        $rack = 1;
        $row = 1;
        $column = 1;
        $barcode = $warehouse->code . $zone . '-' . substr('00' . $rack, -2) . '-' . substr('00' . $row, -2);
        if ($warehouse->tt_code === 'ED02' && $warehouse->is_picking_area === 1) {
            $barcode .= '-' . $column;
        }

        return Location::create([
            'factory_id' => $warehouse->factory_id,
            'warehouse_id' => $warehouse->id,
            'barcode' => $barcode,
            'zone' => $zone,
            'priority' => $priority,
            'default_storage_box' => $defaultStorageBox
        ]);
    }

    protected function createStorageItem(Material $material, Location $location): StorageItem
    {
        return StorageItem::create([
            'material_id' => $material->id,
            'material_sku' => $material->sku,
            'material_name' => $material->display_name,
            'location_id' => $location->id,
            'location' => $location->barcode
        ]);
    }
    protected function createB2BStock(string $sku, int $quantity)
    {
        return B2BStock::create([
            'sku' => $sku,
            'total_quantity' => $quantity
        ]);
    }

    protected function createB2BStockLog(string $sku,int $quantity, int $balance, $workingDay) {
        return B2BStockLog::create([
            'working_day' => $workingDay,
            'sku' => $sku,
            'quantity' => $quantity,
            'balance' => $balance,
            'event' => 'initial',
            'event_key' => '',
            'note' => ''
        ]);
    }
}
