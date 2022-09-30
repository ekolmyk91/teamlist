<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('off_time', function (Blueprint $table) {
            $table->unique(['user_id', 'start_day'], 'user_start_date_unique');
            $table->unique(['user_id', 'end_day'], 'user_end_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('off_time', function (Blueprint $table) {
            $table->dropUnique('user_start_date_unique');
            $table->dropUnique('user_end_date_unique');
        });
    }
}
