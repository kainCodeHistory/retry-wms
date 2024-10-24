<?php

namespace App\Services\B2B;

use App\Repositories\B2BInventoryRepository;
use App\Repositories\MaterialRepository;
use App\Services\AppService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class B2BInventoryService extends AppService
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
            $material = $this->materialRepository->search([
                'sku' => $this->payload['sku']
            ]);
            if (count($inventory) === 0) {
                $this->b2bInventoryRepository->create([
                    'material_id' => $material->get(0)->id,
                    'material_sku' => $material->get(0)->sku,
                    'material_name' => $material->get(0)->display_name,
                    'first_quantity' => (int)$this->payload['quantity'],
                    'status' => 'first_inventory',
                    'user_id' => Auth::user()->id
                ]);
            } else {
                $this->b2bInventoryRepository->update(
                    $inventory->get(0)->id,
                    [
                        'first_quantity' => (int)$this->payload['quantity'],
                        'user_id' => Auth::user()->id
                    ]
                );
            }

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



