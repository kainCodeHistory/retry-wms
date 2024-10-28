<?php

namespace Tests\Feature\Services\B2BStock;

use App\Models\B2BStockLog;
use App\Services\B2BStock\UpdateB2BStockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Tests\GeneralTestCase;

class UpdateB2BStockServiceTest extends GeneralTestCase
{
    public function test_it_can_handle_input_event_01()
    {
        $workingDay = Carbon::now();
        $sku = 'MA88';
        $quantity = $this->faker->randomNumber(2);
        $storageBox = $this->faker->numerify('A#####');
        $note = $this->faker->word();
        $user = $this->createUser([
            'name' => 'user',
            'email' => 'user@tests.com'
        ]);
        Auth::loginUsingId($user->id);
        $event = $this->faker->randomElement([
            B2BStockLog::STOCK_INPUT,
            B2BStockLog::TRANSFER_INPUT,
            B2BStockLog::ITEM_RETURN
        ]);

        $payload = [
            'items' => [
                [
                    'sku' => $sku,
                    'event' => $event,
                    'eventKey' => $storageBox,
                    'quantity' => $quantity,
                    'note' => $note
                ]
            ]
        ];

        app(UpdateB2BStockService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseHas(
            'b2b_stock',
            [
                'sku' => $sku,
                'total_quantity' => $quantity
            ]
        );

        $this->assertDatabaseHas(
            'b2b_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $sku,
                'quantity' => $quantity,
                'balance' => $quantity,
                'event' => $event,
                'event_key' => $storageBox,
                'note' => $note,
                'user_name' => $user->name
            ]
        );
    }

    public function test_it_can_handle_input_event_02()
    {
        $workingDay = Carbon::now();
        $sku = 'MA88';
        $quantity = $this->faker->randomNumber(2);
        $storageBox = $this->faker->numerify('A#####');
        $note = $this->faker->word();
        $event = $this->faker->randomElement([
            B2BStockLog::STOCK_INPUT,
            B2BStockLog::TRANSFER_INPUT,
            B2BStockLog::ITEM_RETURN
        ]);
        $user = $this->createUser([
            'name' => 'user',
            'email' => 'user@tests.com'
        ]);
        Auth::loginUsingId($user->id);

        $stock = \App\Models\B2BStock::create([
            'sku' => $sku,
            'total_quantity' => $this->faker->randomNumber(3)
        ]);

        $payload = [
            'items' => [
                [
                    'sku' => $sku,
                    'event' => $event,
                    'eventKey' => $storageBox,
                    'quantity' => $quantity,
                    'note' => $note
                ]
            ]
        ];

        $subtotal = $stock->total_quantity + $quantity;

        app(UpdateB2BStockService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseHas(
            'b2b_stock',
            [
                'sku' => $sku,
                'total_quantity' => $subtotal
            ]
        );

        $this->assertDatabaseHas(
            'b2b_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $sku,
                'quantity' => $quantity,
                'balance' => $subtotal,
                'event' => $event,
                'event_key' => $storageBox,
                'note' => $note,
                'user_name' => $user->name
            ]
        );
    }

    public function test_it_can_handle_input_event_03()
    {
        $workingDay = Carbon::now();
        $sku = 'MA88';
        $quantity = $this->faker->randomNumber(2);
        $storageBox = $this->faker->numerify('A#####');
        $note = $this->faker->word();
        $user = $this->createUser([
            'name' => 'user',
            'email' => 'user@tests.com'
        ]);
        Auth::loginUsingId($user->id);

        $stock = \App\Models\B2BStock::create([
            'sku' => $sku,
            'total_quantity' => $this->faker->randomNumber(3)
        ]);

        $payload = [
            'items' => [
                [
                    'sku' => $sku,
                    'event' => B2BStockLog::ADJUST,
                    'eventKey' => $storageBox,
                    'quantity' => $quantity,
                    'note' => $note
                ]
            ]
        ];

        $subtotal = $stock->total_quantity + $quantity;

        app(UpdateB2BStockService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseHas(
            'b2b_stock',
            [
                'sku' => $sku,
                'total_quantity' => $subtotal
            ]
        );

        $this->assertDatabaseHas(
            'b2b_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $sku,
                'quantity' => $quantity,
                'balance' => $subtotal,
                'event' => B2BStockLog::ADJUST,
                'event_key' => $storageBox,
                'note' => $note,
                'user_name' => $user->name
            ]
        );
    }

    public function test_it_can_handle_output_event_01()
    {
        $workingDay = Carbon::now();
        $sku = 'MA88';
        $quantity = $this->faker->randomNumber(2);
        $storageBox = $this->faker->numerify('A#####');
        $note = $this->faker->word();
        $event = $this->faker->randomElement([
            B2BStockLog::ITEM_PICK,
            B2BStockLog::ITEM_EOL,
            B2BStockLog::TRANSFER_OUTPUT
        ]);
        $user = $this->createUser([
            'name' => 'user',
            'email' => 'user@tests.com'
        ]);
        Auth::loginUsingId($user->id);

        $stock = \App\Models\B2BStock::create([
            'sku' => $sku,
            'total_quantity' => $this->faker->randomNumber(3)
        ]);

        $payload = [
            'items' => [
                [
                    'sku' => $sku,
                    'event' => $event,
                    'eventKey' => $storageBox,
                    'quantity' => $quantity,
                    'note' => $note
                ]
            ]
        ];

        $subtotal = $stock->total_quantity - $quantity;

        app(UpdateB2BStockService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseHas(
            'b2b_stock',
            [
                'sku' => $sku,
                'total_quantity' => $subtotal
            ]
        );

        $this->assertDatabaseHas(
            'b2b_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $sku,
                'quantity' => $quantity,
                'balance' => $subtotal,
                'event' => $event,
                'event_key' => $storageBox,
                'note' => $note,
                'user_name' => $user->name
            ]
        );
    }

    public function test_it_can_handle_output_event_02()
    {
        $workingDay = Carbon::now();
        $sku = 'MA88';
        $quantity = $this->faker->randomNumber(2);
        $storageBox = $this->faker->numerify('A#####');
        $note = $this->faker->word();
        $event = $this->faker->randomElement([
            B2BStockLog::ITEM_PICK,
            B2BStockLog::ITEM_EOL,
            B2BStockLog::TRANSFER_OUTPUT
        ]);
        $user = $this->createUser([
            'name' => 'user',
            'email' => 'user@tests.com'
        ]);
        Auth::loginUsingId($user->id);

        $payload = [
            'items' => [
                [
                    'sku' => $sku,
                    'event' => $event,
                    'eventKey' => $storageBox,
                    'quantity' => $quantity,
                    'note' => $note
                ]
            ]
        ];

        app(UpdateB2BStockService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseHas(
            'b2b_stock',
            [
                'sku' => $sku,
                'total_quantity' => -$quantity
            ]
        );

        $this->assertDatabaseHas(
            'b2b_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $sku,
                'quantity' => $quantity,
                'balance' => -$quantity,
                'event' => $event,
                'event_key' => $storageBox,
                'note' => $note,
                'user_name' => $user->name
            ]
        );
    }

    public function test_it_can_handle_output_event_03()
    {
        $workingDay = Carbon::now();
        $sku = 'MA88';
        $quantity = 100;
        $storageBox = $this->faker->numerify('A#####');
        $note = $this->faker->word();
        $event = $this->faker->randomElement([
            B2BStockLog::ITEM_PICK,
            B2BStockLog::ITEM_EOL,
            B2BStockLog::TRANSFER_OUTPUT
        ]);
        $user = $this->createUser([
            'name' => 'user',
            'email' => 'user@tests.com'
        ]);
        Auth::loginUsingId($user->id);

        $stock = \App\Models\B2BStock::create([
            'sku' => $sku,
            'total_quantity' => 80
        ]);

        $payload = [
            'items' => [
                [
                    'sku' => $sku,
                    'event' => $event,
                    'eventKey' => $storageBox,
                    'quantity' => $quantity,
                    'note' => $note
                ]
            ]
        ];

        $subtotal = $stock->total_quantity - $quantity;


        app(UpdateB2BStockService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseHas(
            'b2b_stock',
            [
                'sku' => $sku,
                'total_quantity' => $subtotal
            ]
        );

        $this->assertDatabaseHas(
            'b2b_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $sku,
                'quantity' => $quantity,
                'balance' => $subtotal,
                'event' => $event,
                'event_key' => $storageBox,
                'note' => $note,
                'user_name' => $user->name
            ]
        );
    }

    public function test_it_can_handle_output_event_04()
    {
        $workingDay = Carbon::now();
        $sku = 'MA88';
        $quantity = $this->faker->randomNumber(2);
        $storageBox = $this->faker->numerify('A#####');
        $note = $this->faker->word();
        $user = $this->createUser([
            'name' => 'user',
            'email' => 'user@tests.com'
        ]);
        Auth::loginUsingId($user->id);

        $stock = \App\Models\B2BStock::create([
            'sku' => $sku,
            'total_quantity' => $this->faker->randomNumber(3)
        ]);

        $payload = [
            'items' => [
                [
                    'sku' => $sku,
                    'event' => B2BStockLog::ADJUST,
                    'eventKey' => $storageBox,
                    'quantity' => -$quantity,
                    'note' => $note
                ]
            ]
        ];

        $subtotal = $stock->total_quantity - $quantity;

        app(UpdateB2BStockService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertDatabaseHas(
            'b2b_stock',
            [
                'sku' => $sku,
                'total_quantity' => $subtotal
            ]
        );

        $this->assertDatabaseHas(
            'b2b_stock_logs',
            [
                'working_day' => $workingDay->format('Y-m-d'),
                'sku' => $sku,
                'quantity' => -$quantity,
                'balance' => $subtotal,
                'event' => B2BStockLog::ADJUST,
                'event_key' => $storageBox,
                'note' => $note,
                'user_name' => $user->name
            ]
        );
    }

   
}
