<?php

namespace Tests\Feature\Services\Material;

use App\Models\StorageBox\StorageBox;
use App\Services\Material\GetStorageBoxService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

use Tests\GeneralTestCase;

class GetStorageBoxServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_exception_while_get_storage_boxes()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(GetStorageBoxService::class)
            ->setPayload([])
            ->exec();
    }

    public function test_it_can_get_storage_boxes()
    {
        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $material = $this->createMaterial('GSP0320434', 'GSP0320434');

        $storageBox = $this->createStorageBox($warehouse, [
            'prefix' => 'A',
            'barcode' => 'A00001',
            'warehouse_id' => $warehouse->id,
            'location' => sprintf('%s%s', $warehouse->code, $this->faker->numerify('A-##-##-#')),
            'sku' => $material->sku,
            'initial_quantity' => $this->faker->randomNumber(3),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);

        $this->createStorageBoxItem($storageBox, $material, $storageBox->initial_quantity);

        $missingSku = $this->faker->bothify('???#######');
        $jsonString = sprintf(<<<'EOL'
        {
            "skus": [
                "%s",
                "%s"
            ]
        }
        EOL, $material->sku, $missingSku);
        $skus = json_decode($jsonString, true);

        $data = app(GetStorageBoxService::class)
            ->setPayload($skus)
            ->exec();

        $this->assertEquals(
            $data,
            [
                $material->sku => ['A00001']
            ]

        );
    }
}
