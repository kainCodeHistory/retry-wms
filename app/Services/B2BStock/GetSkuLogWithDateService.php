<?php

namespace App\Services\B2BStock;

use App\Repositories\B2BStockLogRepository;
use App\Services\AppService;

class GetSkuLogWithDateService extends AppService
{

    private $b2bStockLogRepository;
    private $sku;
    private $payload;


    public function __construct(B2BStockLogRepository $b2bStockLogRepository)
    {
        $this->b2bStockLogRepository = $b2bStockLogRepository;
    }

    public function setSku(string $sku)
    {
        $this->sku = $sku;
        return $this;
    }

    public function setPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }

    public function exec()
    {
        $this->validate(
            $this->payload,
            [
                'start_date' => 'date_format:Y-m-d',
                'page' => 'numeric|min:1',
                'perPage' => 'numeric|min:1'
            ],
            [
                'perPage.min' => '數量必須大於 0',
                'page.min' => '數量必須大於 0'
            ]

        );
        $date = $this->payload['start_date'] ?? date('Y-m-d');
        $page = $this->payload['page'] ?? 1;
        $perPage = $this->payload['perPage'] ?? 50;
        //先查總共有幾筆記錄
        $total = $this->b2bStockLogRepository->getRecordCount($this->sku, $date);

        $lastPage = ceil($total / $perPage);

        $records = $this->b2bStockLogRepository->getRecords(
            $this->sku,
            $date,
            $perPage,
            ($page - 1) * $perPage
        );

        return [
            'data' => $records->toArray(),
            'paginator' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'lastPage' => $lastPage,
            ]
        ];
    }
}
