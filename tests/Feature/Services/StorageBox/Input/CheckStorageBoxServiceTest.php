<?php

namespace Tests\Feature\Services\StorageBox\Input;

use App\Services\StorageBox\Input\BindMaterialService;
use App\Services\StorageBox\Input\CheckStorageBoxService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use Tests\GeneralTestCase;

class CheckStorageBoxServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(CheckStorageBoxService::class)
            ->setStorageBox('')
            ->exec();
    }

    public function test_it_can_throw_validation_exception_while_prefix_error()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(CheckStorageBoxService::class)
            ->setStorageBox('F00000001')
            ->exec();
    }

    public function test_it_can_throw_validation_exception_while_storage_box_len_error()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(CheckStorageBoxService::class)
            ->setStorageBox('K00000001')
            ->exec();
    }

    public function test_it_can_create_new_storage_box()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@tests.com'
        ]);

        Auth::loginUsingId($user->id);

        $factory = $this->createFactory($this->faker->company);
        $warehouse = $this->createPickingArea($factory);


        app(CheckStorageBoxService::class)
        ->setStorageBox('G00000001')
        ->exec();



        $this->assertDatabaseHas(
            'storage_boxes',
            [
                'barcode' => 'G00000001',
                'prefix' => 'G'
            ]
        );

    }
}
