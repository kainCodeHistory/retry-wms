<?php

namespace Tests\Feature\Services\Material;

use App\Models\StorageBox\StorageBox;
use App\Services\Material\GetBomService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

use Tests\GeneralTestCase;

class GetBomServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_exception_while_get_boms()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(GetBomService::class)
            ->setPayload([])
            ->exec();
    }

    public function test_it_can_get_boms()
    {
        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);

        $checkSku = $this->createMaterial('A2-GSP0320434', '');
        $material = $this->createMaterial('GSP0320434', $checkSku->sku);

        $storageBox = $this->createStorageBox($warehouse, [
            'prefix' => 'A',
            'barcode' => 'A00001',
            'warehouse_id' => $warehouse->id,
            'location' => sprintf('%s%s', $warehouse->code, $this->faker->numerify('A-##-##-#')),
            'sku' => $checkSku->sku,
            'initial_quantity' => $this->faker->randomNumber(3),
            'status' => StorageBox::STORAGE,
            'is_empty' => false,
            'bound_material_at' => Carbon::now(),
            'bound_location_at' => Carbon::now(),
            'bound_picking_area_at' => Carbon::now()
        ]);

        $this->createStorageBoxItem($storageBox, $checkSku, $storageBox->initial_quantity);

        $missingSku = $this->faker->bothify('???#######');
        $jsonString = sprintf(<<<'EOL'
        {
            "skus": {
                "%s": 2,
                "%s": 1
            }
        }
        EOL, $material->sku, $missingSku);
        $skus = json_decode($jsonString, true);

        $data = app(GetBomService::class)
            ->setPayload($skus)
            ->exec();

        $this->assertEquals(
            collect([
                [
                    'sku' => $material->sku,
                    'checkSku' => $checkSku->sku,
                    'locations' => [$storageBox->location]
                ]
            ]),
            $data['foundSkus']
        );
        $this->assertEquals([$missingSku], $data['missingSkus']);
    }
}
