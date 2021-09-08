<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NullableFieldsInMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
	        $table->dropForeign('members_department_id_foreign');
	        $table->dropForeign('members_position_id_foreign');
	        $table->bigInteger('department_id')->nullable()->unsigned()->change();
	        $table->bigInteger('position_id')->nullable()->unsigned()->change();
	        $table->foreign('position_id')->references('id')->on('positions')
	              ->onDelete('restrict');
	        $table->foreign('department_id')->references('id')->on('departments')
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
        Schema::table('members', function (Blueprint $table) {
	        $table->dropForeign('members_department_id_foreign');
	        $table->dropForeign('members_position_id_foreign');
	        $table->foreign('position_id')->references('id')->on('positions')
	              ->onDelete('restrict');
	        $table->foreign('department_id')->references('id')->on('departments')
	              ->onDelete('restrict');
        });
    }
}
