<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->bigIncrements('user_id')->unique()->unsigned();
            $table->string('name')->nullable();
            $table->string('surname')->nullable();
            $table->timestamp('birthday')->nullable();
            $table->string('email')->unique();
            $table->string('phone_1')->nullable();
            $table->string('phone_2')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->bigInteger('room_id')->nullable();
            $table->mediumText('about')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('members');
    }
}
