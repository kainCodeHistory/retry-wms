<?php

namespace Tests\Feature\Services\B2B;

use App\Models\B2BStockLog;
use App\Services\B2B\AddB2BInputService;
use App\Services\B2B\B2BPickedItemService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\GeneralTestCase;

class PickedItemServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(B2BPickedItemService::class)
            ->setPayload([])
            ->exec();
    }

    public function test_it_can_throw_sku_not_found_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(B2BPickedItemService::class)
            ->setPayload([
                'sku' => 'NX01K',
                'quantity' => 200
            ])
            ->exec();
    }

    public function test_it_can_throw_employee_not_found_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);
        $material = \App\Models\Material::create([
            'sku' => 'NX01K',
            'display_name' => 'Button All In 按鈕',
            'full_name' => 'Button All In 按鈕',
            'check_sku' => 'NX01K',
            'ean' => '4710227230383',
            'upc' => '888543003005'
        ]);

        app(B2BPickedItemService::class)
            ->setPayload([
                'sku' => 'NX01K',
                'quantity' => 200
            ])
            ->exec();
    }

    public function test_it_can_throw_orderList_not_found_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);
        $material = \App\Models\Material::create([
            'sku' => 'NX01K',
            'display_name' => 'Button All In 按鈕',
            'full_name' => 'Button All In 按鈕',
            'check_sku' => 'NX01K',
            'ean' => '4710227230383',
            'upc' => '888543003005'
        ]);

        app(B2BPickedItemService::class)
            ->setPayload([
                'sku' => 'NX01K',
                'employee'=>'999993',
                'quantity' => 200
            ])
            ->exec();
    }


    public function test_it_can_add_b2b_picked_item_record()
    {
        $now = Carbon::now('Asia/Taipei');



        $material = \App\Models\Material::create([
            'sku' => 'NX01K',
            'display_name' => 'Button All In 按鈕',
            'full_name' => 'Button All In 按鈕',
            'check_sku' => 'NX01K',
            'ean' => '4710227230383',
            'upc' => '888543003005'
        ]);
        $workingDay = Carbon::now();
        $this->createB2BStock($material->sku, 300);
        $this->createB2BStockLog($material->sku, 300, 300, $workingDay);

        app(B2BPickedItemService::class)
            ->setPayload([
                'sku' => $material->sku,
                'employee'=>'9999993',
                'orderList'=>'test',
                'quantity' => 200
            ])
            ->exec();

        $this->assertDatabaseHas(
            'b2b_picked_items',
            [
                'picked_date' => $now->format('Y-m-d'),
                'sku' => $material->sku,
                'quantity' => 200,
                'employee_no' => '9999993',
                'total_list' => 'test'
            ]
        );
        $this->assertDatabaseHas(
            'b2b_stock',
            [
                'sku' => $material->sku,
                'total_quantity' => 100
            ]
        );
        $this->assertDatabaseHas(
            'b2b_stock_logs',
            [
                'sku' => $material->sku,
                'quantity' => 200,
                'balance'=> 100,
                'event'=>B2BStockLog::ITEM_PICK

            ]
        );
    }


}
