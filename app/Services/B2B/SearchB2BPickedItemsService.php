<?php

namespace App\Services\B2B;

use App\Repositories\B2BPickedItemRepository;
use App\Repositories\MaterialRepository;
use Illuminate\Validation\ValidationException;
use App\Services\AppService;

class SearchB2BPickedItemsService extends AppService
{
    protected $payload;

    protected $b2bPickedItemRepository;
    protected $materialRepository;

    public function __construct(B2BPickedItemRepository $b2bPickedItemRepository, MaterialRepository $materialRepository)
    {
        $this->b2bPickedItemRepository = $b2bPickedItemRepository;
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
                "start_date" => 'required',
                "end_date" => 'required',
                "sku" => 'required|string'
            ],
            [
                'sku.required' => 'SKU必須有值。',
                'start_date.required' => '開始日期必須有值。',
                'end_date.required' => '結束日期必須有值。'
            ]
        );

        $startTime = $this->payload['start_date'];
        $endTime = $this->payload['end_date'];
        $dateLimit = date("Y-m-d", strtotime("+1 month", strtotime($startTime)));

        if ($endTime < $startTime || $dateLimit <= $endTime) {
            throw ValidationException::withMessages(['user' => '時間區間超過上限1個月。']);
        }

        $getMaterial = $this->materialRepository->search(['sku' => $this->payload['sku']])->first();
        if (is_null($getMaterial)) {
            throw ValidationException::withMessages(['user' => '查無此料號' . $this->payload['sku']]);
        }

        $getPickedRecords =  $this->b2bPickedItemRepository->getDetails($this->payload['sku'], $startTime, $endTime);
        if (count($getPickedRecords) === 0) {
            throw ValidationException::withMessages(['user' => '查無此料號' . $this->payload['sku'] . '記錄']);
        }


        foreach ($getPickedRecords as $getPickedRecord) {

            $items[] = [
                'id' => $getPickedRecord->id,
                'employee_no' => $getPickedRecord->employee_no,
                'sku' => $getPickedRecord->sku,
                'order_number' => $getPickedRecord->order_number,
                'quantity' => $getPickedRecord->quantity,
                'fixed_quantity' => $getPickedRecord->fixed_quantity,
                'created_at' => date('Y-m-d H:i:s', strtotime($getPickedRecord->created_at) + 8 * 3600)
            ];
        }

        return $items;
    }
}
