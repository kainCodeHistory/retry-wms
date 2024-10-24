<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class B2CStockLog extends Model
{
    use HasFactory;

    // 入庫
    const STOCK_INPUT = "stock_input";
    // 轉倉入庫
    const TRANSFER_INPUT = "transfer_input";
    // 轉倉出庫
    const TRANSFER_OUTPUT = "transfer_output";
    // 品項復歸
    const ITEM_RETURN = "item_return";
    // 庫存調整
    const ADJUST = "adjust";
    // EOL
    const ITEM_EOL = "item_eol";
    // 揀料
    const ITEM_PICK = "item_pick";

    protected $table = 'b2c_stock_logs';

    protected $fillable = [
        'working_day',
        'sku',
        'quantity',
        'balance',
        'event',
        'event_key',
        'note',
        'user_name'
    ];

    protected $appends = [
        'stock_log_events'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->setTimezone('Asia/Taipei')->format('Y-m-d H:i:s');
    }

    public function getStockLogEventsAttribute()
    {
        return [
            self::STOCK_INPUT => '入庫',
            self::TRANSFER_INPUT => '轉倉入庫',
            self::TRANSFER_OUTPUT => '轉倉出庫',
            self::ITEM_RETURN => '品項復歸',
            self::ADJUST => '庫存調整',
            self::ITEM_EOL => 'EOL',
            self::ITEM_PICK => '揀料'
        ];
    }
}
