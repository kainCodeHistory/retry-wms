<?php

namespace Tests\Feature\Services\B2B;

use App\Services\B2B\UpsertB2B5FInputService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\GeneralTestCase;

class UpsertB2B5FInputServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(UpsertB2B5FInputService::class)
            ->setPayload([])
            ->exec();
    }

    public function test_it_can_throw_sku_not_found_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(UpsertB2B5FInputService::class)
            ->setPayload([
                'inputId' => 0,
                'manufacturingDate' => Carbon::now()->format('Y-m-d'),
                'sku' => 'NX01K',
                'quantity' => 200
            ])
            ->exec();
    }

    public function test_it_can_create_b2b_f5_input()
    {
        $now = Carbon::now('Asia/Taipei');

        $user = $this->createUser([
            'email' => 'wmsuser@tests.com',
            'password' => Hash::make('123456')
        ]);

        Auth::loginUsingId($user->id);

        $material = \App\Models\Material::create([
            'sku' => 'NX01K',
            'display_name' => 'Button All In 按鈕',
            'full_name' => 'Button All In 按鈕',
            'check_sku' => 'NX01K',
            'ean' => '4710227230383',
            'upc' => '888543003005'
        ]);

        $note = $this->faker->word();
        app(UpsertB2B5FInputService::class)
            ->setPayload([
                'inputId' => 0,
                'manufacturingDate' => $now->format('Y-m-d'),
                'sku' => $material->sku,
                'quantity' => 200,
                'note' => $note
            ])
            ->exec();

        $this->assertDatabaseHas(
            'b2b_5f_inputs',
            [
                'manufacturing_date' => $now->format('Y-m-d'),
                'material_id' => $material->id,
                'material_sku' => $material->sku,
                'product_title' => $material->display_name,
                'ean' => $material->ean,
                'quantity' => 200,
                'note' => $note,
                'user_id' => $user->id,
                'user_name' => $user->name
            ]
        );
    }

}
