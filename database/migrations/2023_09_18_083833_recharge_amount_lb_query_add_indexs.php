<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index("lv1_superior_id", "lv1_superior_id_index");
        });
        Schema::table('money_log', function (Blueprint $table) {
            $table->index("user_id", "user_id_index");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex("lv1_superior_id_index");
        });
        Schema::table('money_log', function (Blueprint $table) {
            $table->dropIndex("user_id_index");
        });
    }
};
