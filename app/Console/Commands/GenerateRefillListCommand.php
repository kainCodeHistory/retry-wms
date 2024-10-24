<?php

namespace App\Console\Commands;

use App\Services\PickingArea\Refill\GenerateAAZoneRefillListService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Libs\Slack\SlackService;

class GenerateRefillListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'slack:refillList';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate refill list and slack to nxl-notification channel';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fileName = app(GenerateAAZoneRefillListService::class)
            ->exec();

        app(SlackService::class)
            ->sendFile(config('app.slack.channel.nxl_notification'), $fileName, storage_path('slack/' . $fileName), Carbon::now()->format('Y-m-d H:i') . " 撿料倉雙箱區補料清單。");

        return 0;
    }
}
