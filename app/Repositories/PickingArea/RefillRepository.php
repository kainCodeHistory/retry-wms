<?php

namespace App\Repositories\PickingArea;

use App\Models\PickingArea\Refill;
use App\Repositories\BaseRepository;

class RefillRepository extends BaseRepository
{
    protected $model = Refill::class;

    public function getRefillRecordsByLocation(string $location)
    {
        return $this->model::where('location', $location)->whereIn('status', ['pending', 'processing'])->get();
    }

    public function getPendingStorageBox(string $type)
    {
        $query = Refill::query();
        return $this->model::where('status' ,'pending')
            ->where('fill_type', $type)
            ->orderBy('repl_warehouse_id')
            ->orderBy('repl_location')
            ->get();
    }

    public function getLocationByStorageBox(string $storageBox, string $status)
    {
        return $this->model::where(function($query) use ($storageBox, $status) {
            $query->where('fill_type', 'replace')->where('repl_storage_box', $storageBox)->where('status', $status);
        })->get();
    }

    public function getProcessingStorageBox(string $type)
    {
        $query = Refill::query();
        return $this->model::where('status' ,'!=','completed')
            ->where('status' ,'!=','aborted')
            ->where('fill_type', $type)
            ->orderBy('repl_warehouse_id')
            ->orderBy('repl_location')
            ->get();
    }


}
