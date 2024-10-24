<?php

namespace Tests\Feature\Services\B2B;

use App\Services\B2B\AddB2BInputService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\GeneralTestCase;

class AddB2BInputServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(AddB2BInputService::class)
            ->setPayload([])
            ->exec();
    }

    public function test_it_can_throw_sku_not_found_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(AddB2BInputService::class)
            ->setPayload([
                'box' => 'L20230324001',
                'sku' => 'NX01K',
                'quantity' => 200
            ])
            ->exec();
    }

    public function test_it_can_add_b2b_input()
    {
        $now = Carbon::now('Asia/Taipei');

        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
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

        app(AddB2BInputService::class)
            ->setPayload([
                'box' => 'L20230324001',
                'sku' => $material->sku,
                'quantity' => 200
            ])
            ->exec();

        $this->assertDatabaseHas(
            'b2b_inputs',
            [
                'transaction_date' => $now->format('Y-m-d'),
                'box' => 'L20230324001',
                'material_id' => $material->id,
                'material_sku' => $material->sku,
                'product_title' => $material->display_name,
                'quantity' => 200,
                'user_id' => $user->id,
                'user' => $user->name
            ]
        );
    }
}
