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
}
