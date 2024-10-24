<?php

namespace App\Services\B2B;

use App\Repositories\B2B5FInputRepository;
use App\Services\AppService;

class GetB2B5FInputService extends AppService
{
    protected $inputId;

    protected $b2b5FInputRepository;

    public function __construct(B2B5FInputRepository $b2b5FInputRepository)
    {
        $this->b2b5FInputRepository = $b2b5FInputRepository;
    }

    public function setInputId(int $inputId)
    {
        $this->inputId = $inputId;
        return $this;
    }

    public function exec()
    {
        return $this->b2b5FInputRepository->search([
            'id' => $this->inputId
        ])->first();
    }
}
