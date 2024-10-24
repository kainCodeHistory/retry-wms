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

    public function test_it_can_update_b2b_f5_input()
    {
        $now = Carbon::now('Asia/Taipei');

        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
        ]);

        Auth::loginUsingId($user->id);

        $nxk = \App\Models\Material::create([
            'sku' => 'NX01K',
            'display_name' => 'Button All In 按鈕',
            'full_name' => 'Button All In 按鈕',
            'check_sku' => 'NX01K',
            'ean' => '4710227230383',
            'upc' => '888543003005'
        ]);

        $note = $this->faker->word();

        $input = \App\Models\B2B5FInput::create([
            'manufacturing_date' => $now->format('Y-m-d'),
            'material_id' => $nxk->id,
            'material_sku' => $nxk->sku,
            'product_title' => $nxk->display_name,
            'ean' => $nxk->ean,
            'quantity' => 200,
            'note' => $note,
            'user_id' => $user->id,
            'user_name' => $user->name
        ]);

        $nxe = \App\Models\Material::create([
            'sku' => 'NX0108604E',
            'display_name' => 'Mod NX for iPhone XS Max Rim-White 白色飾條',
            'full_name' => 'Mod NX for iPhone XS Max Rim-White 白色飾條',
            'check_sku' => 'NX0108604E',
            'ean' => '4710227233490',
            'upc' => '888543004996'
        ]);

        $note2 = $this->faker->word();
        app(UpsertB2B5FInputService::class)
            ->setPayload([
                'inputId' => $input->id,
                'manufacturingDate' => Carbon::now()->subDay()->format('Y-m-d'),
                'sku' => $nxe->sku,
                'quantity' => 100,
                'note' => $note2
            ])
            ->exec();

        $this->assertDatabaseHas(
            'b2b_5f_inputs',
            [
                'id' => $input->id,
                'manufacturing_date' => Carbon::now()->subDay()->format('Y-m-d'),
                'material_id' => $nxe->id,
                'material_sku' => $nxe->sku,
                'product_title' => $nxe->display_name,
                'ean' => $nxe->ean,
                'quantity' => 100,
                'note' => $note2,
                'user_id' => $user->id,
                'user_name' => $user->name
            ]
        );
    }
}
