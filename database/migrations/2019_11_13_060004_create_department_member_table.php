<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepartmentMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('department_member', function (Blueprint $table) {
            $table->bigInteger('member')->unsigned();
            $table->integer('department')->unsigned();

            $table->foreign('member')
                  ->references('user_id')->on('members')
                  ->onDelete('cascade');

            $table->foreign('department')
                  ->references('id')->on('departments')
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
        Schema::dropIfExists('department_member');
    }
}
