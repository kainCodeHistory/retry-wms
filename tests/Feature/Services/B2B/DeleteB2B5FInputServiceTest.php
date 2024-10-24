<?php

namespace Tests\Feature\Services\B2B;

use App\Services\B2B\DeleteB2B5FInputService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\GeneralTestCase;

class DeleteB2B5FInputServiceTest extends GeneralTestCase
{
    public function test_it_can_delete_b2b_5f_input()
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

        $result = app(DeleteB2B5FInputService::class)
            ->setInputId($input->id)
            ->exec();

        $this->assertEquals(0, $result);
        $this->assertDatabaseHas(
            'b2b_5f_inputs',
            [
                'id' => $input->id,
                'is_deleted' => 1
            ]
        );
    }
}
