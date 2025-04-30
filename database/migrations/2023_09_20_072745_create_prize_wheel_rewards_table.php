<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrizeWheelRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prize_wheel_rewards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->default('')->comment('名称');
            $table->tinyInteger('reward_type')->comment('奖品类型,1=优惠券,2=产品,3=现金');
            $table->bigInteger('coupon_id')->nullable()->comment('奖品-优惠券ID');
            $table->bigInteger('item_id')->nullable()->comment('奖品-产品ID');
            $table->decimal('cash_amount', 20, 2)->nullable()->comment('奖品-现金数量');
            $table->decimal('rate', 20, 2)->comment('概率(百分比)');
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
        Schema::dropIfExists('prize_wheel_rewards');
    }
}
