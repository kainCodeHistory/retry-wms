<?php

namespace Database\Seeders;

use App\Models\Factory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->seedDefaultUsers(2);
        $this->seedDefaultFactory(['測試廠']);
        $this->seedDefaultWarehouse();
        $this->seedDefaultStorageBoxes();
        $this->seedDefaultLocations();
        $this->seedDefaultMaterials();
    }

    private function seedDefaultUsers(int $quantity)
    {
        for ($k = 0; $k < $quantity; $k++) {
            $suffix = substr('0' . ($k + 1), -2);
            \App\Models\User::factory()->create([
                'name' => 'user' . $suffix,
                'email' => 'user' . $suffix . '@tests.com',
                'password' => Hash::make('123456')
            ]);
        }
    }

    private function seedDefaultFactory(array $factories)
    {
        $factoryRepository = new \App\Repositories\FactoryRepository();
        foreach ($factories as $factory) {
            $factoryRepository->create([
                'name' => $factory
            ]);
        }
    }

    private function seedDefaultWarehouse()
    {
        $factoryRepository = new \App\Repositories\FactoryRepository();
        $factory = $factoryRepository->search([
            'name' => '測試廠'
        ])->first();
        $warehouseRepository = new \App\Repositories\WarehouseRepository();
        $now = Carbon::now();
        $warehouses = [
            [
                'factory_id' => $factory->id,
                'code' => 'X',
                'name' => $factory->name . '-撿料倉',
                'tt_code' => 'test01',
                'is_picking_area' => true,
                'activate' => true,
                'created_at' => $now,
                'updated_at' => $now
            ]
        ];

        $warehouseRepository->createMany($warehouses);
    }

    private function seedDefaultLocations()
    {
        $locationRepository = new \App\Repositories\LocationRepository();
        $locations = [];

        $factoryRepository = new \App\Repositories\FactoryRepository();
        $factory = $factoryRepository->search([
            'name' => '測試廠'
        ])->get(0);

        $warehouseRepository = new \App\Repositories\WarehouseRepository();
        $warehouse = $warehouseRepository->search([
            'factory_id' => $factory->id,
            'tt_code' => 'test01',
            'is_picking_area' => true
        ])->get(0);

        $now = Carbon::now();

        for ($rack = 0; $rack < 2; $rack++) {
            for ($row = 0; $row < 2; $row++) {
                for ($column = 0; $column < 4; $column++) {
                    $barcode = $warehouse->code . 'A-' . substr('00' . ($rack + 1), -2) . '-' . substr('00' . ($row + 1), -2) . '-' . ($column + 1);
                    $locations[] = [
                        'factory_id' => $factory->id,
                        'warehouse_id' => $warehouse->id,
                        'barcode' => $barcode,
                        'zone' => 'A',
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }
            }
        }
        $locationRepository->createMany($locations);
        $locations = [];


        // 罕見品區
        for ($rack = 0; $rack < 2; $rack++) {
            for ($row = 0; $row < 2; $row++) {
                for ($column = 0; $column < 8; $column++) {
                    $barcode = $warehouse->code . 'C-' . substr('00' . ($rack + 1), -2) . '-' . substr('00' . ($row + 1), -2) . '-' . ($column + 1);
                    $locations[] = [
                        'factory_id' => $factory->id,
                        'warehouse_id' => $warehouse->id,
                        'barcode' => $barcode,
                        'zone' => 'C',
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }
            }
        }
        $locationRepository->createMany($locations);



        $locations = [];


        for ($rack = 0; $rack < 2; $rack++) {
            for ($row = 0; $row < 2; $row++) {
                $barcode = 'XA' . '-' . substr('00' . ($rack + 1), -2) . '-' . substr('00' . ($row + 1), -2);
                $locations[] = [
                    'factory_id' => $factory->id,
                    'warehouse_id' => 1,
                    'barcode' => $barcode,
                    'zone' => 'A',
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
        }

        $locationRepository->createMany($locations);

        $locations = [];
        // 特殊區
        for ($rack = 0; $rack < 1; $rack++) {
            for ($row = 0; $row < 10; $row++) {
                for ($column = 0; $column < 7; $column++) {
                    $barcode =  'XB-' . substr('00' . ($rack + 1), -2) . '-' . substr('00' . ($row + 1), -2) . '-' . ($column + 1);
                    $locations[] = [
                        'factory_id' => $factory->id,
                        'warehouse_id' => 1,
                        'barcode' => $barcode,
                        'zone' => 'B',
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }
            }
        }


        $locationRepository->createMany($locations);
        $locations = [];
    }

    private function seedDefaultStorageBoxes()
    {
        $factory = \App\Models\Factory::where('name', '測試廠')->first();

        $this->createStorageBoxes($factory, 'A', 5, 100);
        $this->createStorageBoxes($factory, 'B', 5, 150);
    }

    private function createStorageBoxes(Factory $factory, string $prefix, int $padLength, int $quantity)
    {
        $storageBoxRepository = new \App\Repositories\StorageBox\StorageBoxRepository();

        for ($k = 1; $k <= $quantity; $k++) {
            $barcode = $prefix . substr('0000' . $k, -$padLength);

            $storageBoxRepository->create([
                'prefix' => $prefix,
                'barcode' => $barcode,
                'factory_id' => $factory->id,
                'warehouse_id' => null,
                'location' => '',
                'is_empty' => 1
            ]);
        }
    }


    private function seedDefaultMaterials()
    {
        $materialRepository = new \App\Repositories\MaterialRepository();

        $materialRepository->create([

            'sku' => 'a123456',
            'display_name' => 'iphonexx',
            'full_name' => 'iphonexx',
            'check_sku' => 'a123456',
            'ean' => '1111'
        ]);
    }
}
