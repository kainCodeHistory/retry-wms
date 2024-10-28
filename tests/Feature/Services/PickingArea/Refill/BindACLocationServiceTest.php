<?php

namespace Tests\Feature\Services\PickingArea\Refill;

use App\Models\StorageBox\StorageBox;
use App\Models\Transaction;
use App\Services\PickingArea\Refill\BindACLocationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use Tests\GeneralTestCase;

class BindACLocationServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_exception_while_bind_ac_location()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(BindACLocationService::class)
            ->setPayload([])
            ->exec();
    }

    public function test_it_can_bind_ac_location()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@tests.com',
            'password' => Hash::make('123456')
        ]);
        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $pickingArea = $this->createPickingArea($factory);

        $material = $this->createMaterial('107MB18501C', '');
        $quantity = $this->faker->randomDigitNot(0);

        $storageBox = $this->creaetEmptyStorageBox($pickingArea, 'A00001');
        $location = $this->createLocation($pickingArea, 'C', 0, $storageBox->barcode);
        $this->createStorageItem($material, $location);

        $jsonString = sprintf(<<<'EOL'
        {
            "batchNo": "%s",
            "location": "%s",
            "quantity": %s,
            "sku": "%s",
            "storageBox": "%s"
        }
        EOL, $this->faker->word, $location->barcode, $quantity, $material->sku, $storageBox->barcode);
        $payload = json_decode($jsonString, true);



        app(BindACLocationService::class)
            ->setPayload($payload)
            ->exec();

        $storageBox = \App\Models\StorageBox\StorageBox::where('id', $storageBox->id)->first();
        $this->assertEquals($pickingArea->id, $storageBox->warehouse_id);
        $this->assertEquals($location->barcode, $storageBox->location);
        $this->assertEquals(0, $storageBox->is_empty);
        $this->assertEquals(StorageBox::STORAGE, $storageBox->status);
        $this->assertEquals($material->sku, $storageBox->sku);
        $this->assertEquals($quantity, $storageBox->initial_quantity);
        $this->assertNotEmpty($storageBox->bound_material_at);
        $this->assertNotEmpty($storageBox->bound_location_at);
        $this->assertNotEmpty($storageBox->bound_picking_area_at);

        $this->assertDatabaseHas(
            'storage_box_items',
            [
                'storage_box_id' => $storageBox->id,
                'material_id' => $material->id,
                'quantity' => $quantity
            ]
        );

        $this->assertDatabaseHas(
            'transactions',
            [
                'warehouse_id' => $pickingArea->id,
                'location' => $location->barcode,
                'storage_box' => $storageBox->barcode,
                'material_id' => $material->id,
                'quantity' => $quantity,
                'in_out' => 'input',
                'event' => Transaction::REFILL_INPUT,
                'user' => $user->id
            ]
        );
    }
}
