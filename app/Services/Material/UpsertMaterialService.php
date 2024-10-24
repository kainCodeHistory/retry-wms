<?php

namespace App\Services\Material;

use App\Services\AppService;
use App\Repositories\MaterialRepository;

class UpsertMaterialService extends AppService
{
    protected $payload;
    protected $materialRepository;

    public function __construct(MaterialRepository $materialRepository)
    {
        $this->materialRepository = $materialRepository;
    }

    public function setPayload(array $payload)
    {
        $this->payload = $payload;
        return $this;
    }

    public function exec()
    {
        $this->validate($this->payload, [
            'sku' => 'required|string',
            'name' => 'required|string',
            'check_sku' => 'required|string',
            'check_for_leash' => 'required|boolean'
        ], [
            'sku.required' => 'SKU 必須有值。',
            'name.required' => '物料名稱必須有值。',
            'check_sku.required' => '檢核料號必須有值。',
            'check_for_leash.required' => '是否檢查開賣期間必須有值。'
        ]);

        $material = $this->materialRepository->search([
            'sku' => $this->payload['sku']
        ]);

        if (count($material) > 0) {
            $material = $material->get(0);
            $this->materialRepository->update(
                $material->id,
                [
                    'display_name' => $this->payload['name'],
                    'full_name' => $this->payload['name'],
                    'check_sku' => $this->payload['check_sku'],
                    'check_for_leash' => $this->payload['check_for_leash']
                ]
            );
        } else {
            $material = $this->materialRepository->create([
                'sku' => $this->payload['sku'],
                'display_name' => $this->payload['name'],
                'full_name' => $this->payload['name'],
                'check_sku' => $this->payload['check_sku'],
                'check_for_leash' => $this->payload['check_for_leash']
            ]);
        }
        $material = $this->materialRepository->findOrFail($material->id);
        return $material;
    }
}
