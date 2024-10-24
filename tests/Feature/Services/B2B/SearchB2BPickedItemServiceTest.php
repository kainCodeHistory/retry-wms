<?php

namespace Tests\Feature\Services\B2B;

use App\Services\B2B\SearchB2BPickedItemsService;
use Illuminate\Validation\ValidationException;
use Tests\GeneralTestCase;

class SearchB2BPickedItemServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        $payload =  [
            "start_date" => '',
            "end_date" => '',
            "sku" => ''
        ];
        app(SearchB2BPickedItemsService::class)
            ->setPayload($payload)
            ->exec();
    }

    public function test_it_can_throw_sku_not_found_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        $payload = [
            "start_date" => '2023-03-01',
            "end_date" => '2023-03-02',
            "sku" => 'NX01K'
        ];
        app(SearchB2BPickedItemsService::class)
            ->setPayload($payload)
            ->exec();
    }

    public function test_it_can_throw_date_error_exception()
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

        $payload = [
            "start_date" => '2023-03-02',
            "end_date" => '2023-03-01',
            "sku" => 'NX01K'
        ];
        app(SearchB2BPickedItemsService::class)
            ->setPayload($payload)
            ->exec();
    }

    public function test_it_can_throw_date_over_one_month_exception()
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

        $payload = [
            "start_date" => '2023-03-01',
            "end_date" => '2023-05-02',
            "sku" => 'NX01K'
        ];
        app(SearchB2BPickedItemsService::class)
            ->setPayload($payload)
            ->exec();
    }

    public function test_it_can_throw_no_sku_picked_data_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);
        \App\Models\Material::create([
            'sku' => 'NX01K',
            'display_name' => 'Button All In 按鈕',
            'full_name' => 'Button All In 按鈕',
            'check_sku' => 'NX01K',
            'ean' => '4710227230383',
            'upc' => '888543003005'
        ]);

        \App\Models\B2BPickedItem::create([
            'batch_key' => "1",
            'picked_date' => "2023-01-01",
            'sku' => "NX01K",
            'total_list' => "testTotal",
            'order_number' => "#test",
            'quantity' => 1,
            'employee_no' => "99997"
        ]);

        $payload = [
            "start_date" => '2023-03-01',
            "end_date" => '2023-03-02',
            "sku" => 'NX01K'
        ];
        app(SearchB2BPickedItemsService::class)
            ->setPayload($payload)
            ->exec();
    }

    public function test_it_can_get_sku_picked_data()
    {
        \App\Models\Material::create([
            'sku' => 'NX01K',
            'display_name' => 'Button All In 按鈕',
            'full_name' => 'Button All In 按鈕',
            'check_sku' => 'NX01K',
            'ean' => '4710227230383',
            'upc' => '888543003005'
        ]);

        \App\Models\B2BPickedItem::create([
            'batch_key' => "1",
            'picked_date' => "2023-03-01",
            'sku' => "NX01K",
            'total_list' => "testTotal",
            'order_number' => "#test",
            'quantity' => 1,
            'employee_no' => "99997"
        ]);

        $payload = [
            "start_date" => '2023-03-01',
            "end_date" => '2023-03-02',
            "sku" => 'NX01K'
        ];
        app(SearchB2BPickedItemsService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseHas(
            'b2b_picked_items',
            [
                'batch_key' => "1",
                'picked_date' => "2023-03-01",
                'sku' => "NX01K",
                'total_list' => "testTotal",
                'order_number' => "#test",
                'quantity' => 1,
                'employee_no' => "99997"
            ]
        );
    }
}
