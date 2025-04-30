<?php

namespace App\Console;

use App\Jobs\DailyTeamStatementBonus;
use App\Jobs\CancelFailedGPOrder;
use App\Jobs\CheckIsRechargeOrOwnItem;
use App\Jobs\ItemEarningJob;
use App\Jobs\DecItemStock;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        // 每日商品收益结算
        // 20230915: 恢复成自动
        //$schedule->job(new ItemEarningJob)->dailyAt("02:00");
        // 每日商品收益结算
         $schedule->job(new ItemEarningJob)->everyMinute();
        // 定时关闭过期&失败的拼团
        $schedule->job(new CancelFailedGPOrder)->everyMinute();
        // 每日团队流水分红
        $schedule->job(new DailyTeamStatementBonus)->daily();
        // 自动扣除商品库存
        $schedule->job(new DecItemStock)->everyMinute();
        // 判断用户归类
        $schedule->job(new CheckIsRechargeOrOwnItem)->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
