<?php

namespace App\Repositories;

use App\Models\B2BInput;
use Illuminate\Support\Facades\DB;

class B2BInputRepository extends BaseRepository
{
    protected $model = B2BInput::class;

    public function countInputs(string $transactionDate, string $eanSku)
    {
        $query = $this->model::where('transaction_date', $transactionDate);
        if (!empty($eanSku)) {
            $query = $query->where(function ($q) use ($eanSku) {
                $q->where('material_sku', '=', $eanSku)
                    ->orWhere('ean', '=', $eanSku);
            });
        }

        return $query->count();
    }

    public function getInputs(string $transactionDate, string $eanSku, int $limit, int $offset)
    {
        $query = $this->model::where('transaction_date', $transactionDate);
        if (!empty($eanSku)) {
            $query = $query->where(function ($q) use ($eanSku) {
                $q->where('material_sku', '=', $eanSku)
                    ->orWhere('ean', '=', $eanSku);
            });
        }

        $query = $query->select('id', 'box', 'material_sku', 'ean', 'product_title', 'quantity', 'user', DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') As bound_at"))
            ->orderBy('created_at', 'asc')
            ->take($limit);

        if ($offset > 0) {
            $query = $query->offset($offset);
        }

        return $query->get();
    }
}
