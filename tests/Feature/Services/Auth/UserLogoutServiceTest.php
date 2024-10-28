<?php

namespace Tests\Feature\Services\Auth;

use App\Services\Auth\UserLogoutService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Tests\GeneralTestCase;

class UserLogoutServiceTest extends GeneralTestCase
{
    public function test_user_can_logout()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@tests.com',
            'password' => Hash::make('rhino5hield')
        ]);

        Auth::loginUsingId($user->id);

        app(UserLogoutService::class)
            ->setPayload([])
            ->exec();

        $this->assertFalse(Auth::check());
    }
}
