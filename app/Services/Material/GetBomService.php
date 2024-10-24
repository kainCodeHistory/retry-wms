<?php

namespace App\Services\Material;

use App\Repositories\MaterialRepository;
use App\Repositories\StorageBox\PickingItemRepository;
use App\Services\AppService;

class GetBomService extends AppService
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
        try {
            $this->validate($this->payload, [
                'skus' => 'required|array'
            ], [
                'skus.required' => 'SKU 為必填項目。',
                'skus.array' => 'SKU 可接受的格式為陣列。'
            ]);
            $skus = array_keys($this->payload['skus']);

            $materials = $this->materialRepository->getMaterialsBySku($skus);
            $bomSkus = $materials->pluck('check_sku')->toArray();

            $pickingItems = $this->pickingItemRepository->getPickingItems($bomSkus);
            $items = $materials->map(function($material) use ($pickingItems) {
                $filteredPickingItems = $pickingItems->filter(function($pickingItem) use ($material) {
                    return $pickingItem->material_sku === $material->check_sku;
                });

                if (count($filteredPickingItems) > 0) {
                    $checkSku = $filteredPickingItems->first()->material_sku;
                } else {
                    $checkSku = '';
                }
                return [
                    'sku' => $material->sku,
                    'checkSku' => $checkSku,
                    'locations' => $filteredPickingItems->pluck('location')->toArray()
                ];
            });

            $foundSkus = $items->filter(function($sku) {
                return !empty($sku['checkSku']);
            });

            $missingSkus = collect($skus)->filter(function($sku) use ($foundSkus) {
                return !$foundSkus->contains(function($foundSku) use ($sku) {
                    return $foundSku['sku'] === $sku;
                });
            })->values()->toArray();

            return [
                'foundSkus' => $foundSkus,
                'missingSkus' => $missingSkus
            ];
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
