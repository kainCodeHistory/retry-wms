<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionRepository extends BaseRepository
{
    protected $model = Transaction::class;

    public function getResetStorageBoxes(string $checkDate, string $checkSku, string $location)
    {
        return $this->model::where('material_sku', $checkSku)
            ->where('location', $location)
            ->whereDate('created_at', '=', $checkDate)
            ->where('event', Transaction::STORAGE_BOX_RESET)
            ->get();
    }

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

    public function getLogs(string $transDate = '', string $location = '', string $sku = '', array $storageBoxes = [],  array $events = [], array $sort = [])
    {
        $query = $this->model::whereRaw('1=1');

        if (!empty($transDate)) {
            $query = $query->where(DB::raw("DATE_FORMAT(`created_at`, '%Y-%m-%d')"), '=', $transDate);
        }

        if (!empty($location)) {
            $query = $query->where('location', $location);
        }

        if (!empty($sku)) {
            $query = $query->where('material_sku', $sku);
        }

        if (count($storageBoxes) > 0) {
            $query = $query->whereIn('storage_box', $storageBoxes);
        }

        if (count($events) > 0) {
            $query = $query->whereIn('event', $events);
        }

        foreach ($sort as $column => $order) {
            $query = $query->orderBy($column, $order);
        }

        return $query->get();
    }
}
