<?php

namespace App\Services\PickingArea;

use App\Repositories\MaterialRepository;
use App\Repositories\RolloverRepository;
use App\Services\AppService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RolloverRecordService extends AppService
{
    protected $rolloverRepository;
    protected $materialRepository;

    public function __construct( MaterialRepository $materialRepository, RolloverRepository $rolloverRepository)
    {
        $this->rolloverRepository = $rolloverRepository;
        $this->materialRepository = $materialRepository;
    }

    public function setPayload(array $payload)
    {
        $this->payload = $payload;
        return $this;
    }

    public function exec()
    {
        $this->validate(
            $this->payload,
            [
                'quantity' => 'required|integer|min:1',
                'sku' => 'required|string'
            ],
            [
                'sku.required' => 'SKU 必須有值。',
                'quantity.required' => '數量必須有值。',
                'quantity.min' => '數量必須大於 0。'
            ]
        );

        $material = $this->materialRepository->search([
            'sku' => $this->payload['sku']
        ]);
        if (count($material) === 0) {
            throw ValidationException::withMessages(['sku' => '無此物料 (' . $this->payload['sku'] . ')。']);
        }
        $material = $material->get(0);


        try {
            DB::beginTransaction();

            $this->rolloverRepository->create([
                'sku' => $this->payload['sku'],
                'quantity' => (int)$this->payload['quantity'],
                'note' => $this->payload['note']
            ]);

            DB::commit();

            return [
                'sku' => $this->payload['sku'],
                'quantity' => $this->payload['quantity']
            ];
        } catch (Exception $ex) {
            throw ValidationException::withMessages(['location' => $ex->getMessage()]);
            DB::rollBack();
        }
    }
}
