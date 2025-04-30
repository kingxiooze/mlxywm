<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrizeWheelLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prize_wheel_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->comment('用户ID');
            $table->bigInteger('reward_id')->comment('奖品ID');
            // 以下为奖品信息的快照
            $table->string('name')->default('')->comment('名称');
            $table->tinyInteger('reward_type')->comment('奖品类型,1=优惠券,2=产品,3=现金');
            $table->bigInteger('coupon_id')->nullable()->comment('奖品-优惠券ID');
            $table->bigInteger('item_id')->nullable()->comment('奖品-产品ID');
            $table->decimal('cash_amount', 20, 2)->nullable()->comment('奖品-现金数量');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prize_wheel_logs');
    }
}
