<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCertificateMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certificate_member', function (Blueprint $table) {
            $table->bigInteger('member')->unsigned();
            $table->integer('certificate')->unsigned();

            $table->foreign('member')
                  ->references('user_id')->on('members')
                  ->onDelete('cascade');

            $table->foreign('certificate')
                  ->references('id')->on('certificates')
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
        Schema::dropIfExists('certificate_member');
    }
}
