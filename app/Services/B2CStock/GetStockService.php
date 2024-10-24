<?php

namespace App\Services\B2CStock;

use App\Repositories\B2CStockRepository;
use App\Services\AppService;

class GetStockService extends AppService
{
    /**
     * eanSku
     * @var string
     */
    private $eanSku;

    private $b2cStockRepository;

    public function __construct(B2CStockRepository $b2cStockRepository)
    {
        $this->b2cStockRepository = $b2cStockRepository;
    }

    public function setEanSku(string $eanSku)
    {
        $this->eanSku = $eanSku;
        return $this;
    }

    public function exec()
    {
        $stock = $this->b2cStockRepository->getStock($this->eanSku);

        if (is_null($stock)) {
            return null;
        } else {
            return [
                'sku' => $stock->sku,
                'product_title' => $stock->display_name,
                'current_quantity' => $stock->total_quantity
            ];
        }
    }
}
