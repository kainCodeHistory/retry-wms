<?php

namespace App\Services\Inventory;

use App\Repositories\InventoryRepository;
use App\Services\AppService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;
use FFI;

class CheckInventoryService extends AppService
{
    protected $box;

    public function __construct(InventoryRepository $inventoryRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
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
                'box' => 'required|string',
            ],
            [
                'box.required' => '貨箱條碼必須有值。',
                'checkQuantity.required' => '數量必須有值。'
            ]
        );
        $box = $this->inventoryRepository->search([
            'storage_box' => $this->payload['box']
        ]);
        $box = $box->get(0);
        try {
            DB::beginTransaction();

            $this->inventoryRepository->update(
                $box->id,
                [
                    'check_quantity' => (int)$this->payload['checkQuantity'],
                    'status' => 'check_inventory',
                    'user_id' => Auth::user()->id
                ]
            );


            DB::commit();

            return [
                'box' => $this->payload['box'],
                'quantity' =>  $this->payload['checkQuantity']
            ];
        } catch (Exception $ex) {
            throw ValidationException::withMessages(['location' => $ex->getMessage()]);
            DB::rollBack();
        }
    }
}
