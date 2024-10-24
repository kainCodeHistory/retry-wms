<?php

namespace App\Services;

use Illuminate\Contracts\Validation\Factory;

abstract class AppService
{
    abstract public function exec();

    public function validate(array $inputs, array $rules,
                             array $messages = [], array $customAttributes = [])
    {
        return $this->getValidationFactory()->make(
            $inputs, $rules, $messages, $customAttributes
        )->validate();
    }

    protected function getValidationFactory()
    {
        return app(Factory::class);
    }
}
