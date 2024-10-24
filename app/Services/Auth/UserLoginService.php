<?php

namespace App\Services\Auth;

use App\Services\AppService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserLoginService extends AppService {
    protected $payload;

    public function __construct()
    {

    }

    public function setPayload(array $payload)
    {
        $this->payload = $payload;
        return $this;
    }

    public function exec()
    {
        $this->validate($this->payload, [
            'account' => 'required|email|exists:users,email',
            'password' => 'required'
        ],[
            'account.required' => '帳號必須有值。',
            'account.email' => '帳號為 Email 格式。',
            'account.exists' => '帳號錯誤 (:input)。',
            'password.required' => '密碼必須有值。'
        ]);

        $credentails = [
            'email' => $this->payload['account'],
            'password' => $this->payload['password']
        ];


        if (Auth::attempt($credentails)) {
            //
        } else {
            throw ValidationException::withMessages(['password' => '密碼錯誤。']);
        }

        return response()->json([
            'ok' => true
        ]);
    }
}
