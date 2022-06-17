<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOffTimeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('off_time', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('start_day');
            $table->date('end_day');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('type_id');
            $table->enum('status', array('draft','waiting_approve','approved'));
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('type_id')
                ->references('id')->on('off_time_type')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('off_time');
    }
}
