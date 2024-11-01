<?php

namespace App\Services\B2B;

use App\Repositories\B2B5FInputRepository;
use App\Services\AppService;

class GetB2BInputListService extends AppService
{
    protected $payload;

    protected $b2b5fInputRepository;

    public function __construct(B2B5FInputRepository $b2b5fInputRepository)
    {
        $this->b2b5fInputRepository = $b2b5fInputRepository;
    }

    public function setPayload(array $payload)
    {
        $this->payload = $payload;
        return $this;
    }

    public function exec()
    {
        $page = $this->payload['page'] ?? 1;
        $perPage = $this->payload['perPage'] ?? 50;

        $total = $this->b2b5fInputRepository->countInputs(
            $this->payload['manufacturingDate'],
            $this->payload['eanSku'] ?? ""
        );

        $lastPage = ceil($total / $perPage);

        $inputs = $this->b2b5fInputRepository->getInputs(
            $this->payload['manufacturingDate'],
            $this->payload['eanSku'] ?? "",
            $page * $perPage,
            ($page - 1) * $perPage
        );

        return [
            'inputs' => $inputs,
            'paginator' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'lastPage' => $lastPage,
            ]
        ];
    }
}
