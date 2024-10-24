<?php

namespace App\Services\Query;

use App\Repositories\TransactionRepository;
use App\Services\AppService;

class GetBindTimeService extends AppService
{
    private $sku;

    protected $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function setSku(string $sku)
    {
        $this->sku = $sku;
        return $this;
    }

    public function exec()
    {
        $storageTransaction = $this->transactionRepository->getSkuBindTime($this->sku);
        $items = [];
        foreach ($storageTransaction as $skuRecord) {
            $pickingAreaTime = '';

            $firstStorageTime = '';

            $firstStorageTransaction = $this->transactionRepository->getSkuBindFirstLocationTime($skuRecord->storage_box, $this->sku);
            if (count($firstStorageTransaction) > 0) {
                $firstStorageTime = date('Y-m-d H:i:s', strtotime($firstStorageTransaction->last()->transactions_time) + 8 * 3600);
            }

            $pickingAreaTransaction = $this->transactionRepository->getPickingAreaTime($skuRecord->storage_box, $this->sku);
            if (count($pickingAreaTransaction) > 0) {
                $pickingAreaTime = date('Y-m-d H:i:s', strtotime($pickingAreaTransaction->last()->picking_area_time) + 8 * 3600);
            }
            $items[] = [
                'storage_box' => $skuRecord->storage_box,
                'material_sku' => $skuRecord->material_sku,
                'material_name' => $skuRecord->material_name,
                'material_bind' => date('Y-m-d H:i:s', strtotime($skuRecord->storage_box_time) + 8 * 3600),
                'storage_box_bind' => $firstStorageTime,
                'picking_area_bind' => $pickingAreaTime
            ];
        }

        return $items;
    }
}
