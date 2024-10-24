<?php

namespace Tests\Feature\Services\Auth;

use App\Services\Auth\UserLoginService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Tests\GeneralTestCase;

class UserLoginServiceTest extends GeneralTestCase
{
    public function test_it_can_throw_validation_exception_while_user_login()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        app(UserLoginService::class)
            ->setPayload([])
            ->exec();
    }

    public function test_user_can_login()
    {
        $user = $this->createUser([
            'email' => 'wmsuser@evolutivelabs.com',
            'password' => Hash::make('rhino5hield')
        ]);

        $jsonstring = sprintf(<<<'EOL'
        {
            "account": "%s",
            "password": "%s"
        }
        EOL, $user->email, 'rhino5hield');
        $payload = json_decode($jsonstring, true);

        app(UserLoginService::class)
            ->setPayload($payload)
            ->exec();

        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }
}
