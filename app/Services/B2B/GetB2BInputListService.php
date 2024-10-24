<?php

namespace App\Services\B2B;

use App\Repositories\B2BStockLogRepository;
use App\Services\AppService;

class GetB2BInputListService extends AppService
{
    /**
     * payload
     * @var array
     */
    private $payload;

    private $b2bStockLogRepository;

    public function __construct(B2BStockLogRepository $b2bStockLogRepository)
    {
        $this->b2bStockLogRepository = $b2bStockLogRepository;
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

        $total = $this->b2bStockLogRepository->countInputs(
            $this->payload['transactionDate'],
            $this->payload['eanSku'] ?? ""
        );

        $lastPage = ceil($total / $perPage);

        $inputs = $this->b2bStockLogRepository->getInputs(
            $this->payload['transactionDate'],
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
