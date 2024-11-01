<?php

namespace App\Services\B2B;

use App\Repositories\B2B5FInputRepository;
use App\Repositories\MaterialRepository;
use App\Services\AppService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpsertB2BInputService extends AppService
{
    private $payload;

    protected $b2b5fInputRepository;
    protected $materialRepository;

    public function __construct(
        B2B5FInputRepository $b2b5fInputRepository,
        MaterialRepository $materialRepository
    ) {
        $this->b2b5fInputRepository = $b2b5fInputRepository;
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
                'inputId' => 'required|numeric',
                'manufacturingDate' => 'required|string',
                'sku' => 'required',
                'quantity' => 'required|numeric|min:1'
            ],
            [
                'inputId.required' => 'ID 必須有值。',
                'manufacturingDate.string' => '製造日期必須有值。',
                'sku.required' => 'SKU 必須有值。',
                'quantity.required' => '數量必須有值。',
                'quantity.numeric' => '數量格式錯誤。',
                'quantity.min' => '數量必須大於0。'
            ]
        );

        $material = $this->materialRepository->search([
            'sku' => $this->payload['sku']
        ])->first();

        if (is_null($material)) {
            throw ValidationException::withMessages(['material' => sprintf("無此 SKU (%s)。", $this->payload['sku'])]);
        }

        $user = Auth::user();

        $inputId = $this->payload['inputId'];

        if ($inputId > 0) {
            $this->b2b5fInputRepository->update(
                $inputId,
                [
                    'manufacturing_date' => $this->payload['manufacturingDate'],
                    'material_id' => $material->id,
                    'material_sku' => $material->sku,
                    'ean' => $material->ean,
                    'product_title' => $material->display_name,
                    'quantity' => $this->payload['quantity'],
                    'note' => $this->payload['note'],
                    'user_id' => $user->id,
                    'user_name' => $user->name
                ]
            );

            $input = $this->b2b5fInputRepository->search([
                'id' => $inputId
            ])->first();
        } else {
            $sql = <<<SQL
            SELECT IFNULL(MAX(`item_number`) + 1, 1) As `item_number` FROM `b2b_5f_inputs` WHERE `manufacturing_date` = ?
            SQL;

            $rows = DB::select($sql, [$this->payload['manufacturingDate']]);

            $input = $this->b2b5fInputRepository->create([
                'manufacturing_date' => $this->payload['manufacturingDate'],
                'item_number' => $rows[0]->item_number,
                'material_id' => $material->id,
                'material_sku' => $material->sku,
                'ean' => $material->ean,
                'product_title' => $material->display_name,
                'quantity' => $this->payload['quantity'],
                'note' => $this->payload['note'],
                'user_id' => $user->id,
                'user_name' => $user->name,
                'is_deleted' => 0
            ]);
        }

        return $input;
    }
}
