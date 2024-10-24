<?php

namespace App\Services\PickingArea\Refill;

use App\Models\StorageBox\StorageBox;
use App\Repositories\StorageBox\InventoryItemRepository;
use App\Repositories\StorageBox\StorageBoxRepository;
use App\Services\AppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateAAZoneRefillListService extends AppService
{
    protected $inventoryItemRepository;
    protected $storageBoxRepository;

    public function __construct(InventoryItemRepository $inventoryItemRepository, StorageBoxRepository $storageBoxRepository)
    {
        $this->inventoryItemRepository = $inventoryItemRepository;
        $this->storageBoxRepository = $storageBoxRepository;
    }


    public function exec()
    {
        $wrongLocations = DB::select("SELECT `tmp`.`location`, COUNT(`tmp`.`material_sku`) FROM ( " .
            "SELECT DISTINCT `location`, `material_sku` FROM `v_picking_items` WHERE LEFT(`location`, 2) = 'AA' " .
            ") As `tmp` GROUP BY `tmp`.`location` HAVING COUNT(`tmp`.`material_sku`) > 1");
        $wrongLocations = (collect($wrongLocations)->pluck('location')->toArray());

        $items = DB::select("SELECT `location`, `material_sku`, COUNT(`storage_box`) FROM `v_picking_items` WHERE (LEFT(`location`, 2) = 'AA'  or  LEFT(`location`, 5) = 'MN-07') GROUP BY `location`, `material_sku` HAVING COUNT(`storage_box`) = 1");

        $timestamp = Carbon::now()->format('YmdHis');
        $fileName = 'refill_' . $timestamp . ".csv";
        if (count($items) > 0) {
            $fp = fopen(storage_path("slack/" . $fileName), 'w');

            fputcsv($fp, [$this->toBig5('撿料倉儲位'), 'SKU', $this->toBig5('預備倉儲位'), $this->toBig5('貨箱號碼'), $this->toBig5('數量')]);
            foreach ($items as $item) {
                if (!in_array($item->location, $wrongLocations)) {
                    try {
                        DB::beginTransaction();
                        $inventoryItem = $this->inventoryItemRepository->search([
                            'material_sku' => $item->material_sku
                        ])->sortBy('bound_at')->first();

                        if (is_null($inventoryItem)) {
                            fputcsv($fp, [$item->location, $item->material_sku, '-', '-', null]);
                        } else {
                            // storage_boxes
                            $storageBox = $this->storageBoxRepository->search([
                                'barcode' => $inventoryItem->storage_box
                            ])->first();

                            $this->storageBoxRepository->update(
                                $storageBox->id,
                                [
                                    'warehouse_id' => null,
                                    'location' => '',
                                    'status' => StorageBox::STORAGE
                                ]
                            );

                            fputcsv($fp, [$item->location, $item->material_sku, $inventoryItem->location, $inventoryItem->storage_box, $inventoryItem->quantity]);
                        }

                        DB::commit();
                    } catch (\Exception $ex) {
                        fputcsv($fp, [$item->location, $item->material_sku, '-', '-', $ex->getMessage()]);
                        DB::rollBack();
                    }
                }
            }

            fclose($fp);
        }
        return $fileName;
    }

    private function toBig5(string $value) {
        return iconv('UTF-8', 'BIG5', $value);
    }
}
