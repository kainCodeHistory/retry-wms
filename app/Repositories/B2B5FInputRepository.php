<?php

namespace App\Repositories;

use App\Models\B2B5FInput;

class B2B5FInputRepository extends BaseRepository
{
    protected $model = B2B5FInput::class;

    public function countInputs(string $manufacturingDate, string $eanSku)
    {
        $query = $this->model::where('manufacturing_date', $manufacturingDate);
        if (!empty($eanSku)) {
            $query = $query->where(function ($q) use ($eanSku) {
                $q->where('material_sku', '=', $eanSku)
                    ->orWhere('ean', '=', $eanSku);
            });
        }

        return $query->count();
    }

    public function getInputs(string $manufacturingDate, string $eanSku, int $limit, int $offset)
    {
        $query = $this->model::where('manufacturing_date', $manufacturingDate);
        if (!empty($eanSku)) {
            $query = $query->where(function ($q) use ($eanSku) {
                $q->where('material_sku', '=', $eanSku)
                    ->orWhere('ean', '=', $eanSku);
            });
        }

        $query = $query->select('id', 'item_number', 'material_sku', 'ean', 'product_title', 'quantity', 'note', 'is_deleted')
            ->orderBy('item_number', 'asc')
            ->take($limit);

        if ($offset > 0) {
            $query = $query->offset($offset);
        }

        return $query->get();
    }
}
