<?php

namespace Tests\Feature\Services\PickingArea\Refill;

use App\Models\StorageBox\StorageBox;
use App\Services\PickingArea\Refill\GetLocationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Tests\GeneralTestCase;

class GetLocationServiceTest extends GeneralTestCase
{
    public function test_it_can_get_location_with_replace_type()
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
        $storageBoxA = $this->createStorageBox($pickingArea, [
            'prefix' => 'A',
            'barcode' => 'A00001',
            'warehouse_id' => $pickingArea->id,
            'location' => sprintf('%sA-07-03-1', $pickingArea->code),
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
            'location' => sprintf('%sB-09-02', $semiFinishedProductArea->code),
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(3),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);

        $storageBoxItemA = $this->createStorageBoxItem($storageBoxA, $material, $storageBoxA->initial_quantity);
        $storageBoxItemB = $this->createStorageBoxItem($storageBoxB, $material, $storageBoxB->initial_quantity);

        $refill = $this->createRefillRecord($storageBoxA, $storageBoxItemA, $storageBoxB, $storageBoxItemB, $storageBoxItemB->quantity, 'replace', 'processing');

        $data = app(GetLocationService::class)
            ->setStorageBox($storageBoxB->barcode)
            ->exec();

        $this->assertEquals(
            [
                'id' => $refill->id,
                'designatedLocation' => $storageBoxA->location,
                'boxes' => [
                    [
                        'barcode' => $storageBoxA->barcode,
                        'release' => false
                    ]
                ]
            ],
            $data
        );
    }
}
