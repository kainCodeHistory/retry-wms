<?php

namespace App\Services\B2B;

use App\Repositories\B2BInputRepository;
use App\Services\AppService;

class DeleteB2BInputService extends AppService
{
    /**
     * inputId
     * @var int
     */
    private $inputId;

    private $b2bInputRepository;

    public function __construct(B2BInputRepository $b2bInputRepository)
    {
        $this->b2bInputRepository = $b2bInputRepository;
    }

    public function setInputId(int $inputId)
    {
        $this->inputId = $inputId;
        return $this;
    }

    public function exec()
    {
        $this->b2bInputRepository->delete($this->inputId);

        return 0;
    }
}
