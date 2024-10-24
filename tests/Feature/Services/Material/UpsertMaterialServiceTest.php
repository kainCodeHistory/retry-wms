<?php

namespace Tests\Feature\Services\Material;

use App\Services\Material\UpsertMaterialService;;
use Illuminate\Validation\ValidationException;

use Tests\GeneralTestCase;

class UpsertMaterialServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_exception_while_create_material()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(UpsertMaterialService::class)
            ->setPayload([])
            ->exec();
    }

    public function test_it_can_upsert_material()
    {
        $jsonString = file_get_contents(base_path('tests/Stub/material.json'));
        $material = json_decode($jsonString, true);

        app(UpsertMaterialService::class)
            ->setPayload($material)
            ->exec();

        $this->assertDatabaseHas('materials', [
            'sku' => $material['sku'],
            'display_name' => $material['name'],
            'full_name' => $material['name']
        ]);

        $checkSku = 'A0-CGN01086C0';
        $material['check_sku'] = $checkSku;
        app(UpsertMaterialService::class)
            ->setPayload($material)
            ->exec();

        $this->assertDatabaseHas('materials', [
            'sku' => $material['sku'],
            'check_sku' => $checkSku
        ]);
    }
}
