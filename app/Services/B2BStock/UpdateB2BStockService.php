<?php

namespace App\Services\B2BStock;

use App\Models\B2BStockLog;
use App\Repositories\B2BStockLogRepository;
use App\Repositories\B2BStockRepository;
use App\Repositories\StorageBox\StorageBoxRepository;
use App\Services\AppService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Libs\Slack\SlackService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateB2BStockService extends AppService
{
    private $b2bStockRepository;
    private $b2bStockLogRepository;
    private $storageBoxRepository;

    /**
     * payload
     * @var array
     */
    private $payload;

    public function __construct(
        B2BStockRepository $b2bStockRepository,
        B2BStockLogRepository $b2bStockLogRepository,
        StorageBoxRepository $storageBoxRepository
    ) {
        $this->b2bStockLogRepository = $b2bStockLogRepository;
        $this->b2bStockRepository = $b2bStockRepository;
        $this->storageBoxRepository = $storageBoxRepository;
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
                'items' => 'required|array',
                'items.*.sku' => 'required|string',
                'items.*.event' => 'required|string',
                'items.*.quantity' => 'required|integer'
            ]
        );

        $workingDay = Carbon::now()->format('Y-m-d');

        $loggedInUser = Auth::user();
        if (is_null($loggedInUser)) {
            $loggedInUser = "";
        } else {
            $loggedInUser = $loggedInUser->name;
        }
        try {
            DB::beginTransaction();

            foreach ($this->payload['items'] as $item) {
                $event = $item['event'];
                $stock = $this->b2bStockRepository->search(['sku' => $item['sku']])->first();

                $storageZone = config('storageBoxZone.storage');
                $floor = (array_values($storageZone['5F']));
                $storageBox = $this->storageBoxRepository->search(['barcode' => $item['eventKey']])->first();
                if (!is_null($storageBox)) {
                    $prefix =  $storageBox->prefix;

                    if (!in_array($prefix, $floor)) {
                        throw ValidationException::withMessages(['box' => '此貨箱只能在B2C倉 (' . $this->payload['storageBox'] . ')使用。']);
                    }
                }
                if (
                    $event === B2BStockLog::STOCK_INPUT ||
                    $event === B2BStockLog::TRANSFER_INPUT ||
                    $event === B2BStockLog::ITEM_RETURN
                ) {
                    $this->handleInput($event, $item, $stock, $workingDay, $loggedInUser);
                } else if (
                    $event === B2BStockLog::ITEM_PICK ||
                    $event === B2BStockLog::TRANSFER_OUTPUT ||
                    $event === B2BStockLog::ITEM_EOL
                ) {
                    $this->handleOutput($event, $item, $stock, $workingDay, $loggedInUser);
                } else {
                    // Adjust
                    if ($item['quantity'] > 0) {
                        $this->handleInput($event, $item, $stock, $workingDay, $loggedInUser);
                    } else {
                        $item['quantity'] = abs($item['quantity']);
                        $this->handleOutput($event, $item, $stock, $workingDay, $loggedInUser);
                    }
                }
            }

            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
        }
    }

    private function handleInput(string $event, array $item, $stock, $workingDay, $loggedInUser)
    {
        if (is_null($stock)) {
            $this->createStock($item['sku'], $item['quantity']);

            $this->appendLog(
                $item['sku'],
                $item['quantity'],
                $item['quantity'],
                $event,
                $item['eventKey'],
                $item['note'],
                $workingDay,
                $loggedInUser
            );
        } else {
            $subtotal = $stock->total_quantity + $item['quantity'];
            $this->appendLog(
                $item['sku'],
                $item['quantity'],
                $subtotal,
                $event,
                $item['eventKey'],
                $item['note'],
                $workingDay,
                $loggedInUser
            );

            $this->updateStock($stock->id, $subtotal);
        }
    }

    private function handleOutput(string $event, array $item, $stock, $workingDay, $loggedInUser)
    {
        if (is_null($stock)) {

            $subtotal = 0 - $item['quantity'];
            $this->createStock($item['sku'], $subtotal);
            $this->appendLog(
                $item['sku'],
                $event === B2BStockLog::ADJUST ? -$item['quantity'] : $item['quantity'],
                $subtotal,
                $event,
                $item['eventKey'],
                $item['note'],
                $workingDay,
                $loggedInUser
            );

            $blocks = [];
            array_push($blocks, $this->getSlackHeader("B2B 負庫存。"));
            $blocks = array_merge($blocks, $this->getSlackBody($event, $item['sku'], $item['quantity'], '無此 SKU 庫存。'));

            app(SlackService::class)
                ->sendMessageViaWebhookURL(config('app.slack.channel.nxl_logger'), $blocks);
        } else {
            $subtotal = $stock->total_quantity - $item['quantity'];

            $this->updateStock($stock->id, $subtotal);

            $this->appendLog(
                $item['sku'],
                $event === B2BStockLog::ADJUST ? -$item['quantity'] : $item['quantity'],
                $subtotal,
                $event,
                $item['eventKey'],
                $item['note'],
                $workingDay,
                $loggedInUser
            );

            if ($subtotal < 0) {
                $blocks = [];
                array_push($blocks, $this->getSlackHeader("B2B 負庫存。"));
                $blocks = array_merge($blocks, $this->getSlackBody($event, $item['sku'], $item['quantity'], sprintf('庫存不足 (原庫存: %s)。', $subtotal + $item['quantity'])));

                app(SlackService::class)
                    ->sendMessageViaWebhookURL(config('app.slack.channel.nxl_logger'), $blocks);
            }
        }
    }

    private function createStock(string $sku, int $quantity)
    {
        $this->b2bStockRepository->create([
            'sku' => $sku,
            'total_quantity' => $quantity
        ]);
    }

    private function updateStock(int $stockId, int $quantity)
    {
        $this->b2bStockRepository->update(
            $stockId,
            [
                'total_quantity' => $quantity
            ]
        );
    }

    private function appendLog(
        string $sku,
        int $quantity,
        int $balance,
        string $event,
        string $eventKey,
        string $note,
        $workingDay,
        $loggedInUser
    ) {
        Log::info('B2B-stock-log:'.$eventKey.'event:'.$event.',user:'.$loggedInUser.',date:'.$workingDay);
        $this->b2bStockLogRepository->create([
            'working_day' => $workingDay,
            'sku' => $sku,
            'quantity' => $quantity,
            'balance' => $balance,
            'event' => $event,
            'event_key' => $eventKey,
            'note' => $note,
            'user_name' => $loggedInUser
        ]);
    }

    private function getSlackHeader(string $header): array
    {
        return [
            "type" => "header",
            "text" => [
                "type" => "plain_text",
                "text" => $header
            ]
        ];
    }

    private function getSlackBody(string $event, string $sku, int $quantity, string $note): array
    {
        $body = [];
        array_push($body, [
            "type" => "section",
            "text" => [
                "type" => "mrkdwn",
                "text" => sprintf("*SKU*: `%s`", $sku)
            ]
        ]);

        array_push($body, [
            "type" => "section",
            "text" => [
                "type" => "mrkdwn",
                "text" => sprintf("*異動數量*: `%s`", $quantity)
            ]
        ]);

        array_push($body, [
            "type" => "section",
            "text" => [
                "type" => "mrkdwn",
                "text" => sprintf("*事件代碼*: `%s`", $event)
            ]
        ]);

        array_push($body, [
            "type" => "section",
            "text" => [
                "type" => "mrkdwn",
                "text" => sprintf("*錯誤訊息*: `%s`", $note)
            ]
        ]);

        return $body;
    }
}
