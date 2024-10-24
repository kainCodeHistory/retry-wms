<?php

namespace App\Repositories;

use App\Models\Inventory;
use App\Repositories\BaseRepository;
class InventoryRepository extends BaseRepository
{
    protected $model = Inventory::class;

    public function getPickingAreaInventory(string $location = '')
    {
        $query = $this->model::where('warehouse_id', 1)
            ->where('status', 'check_inventory');
        if (!empty($location)) {
            $query = $query->where('location', $location);
        }
        return $query->orderBy('location', 'ASC')
            ->orderBy('storage_box', 'ASC')
            ->get();
    }
}
