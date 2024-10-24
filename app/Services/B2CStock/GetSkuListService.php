<?php

namespace App\Services\B2CStock;

use App\Repositories\B2CStockLogRepository;
use App\Services\AppService;

class GetSkuListService extends AppService
{

    private $b2cStockLogRepository;

    public function __construct(B2CStockLogRepository $b2cStockLogRepository)
    {
        $this->b2cStockLogRepository = $b2cStockLogRepository;
    }


    public function exec()
    {
        $skuList = $this->b2cStockLogRepository->getSkuList();

        return ['data' => $skuList->pluck('sku')];
    }
}
