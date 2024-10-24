<?php

namespace App\Services\B2B;

use App\Repositories\B2BInputRepository;
use App\Repositories\MaterialRepository;
use App\Services\AppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Throwable;

class AddB2BInputService extends AppService
{
    private $payload;

    protected $b2bInputRepository;
    protected $materialRepository;

    public function __construct(
        B2BInputRepository $b2bInputRepository,
        MaterialRepository $materialRepository
    ) {
        $this->b2bInputRepository = $b2bInputRepository;
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
                'box' => 'required',
                'sku' => 'required',
                'quantity' => 'required|numeric|min:1'
            ],
            [
                'box.required' => '紙箱條碼必須有值。',
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

        $now = Carbon::now('Asia/Taipei');
        $user = Auth::user();

        try {
            $input = $this->b2bInputRepository->create([
                'transaction_date' => $now->format('Y-m-d'),
                'box' => $this->payload['box'],
                'material_id' => $material->id,
                'material_sku' => $material->sku,
                'ean' => $material->ean,
                'product_title' => $material->display_name,
                'quantity' => $this->payload['quantity'],
                'user_id' => $user->id,
                'user' => $user->name,
                'created_at' => $now->format('Y-m-d H:i:s'),
                'updated_at' => $now->format('Y-m-d H:i:s')
            ]);

            return $input->id;
        } catch (Throwable $th) {
            throw ValidationException::withMessages(['box' => $th->getMessage()]);
        }
    }
}
