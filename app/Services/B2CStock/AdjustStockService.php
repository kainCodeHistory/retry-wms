<?php

namespace App\Services\B2CStock;

use App\Models\B2CStockLog;
use App\Repositories\B2CStockRepository;
use App\Repositories\B2CStockLogRepository;
use App\Repositories\MaterialRepository;
use App\Services\AppService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class AdjustStockService extends AppService
{
    /**
     * payload
     * @var array
     */
    private $payload = [];

    private $b2cStockRepository;
    private $b2cStockLogRepository;
    private $materialRepository;

    public function __construct(
        B2CStockRepository $b2cStockRepository,
        B2CStockLogRepository $b2cStockLogRepository,
        MaterialRepository $materialRepository
    ) {
        $this->b2cStockRepository = $b2cStockRepository;
        $this->b2cStockLogRepository = $b2cStockLogRepository;
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
                'ean_sku' => 'required|string',
                'current_quantity' => 'required|numeric',
                'adjusted_quantity' => 'required|numeric'
            ]
        );

        $eanSku = $this->payload['ean_sku'];

        $material = $this->materialRepository->getMaterialByEanOrSku($eanSku)->first();

        if (is_null($material)) {
            throw new ValidationException(sprintf("該物料 (%s) 不存在。", $eanSku));
        }

        $currentQuantity = (int)$this->payload['current_quantity'];
        $adjustedQuantity = (int)$this->payload['adjusted_quantity'];
        $note = $this->payload['note'] ?? '';
        $stock = $this->b2cStockRepository->search([
            'sku' => $material->sku
        ])->first();

        try {
            DB::beginTransaction();

            $this->b2cStockRepository->update(
                $stock->id,
                [
                    'total_quantity' => $adjustedQuantity
                ]
            );

            $loggedInUser = Auth::user();

            $this->b2cStockLogRepository->create([
                'working_day' => Carbon::now()->format('Y-m-d'),
                'sku' => $material->sku,
                'quantity' => $adjustedQuantity - $currentQuantity,
                'balance' => $adjustedQuantity,
                'event' => B2CStockLog::ADJUST,
                'event_key' => '',
                'note' => $note,
                'user_name' => $loggedInUser->name
            ]);

            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            dd($th->getMessage());
            throw new Exception(sprintf("%s 庫存調整失敗。", $eanSku));
        }
    }
}
