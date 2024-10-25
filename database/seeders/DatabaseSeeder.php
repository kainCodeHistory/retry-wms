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
        $this->seedDefaultUsers(4);
        $this->seedDefaultFactory(['測試廠']);
        $this->seedDefaultWarehouse();
        $this->seedDefaultStorageBoxes();
        $this->seedDefaultLocations();
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
                'code' => 'A',
                'name' => $factory->name . '-B2C 撿料倉',
                'tt_code' => 'test02',
                'is_picking_area' => true,
                'activate' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'factory_id' => $factory->id,
                'code' => 'B',
                'name' => $factory->name . '-B2C預備倉',
                'tt_code' => 'test02',
                'is_picking_area' => false,
                'activate' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'factory_id' => $factory->id,
                'code' => 'X',
                'name' => $factory->name . '-B2B撿料倉',
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
            'tt_code' => 'test02',
            'is_picking_area' => true
        ])->get(0);

        $now = Carbon::now();
        // 雙箱區
        for ($rack = 0; $rack < 4; $rack++) {
            for ($row = 0; $row < 24; $row++) {
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

        // 單箱區
        for ($rack = 0; $rack < 8; $rack++) {
            for ($row = 0; $row < 24; $row++) {
                for ($column = 0; $column < 4; $column++) {
                    $barcode = $warehouse->code . 'B-' . substr('00' . ($rack + 1), -2) . '-' . substr('00' . ($row + 1), -2) . '-' . ($column + 1);
                    $locations[] = [
                        'factory_id' => $factory->id,
                        'warehouse_id' => $warehouse->id,
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

        // 罕見品區
        for ($rack = 0; $rack < 11; $rack++) {
            for ($row = 0; $row < 9; $row++) {
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

        $warehouse = $warehouseRepository->search([
            'factory_id' => $factory->id,
            'tt_code' => 'test02',
            'is_picking_area' => false
        ])->get(0);

        $zones = ['A', 'B', 'C', 'D', 'E'];
        foreach ($zones as $zone) {
            for ($rack = 0; $rack < 12; $rack++) {
                for ($row = 0; $row < 10; $row++) {
                    $barcode = $warehouse->code . $zone . '-' . substr('00' . ($rack + 1), -2) . '-' . substr('00' . ($row + 1), -2);
                    $locations[] = [
                        'factory_id' => $factory->id,
                        'warehouse_id' => $warehouse->id,
                        'barcode' => $barcode,
                        'zone' => $zone,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }
            }
        }
        $locationRepository->createMany($locations);

        $locations = [];

        // B2B紙箱Ａ區
        for ($rack = 0; $rack < 10; $rack++) {
            for ($row = 0; $row < 26; $row++) {
                $barcode = 'XA' . '-' . substr('00' . ($rack + 1), -2) . '-' . substr('00' . ($row + 1), -2);
                $locations[] = [
                    'factory_id' => $factory->id,
                    'warehouse_id' => 3,
                    'barcode' => $barcode,
                    'zone' => 'A',
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
        }

        $locationRepository->createMany($locations);

        $locations = [];
        // B2B小藍箱B區
        for ($rack = 0; $rack < 1; $rack++) {
            for ($row = 0; $row < 40; $row++) {
                for ($column = 0; $column < 7; $column++) {
                    $barcode =  'XB-' . substr('00' . ($rack + 1), -2) . '-' . substr('00' . ($row + 1), -2) . '-' . ($column + 1);
                    $locations[] = [
                        'factory_id' => $factory->id,
                        'warehouse_id' => 3,
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

        // B2B小藍箱B區
        for ($rack = 1; $rack < 2; $rack++) {
            for ($row = 0; $row < 75; $row++) {
                for ($column = 0; $column < 7; $column++) {
                    $barcode =  'XB-' . substr('00' . ($rack + 1), -2) . '-' . substr('00' . ($row + 1), -2) . '-' . ($column + 1);
                    $locations[] = [
                        'factory_id' => $factory->id,
                        'warehouse_id' => 3,
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

        // B2B紙箱C區
        for ($rack = 0; $rack < 18; $rack++) {
            for ($row = 0; $row < 28; $row++) {
                $barcode = 'XC' . '-' . substr('00' . ($rack + 1), -2) . '-' . substr('00' . ($row + 1), -2);
                $locations[] = [
                    'factory_id' => $factory->id,
                    'warehouse_id' => 3,
                    'barcode' => $barcode,
                    'zone' => 'C',
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
        }

        $locationRepository->createMany($locations);


        $locations = [];

        // B2B大藍箱D區
        for ($rack = 0; $rack < 1; $rack++) {
            for ($row = 0; $row < 50; $row++) {
                $barcode = 'XD' . '-' . substr('00' . ($rack + 1), -2) . '-' . substr('00' . ($row + 1), -2);
                $locations[] = [
                    'factory_id' => $factory->id,
                    'warehouse_id' => 3,
                    'barcode' => $barcode,
                    'zone' => 'D',
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
        }

        $locationRepository->createMany($locations);

        $locations = [];

        // B2B紙箱L區
        for ($rack = 0; $rack < 1; $rack++) {
            for ($row = 0; $row < 8; $row++) {
                $barcode = 'XL' . '-' . substr('00' . ($rack + 1), -2) . '-' . substr('00' . ($row + 1), -2);
                $locations[] = [
                    'factory_id' => $factory->id,
                    'warehouse_id' => 3,
                    'barcode' => $barcode,
                    'zone' => 'L',
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
        }

        $locationRepository->createMany($locations);

        $locations = [];

        // B2B紙箱N區
        for ($rack = 0; $rack < 1; $rack++) {
            for ($row = 0; $row < 12; $row++) {
                $barcode = 'XN' . '-' . substr('00' . ($rack + 1), -2) . '-' . substr('00' . ($row + 1), -2);
                $locations[] = [
                    'factory_id' => $factory->id,
                    'warehouse_id' => 3,
                    'barcode' => $barcode,
                    'zone' => 'N',
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
        }

        $locationRepository->createMany($locations);
    }

    private function seedDefaultStorageBoxes()
    {
        $factory = \App\Models\Factory::where('name', '測試廠')->first();

        $this->createStorageBoxes($factory, 'A', 5, 1600);
        $this->createStorageBoxes($factory, 'B', 5, 150);
        $this->createStorageBoxes($factory, 'D', 5, 600);
        $this->createStorageBoxes($factory, 'F', 5, 900);
        $this->createStorageBoxes($factory, 'I', 5, 805);
        $this->createStorageBoxes($factory, 'J', 5, 300);
        $this->createStorageBoxes($factory, 'L', 5, 100);
    }

    private function createStorageBoxes(Factory $factory, string $prefix, int $padLength, int $quantity)
    {
        $storageBoxRepository = new \App\Repositories\StorageBox\StorageBoxRepository();

        for ($k = 1; $k <= $quantity; $k++) {
            $barcode = $prefix . substr('0000000000' . $k, -$padLength);

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
}
