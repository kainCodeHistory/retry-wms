<?php

namespace App\Services\B2BStock;

use App\Repositories\B2BStockLogRepository;
use App\Services\AppService;

class GetSkuListService extends AppService
{

    private $b2bStockLogRepository;

    public function __construct(B2BStockLogRepository $b2bStockLogRepository)
    {
        $this->b2bStockLogRepository = $b2bStockLogRepository;
    }


    public function exec()
    {
        $skuList = $this->b2bStockLogRepository->getSkuList();

        return ['data' => $skuList->pluck('sku')];
    }
}
