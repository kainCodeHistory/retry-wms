<?php

namespace Tests\Feature\Services\Material;

use App\Services\Material\GetMaterialService;

use Tests\GeneralTestCase;

class GetMaterialServiceTest extends GeneralTestCase
{
    public function test_it_can_get_material()
    {
        $checkMaterial = $this->createMaterial('107MB18501C', '');

        $data = app(GetMaterialService::class)
            ->setSku($checkMaterial->sku)
            ->exec();

        $this->assertEquals(
            [
                'sku' => $checkMaterial->sku,
                'name' => $checkMaterial->display_name,
                'ean' =>''
            ],
            $data
        );
    }

    public function test_it_cannot_get_material()
    {
        $sku = $this->faker->bothify('???###??');
        $data = app(GetMaterialService::class)
            ->setSku($sku)
            ->exec();

        $this->assertEquals(
            [
                'sku' => '',
                'name' => '無此 SKU (' . $sku . ')。',
                'ean' => ''
            ],
            $data
        );
    }
}
