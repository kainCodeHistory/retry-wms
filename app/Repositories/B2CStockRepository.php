<?php

namespace App\Repositories;

use App\Models\B2CStock;

class B2CStockRepository extends BaseRepository
{
    protected $model = B2CStock::class;

    public function getStock(string $eanSku)
    {
        return $this->model::select('materials.sku', 'materials.display_name', 'b2c_stock.total_quantity')
            ->join('materials', 'b2c_stock.sku', '=', 'materials.sku')
            ->where(function ($query) use ($eanSku) {
                $query->where('materials.sku', '=', $eanSku)
                    ->orWhere('materials.ean', '=', $eanSku);
            })
            ->first();
    }
}
