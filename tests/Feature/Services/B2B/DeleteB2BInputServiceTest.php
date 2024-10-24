<?php

namespace Tests\Feature\Services\B2B;

use App\Services\B2B\DeleteB2BInputService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Tests\GeneralTestCase;

class DeleteB2BInputServiceTest extends GeneralTestCase
{
    public function test_it_can_delete_b2b_input()
    {
        $user = $this->createUser([
            'name' => 'user',
            'email' => 'wms@evolutivelabs.com'
        ]);
        Auth::loginUsingId($user->id);

        $material = $this->createMaterial('MA88', 'MA88', [
            'display_name' => 'Type C-TypeC TPE充電線 黑色 2M',
            'ean' => '4715517677862'
        ]);

        $workingDay = Carbon::now()->format('Y-m-d');
        $input = \App\Models\B2BInput::create([
            'transaction_date' => $workingDay,
            'box' => sprintf("L%s001", $workingDay),
            'material_id' => $material->id,
            'material_sku' => $material->sku,
            'ean' => $material->ean,
            'product_title' => $material->display_name,
            'quantity' => 200,
            'user_id' => $user->id,
            'user' => $user->name
        ]);

        app(DeleteB2BInputService::class)
            ->setInputId($input->id)
            ->exec();

        $this->assertDatabaseMissing(
            'b2b_inputs',
            [
                'id' => $input->id
            ]
        );
    }
}
