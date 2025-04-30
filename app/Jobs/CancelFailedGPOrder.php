<?php

namespace App\Jobs;

use App\Models\Item;
use App\Models\GroupPurchaseRecord;
use App\Repositories\UserRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CancelFailedGPOrder implements ShouldQueue
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

        $records = GroupPurchaseRecord::where("status", 0)
            ->where("expired_at", "<", now())
            ->get();
        $userRepository = app(UserRepository::class);
        
        foreach ($records as $record) {
            $record->status = 2;
            $record->save();

            $item = $record->item;

            // 返还余额
            $userRepository->addBalance([
                "user_id" => $record->user_id,
                "money" => $item->price,
                "log_type" => 10,
                "item_id" => $record->item_id
            ]);

            // 返回库存
            DB::transaction(function() use ($record) {
                $item = Item::where("id", $record->item->id)
                    ->lockForUpdate()
                    ->first();
                $item->stock += 1;
                $item->save();
            });
        }
        
    }
}
