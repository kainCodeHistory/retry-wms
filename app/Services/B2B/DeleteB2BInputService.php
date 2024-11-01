<?php

namespace App\Services\B2B;

use App\Repositories\B2B5FInputRepository;
use App\Services\AppService;

class DeleteB2BInputService extends AppService
{
    protected $b2b5FInputRepository;

    protected $inputId;

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
        $this->b2b5FInputRepository->update(
            $this->inputId,
            [
                'is_deleted' => 1
            ]
        );

        return 0;
    }
}
