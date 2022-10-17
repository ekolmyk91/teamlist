<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDaysPerYearTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('off_time_types', function (Blueprint $table) {
            $table->bigInteger('days_per_year')->after('name')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('off_time_types', function (Blueprint $table) {
            $table->dropColumn('days_per_year');
        });
    }
}
