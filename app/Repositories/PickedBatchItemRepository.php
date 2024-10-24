<?php

namespace App\Repositories;

use App\Models\PickedBatchItem;

class PickedBatchItemRepository extends BaseRepository
{
    protected $model = PickedBatchItem::class;

    public function getStockOutItems()
    {
        return $this->model::where('is_debited', '=', 0)
            ->where('location', 'not like', 'AZ%')
            ->get();
    }
}
