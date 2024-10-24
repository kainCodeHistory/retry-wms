<?php

namespace Tests\Feature\Services\StorageBox\Input;

use App\Models\StorageBox\StorageBox;
use App\Models\Transaction;
use App\Services\StorageBox\Input\BindMaterialService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use Tests\GeneralTestCase;

class BindMaterialServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_exception_while_bind_material()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(BindMaterialService::class)
            ->setPayload([])
            ->exec();
    }

    public function test_it_can_bind_material()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com'
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $material = $this->createMaterial('107MB18501C', '');

        $storageBox = $this->creaetEmptyStorageBox($warehouse, 'F00001');

        $location = $this->createLocation($warehouse, 'A', 0, $storageBox->barcode);
        $this->createStorageItem($material, $location);

        $batchNo = $this->faker->bothify('?#?#?#?#?#');
        $quantity = $this->faker->randomNumber(2);
        $jsonString = sprintf(<<<'EOL'
        {
            "batchNo": "%s",
            "storageBox": "%s",
            "sku": "%s",
            "quantity": %s
        }
        EOL, $batchNo, $storageBox->barcode, $material->sku, $quantity);
        $payload = json_decode($jsonString, true);


        app(BindMaterialService::class)
            ->setPayload($payload)
            ->exec();

        $storageBox = \App\Models\StorageBox\StorageBox::where('id', $storageBox->id)->first();
        $this->assertEquals(StorageBox::BOUND, $storageBox->status);
        $this->assertEquals($material->sku, $storageBox->sku);
        $this->assertEquals($quantity, $storageBox->initial_quantity);
        $this->assertNotEmpty($storageBox->bound_material_at);

        $this->assertDatabaseHas(
            'storage_box_items',
            [
                'storage_box_id' => $storageBox->id,
                'material_id' => $material->id,
                'batch_no' => $batchNo,
                'quantity' => $quantity
            ]
        );

        $this->assertDatabaseHas(
            'transactions',
            [
                'warehouse_id' => null,
                'location' => '',
                'storage_box' => $storageBox->barcode,
                'material_id' => $material->id,
                'material_sku' => $material->sku,
                'batch_no' => $batchNo,
                'quantity' => $quantity,
                'in_out' => 'input',
                'event' => Transaction::ITEM_BOUND,
                'event_key' => '',
                'user' => Auth::user()->id
            ]
        );
    }
}
