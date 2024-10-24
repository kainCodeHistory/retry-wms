<?php

namespace App\Repositories;

use App\Models\B2BStockLog;
use Illuminate\Support\Facades\DB;

class B2BStockLogRepository extends BaseRepository
{
    protected $model = B2BStockLog::class;
    public function getSkuList()
    {
        return $this->model::select('sku')->distinct()->get();
    }
    public function getRecordCount(string $sku, string $date)
    {
        $query = $this->model::where('working_day', '<=', $date)->where('sku', $sku);


        return $query->count();
    }

    public function getRecords(string $sku, string $date, int $limit, int $offset)
    {
        $query = $this->model::select()->where('working_day', '<=', $date)->where('sku', $sku)->orderBy('id', 'DESC')->take($limit);

        if ($offset > 0) {
            $query = $query->offset($offset);
        }

        return $query->get();
    }
    public function countInputs(string $transactionDate, string $eanSku)
    {
        $query = $this->model::where('working_day', $transactionDate)
        ->where('event','stock_input');
        if (!empty($eanSku)) {
            $query = $query->where(function ($q) use ($eanSku) {
                $q->where('sku', '=', $eanSku)
                    ->orWhere('ean', '=', $eanSku);
            });
        }

        return $query->count();
    }

    public function getInputs(string $transactionDate, string $eanSku, int $limit, int $offset)
    {
        $query = $this->model::leftjoin('materials', 'materials.sku', '=', 'b2b_stock_logs.sku')
            ->where('b2b_stock_logs.working_day', $transactionDate)
            ->where('b2b_stock_logs.event','stock_input');

        if (!empty($eanSku)) {
            $query = $query->where(function ($q) use ($eanSku) {
                $q->where('materials.sku', '=', $eanSku)
                    ->orWhere('materials.ean', '=', $eanSku);
            });
        }

        $query = $query->select('b2b_stock_logs.event_key', 'b2b_stock_logs.sku', 'materials.ean', 'materials.display_name', 'b2b_stock_logs.quantity', 'b2b_stock_logs.user_name', DB::raw("DATE_FORMAT(b2b_stock_logs.created_at, '%Y-%m-%d %H:%i') As bound_at"))
            ->orderBy('b2b_stock_logs.created_at', 'asc')
            ->take($limit);

        if ($offset > 0) {
            $query = $query->offset($offset);
        }

        return $query->get();
    }
}
