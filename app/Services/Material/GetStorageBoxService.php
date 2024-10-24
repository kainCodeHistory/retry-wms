<?php

namespace App\Services\Material;

use App\Repositories\MaterialRepository;
use App\Repositories\StorageBox\PickingItemRepository;
use App\Services\AppService;

class GetStorageBoxService extends AppService
{
    protected $payload;

    protected $materialRepository;
    protected $pickingItemRepository;

    public function __construct(MaterialRepository $materialRepository, PickingItemRepository $pickingItemRepository)
    {
        $this->materialRepository = $materialRepository;
        $this->pickingItemRepository = $pickingItemRepository;
    }

    public function setPayload(array $payload)
    {
        $this->payload = $payload;
        return $this;
    }

    public function exec()
    {
        $skuStorageBox=[];
        try {
            $this->validate($this->payload, [
                'skus' => 'required|array'
            ], [
                'skus.required' => 'SKU 為必填項目。',
                'skus.array' => 'SKU 可接受的格式為陣列。'
            ]);
            $skus = array_values($this->payload['skus']);

            $pickingItems = $this->pickingItemRepository->getPickingItems($skus);

            foreach($pickingItems as $pickingItem){
                if (array_key_exists($pickingItem->material_sku, $skuStorageBox)) {
                    array_push($skuStorageBox[$pickingItem->material_sku] , $pickingItem->storage_box);
                }else{
                    $skuStorageBox[$pickingItem->material_sku] = [$pickingItem->storage_box];
                }
            }

            return $skuStorageBox;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
