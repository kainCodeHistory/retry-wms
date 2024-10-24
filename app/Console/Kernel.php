<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\CreateAZPickingListCommand',
        'App\Console\Commands\SyncCheckSkuMaterialCommand',
        'App\Console\Commands\SyncMaterialCommand',
        'App\Console\Commands\SyncPickingAreaInventoryCommand',
        'App\Console\Commands\StockOutCommand',
        'App\Console\Commands\ClearDataCommand'
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('sync:pickingAreaInventory shipping_server --az')->timezone('Asia/Taipei')->dailyAt('02:05')->withoutOverlapping();
        $schedule->command('stock:out')->timezone('Asia/Taipei')->dailyAt('00:45')->withoutOverlapping();
        $schedule->command('stock:out')->timezone('Asia/Taipei')->dailyAt('12:45')->withoutOverlapping();
        $schedule->command('clear:data')->timezone('Asia/Taipei')->dailyAt('01:00')->withoutOverlapping();

        $schedule->command('sync:materials')->timezone('Asia/Taipei')->dailyAt('03:00')->withoutOverlapping();
        $schedule->command('sync:materialsCheckSku')->timezone('Asia/Taipei')->dailyAt('04:00')->withoutOverlapping();

        $schedule->command('shipmentItem:LocationIsEmpty')->timezone('Asia/Taipei')->dailyAt('09:00')->withoutOverlapping();
        $schedule->command('shipmentItem:LocationIsEmpty')->timezone('Asia/Taipei')->dailyAt('13:30')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
