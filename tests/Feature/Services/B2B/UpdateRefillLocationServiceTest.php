<?php

namespace Tests\Feature\Services\B2B;

use App\Models\StorageBox\StorageBox;
use App\Services\B2B\UpdateRefillLocationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

use Tests\GeneralTestCase;

class UpdateRefillLocationServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(UpdateRefillLocationService::class)
            ->setPayload([])
            ->exec();
    }

    public function test_it_can_refill_input_storage_box()
    {
        $factory = $this->createFactory($this->faker->company);
        $pickingArea = $this->createPickingArea($factory);

        $material = $this->createMaterial('107MB18501C', '');
        $storageBoxA = $this->createStorageBox($pickingArea, [
            'prefix' => 'K',
            'barcode' => 'K00001',
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

        $storageBoxB = $this->createStorageBox($pickingArea, [
            'prefix' => 'K',
            'barcode' => 'K00002',
            'warehouse_id' => $pickingArea->id,
            'location' => sprintf('%sB-09-02', $pickingArea->code),
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(3),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
        ]);

        $storageBoxItemA = $this->createStorageBoxItem($storageBoxA, $material, 5);
        $storageBoxItemB = $this->createStorageBoxItem($storageBoxB, $material, $storageBoxB->initial_quantity);

        $user = $this->createUser([
            'name' => 'user',
            'email' => 'user@evolutivelabs.com'
        ]);
        Auth::loginUsingId($user->id);
        $jsonString = sprintf(<<<'EOL'
        {
            "quantity": %s,
            "inputStorageBox": "%s",
            "outputStorageBox": "%s"
        }
        EOL, 1, $storageBoxItemA->storage_box, $storageBoxItemB->storage_box);
        $payload = json_decode($jsonString, true);


        app(UpdateRefillLocationService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseHas(
            'transactions',
            [
               'storage_box' => $storageBoxItemA->storage_box,
               'material_sku' =>  $material->sku,
               'quantity' => 1,
               'in_out' => 'input',
               'event' => 'refill_input'
            ]
        );
    }
}
