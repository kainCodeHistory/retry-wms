<?php

namespace App\Services\B2B;

use App\Repositories\B2BInventoryRepository;
use App\Repositories\MaterialRepository;
use App\Services\AppService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class B2BCheckInventoryService extends AppService
{
    protected $box;
    protected $b2bInventoryRepository;
    protected $materialRepository;
    protected $payload;

    public function __construct(B2BInventoryRepository $b2bInventoryRepository, MaterialRepository $materialRepository)
    {
        $this->b2bInventoryRepository = $b2bInventoryRepository;
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
                'sku' => 'required|string',
                'quantity' => 'required|integer|min:1'
            ],
            [
                'quantity.required' => '數量必須有值。',
                'quantity.min' => '數量必須大於 0。'
            ]
        );

        try {
            DB::beginTransaction();
            $inventory = $this->b2bInventoryRepository->search([
                'material_sku' => $this->payload['sku']
            ]);
            $inventory = $inventory->get(0);


            $this->b2bInventoryRepository->update(
                $inventory->id,
                [
                    'check_quantity' => (int)$this->payload['quantity'],
                    'status' => 'check_inventory',
                    'user_id' => Auth::user()->id
                ]
            );

            DB::commit();

            return [
                'sku' => $this->payload['sku'],
                'quantity' =>  $this->payload['quantity']
            ];
        } catch (Exception $ex) {
            throw ValidationException::withMessages(['location' => $ex->getMessage()]);
            DB::rollBack();
        }
    }
}
