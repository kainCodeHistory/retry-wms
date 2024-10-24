<?php

namespace App\Http\Controllers;

use App\Services\Auth\UserLoginService;
use App\Services\Auth\UserLogoutService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        return app(UserLoginService::class)
            ->setPayload($request->all())
            ->exec();
    }

    public function logout(Request $request)
    {
        return app(UserLogoutService::class)
            ->exec();
    }
}
