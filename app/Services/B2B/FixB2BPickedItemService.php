<?php

namespace App\Services\B2B;

use App\Models\B2BStockLog;
use App\Repositories\B2BPickedItemRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\AppService;
use App\Services\B2BStock\UpdateB2BStockService;

class FixB2BPickedItemService extends AppService
{
    protected $payload;

    protected $b2bPickedItemRepository;

    public function __construct(B2BPickedItemRepository $b2bPickedItemRepository)
    {
        $this->b2bPickedItemRepository = $b2bPickedItemRepository;
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
                'id' => 'required',
                'fixed_quantity' => 'required|numeric|min:1'
            ],
            [
                'id.required' => 'id必須有值。',
                'fixed_quantity.required' => '修正數量必須有值。',
                'fixed_quantity.min' => '數量必須大於 0'
            ]
        );
        $getId = $this->b2bPickedItemRepository->search(['id' => $this->payload['id']])->first();

        if (is_null($getId)) {
            throw ValidationException::withMessages(['messages' => '查無此id編號' . $this->payload['id']]);
        }

        try {
            DB::beginTransaction();

            $this->b2bPickedItemRepository->update($this->payload['id'], ['fixed_quantity' => $this->payload['fixed_quantity']]);

            app(UpdateB2BStockService::class)
                ->setPayload([
                    'items' => [
                        [
                            'sku' => $getId->sku,
                            'quantity' =>  $this->payload['fixed_quantity'],
                            'event' => B2BStockLog::ITEM_RETURN,
                            'eventKey' => '',
                            'note' => '多扣'
                        ]
                    ]
                ])
                ->exec();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
        }


        return [
            "message" => "ok"
        ];
    }
}
