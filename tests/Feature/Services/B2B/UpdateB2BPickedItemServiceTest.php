<?php

namespace Tests\Feature\Services\B2B;

use App\Models\B2BStockLog;
use App\Services\B2B\FixB2BPickedItemService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\GeneralTestCase;

class UpdateB2BPickedItemServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(FixB2BPickedItemService::class)
            ->setPayload(["id" => '', 'fixed_quantity' => 0])
            ->exec();
    }
    public function test_it_can_throw_validation_without_id_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(FixB2BPickedItemService::class)
            ->setPayload(['fixed_quantity' => 1])
            ->exec();
    }
    public function test_it_can_throw_validation_quantity_with_0_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(FixB2BPickedItemService::class)
            ->setPayload(["id" => '1', 'fixed_quantity' => 0])
            ->exec();
    }
    public function test_it_can_throw_validation_without_find_id_detail_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);
        \App\Models\B2BPickedItem::create([
            'id' => "1",
            'batch_key' => "1",
            'picked_date' => "2023-03-01",
            'sku' => "NX01K",
            'total_list' => "testTotal",
            'order_number' => "#test",
            'quantity' => 1,
            'employee_no' => "99997"
        ]);


        app(FixB2BPickedItemService::class)
            ->setPayload(["id" => "2", "fixed_quantity" => 1])
            ->exec();
    }
    public function test_it_can_fix_picked_reocrd()
    {
        \App\Models\B2BPickedItem::create([
            'id' => "1",
            'batch_key' => "1",
            'picked_date' => "2023-03-01",
            'sku' => "NX01K",
            'total_list' => "testTotal",
            'order_number' => "#test",
            'quantity' => 1,
            'employee_no' => "99997"
        ]);
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


        app(FixB2BPickedItemService::class)
            ->setPayload(['id' => '1', 'fixed_quantity' => 1])
            ->exec();

        $this->assertDatabaseHas(
            'b2b_picked_items',
            [
                'id' => 1,
                'fixed_quantity' => 1
            ]
        );
        $this->assertDatabaseHas(
            'b2b_stock',
            [
                'sku' => 'NX01K',
                'total_quantity' => 301
            ]
        );
        $this->assertDatabaseHas(
            'b2b_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => 'NX01K',
                'quantity' => 1,
                'balance' => 301,
                'event' => B2BStockLog::ITEM_RETURN
            ]
        );
    }
}
