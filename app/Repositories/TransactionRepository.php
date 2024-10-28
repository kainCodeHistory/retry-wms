<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionRepository extends BaseRepository
{
    protected $model = Transaction::class;

    public function getSkuBindTime(string $sku)
    {
        return $this->model::select('transactions.material_sku', 'storage_box_items.material_name', 'transactions.storage_box', 'storage_box_items.created_at as storage_box_time')
            ->join('storage_box_items', function ($join) {
                $join->on('storage_box_items.storage_box', '=', 'transactions.storage_box')->on('storage_box_items.material_sku', '=', 'transactions.material_sku');
            })
            ->where(function ($query) {
                $query->where('transactions.event',  Transaction::ITEM_BOUND);
            })
            ->where('transactions.material_sku', $sku)
            ->get();
    }

    public function getSkuBindFirstLocationTime(string $storageBox, string $sku)
    {
        return $this->model::select('created_at as transactions_time')
            ->where(function ($query) {
                $query->where('location', 'like', 'B%')
                    ->orwhere('location', 'like', 'A%');
            })
            ->where(function ($query) {
                $query->where('event',  Transaction::STORAGE_BOX_INPUT);
            })
            ->where('storage_box', $storageBox)
            ->where('material_sku', $sku)
            ->get();
    }

    public function getPickingAreaTime(string $storageBox, string $sku)
    {
        return $this->model::select('transactions.created_at as picking_area_time')
            ->where('location', 'like', 'A%')
            ->where(function ($query) {
                $query->where('event',  Transaction::STORAGE_BOX_INPUT)
                    ->orWhere('event',  Transaction::ADJUST_LOCATION)
                    ->orWhere('event',  Transaction::REFILL_INPUT);
            })
            ->where('storage_box', $storageBox)
            ->where('material_sku', $sku)
            ->get();
    }

}
