<?php

namespace App\Services\Auth;

use App\Services\AppService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Session;

class UserLogoutService extends AppService
{
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
        try {
            Auth::logout();
            Session::flush();
        } catch (\Illuminate\Database\QueryException $ex) {
            throw ValidationException::withMessages(['user' => $ex->getMessage()]);
        }

        return response()->json([
            'ok' => true
        ]);
    }
}
