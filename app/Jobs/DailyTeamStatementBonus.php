<?php

namespace App\Jobs;

use App\Models\MoneyLog;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

// 每日团队流水分红
class DailyTeamStatementBonus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $userRepository = app(UserRepository::class);

        $users = User::all();
        foreach ($users as $user) {
            $team_users = User::where("id", $user->id)
                ->orWhere("lv1_superior_id", $user->id)
                ->orWhere("lv2_superior_id", $user->id)
                ->orWhere("lv3_superior_id", $user->id)
                ->select("id")
                ->pluck("id");
            $total_raw = MoneyLog::whereIn("user_id", $team_users)
                ->where("log_type", 6)
                ->whereDate("created_at", Carbon::yesterday())
                ->sum("money");
            $total = abs($total_raw);

            // **************
            $bonus4_raw = setting("DAILY_TEAM_STATEMENT_BONUS_4");
            $bonus4 = explode(";", $bonus4_raw);
            if ($total >= intval($bonus4[0])) {
                $userRepository->addBalance([
                    "user_id" => $user->id,
                    "money" => floatval($bonus4[1]) * $total,
                    "log_type" => 13
                ]);
                continue;
            }

            // **************
            $bonus3_raw = setting("DAILY_TEAM_STATEMENT_BONUS_3");
            $bonus3 = explode(";", $bonus3_raw);
            if ($total >= intval($bonus3[0])) {
                $userRepository->addBalance([
                    "user_id" => $user->id,
                    "money" => floatval($bonus3[1]) * $total,
                    "log_type" => 13
                ]);
                continue;
            }

            // **************
            $bonus2_raw = setting("DAILY_TEAM_STATEMENT_BONUS_2");
            $bonus2 = explode(";", $bonus2_raw);
            if ($total >= intval($bonus2[0])) {
                $userRepository->addBalance([
                    "user_id" => $user->id,
                    "money" => floatval($bonus2[1]) * $total,
                    "log_type" => 13
                ]);
                continue;
            }

            // **************
            $bonus1_raw = setting("DAILY_TEAM_STATEMENT_BONUS_1");
            $bonus1 = explode(";", $bonus1_raw);
            if ($total >= intval($bonus1[0])) {
                $userRepository->addBalance([
                    "user_id" => $user->id,
                    "money" => floatval($bonus1[1]) * $total,
                    "log_type" => 13
                ]);
                continue;
            }

        }
    }
}
