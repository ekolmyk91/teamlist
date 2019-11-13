<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkillMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skill_member', function (Blueprint $table) {
            $table->bigInteger('member')->unsigned();
            $table->integer('skill')->unsigned();

            $table->foreign('member')
                  ->references('user_id')->on('members')
                  ->onDelete('cascade');

            $table->foreign('skill')
                  ->references('id')->on('skills')
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
        Schema::dropIfExists('skill_member');
    }
}
