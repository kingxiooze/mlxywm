<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRedpacketsV2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redpackets_v2', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('salesman_mobile')->default('')->comment('业务员手机号');
            $table->string('remark')->default('')->comment('备注');
            $table->decimal('amount', 20, 2)->comment('金额');
            $table->integer('count')->comment('可领取人数');
            $table->tinyInteger("is_sale")->default(0)->comment("是否上架, 0=下架, 1=上架");
            $table->tinyInteger("status")->default(1)->comment("状态, 0=已发完, 1=未发完");
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
        Schema::dropIfExists('redpackets_v2');
    }
}
