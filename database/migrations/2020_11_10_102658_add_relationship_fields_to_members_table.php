<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRelationshipFieldsToMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->bigInteger('position_id')->after('department_id')->unsigned();
            $table->foreign('position_id')->references('id')->on('positions')
                  ->onDelete('restrict');
            DB::statement('ALTER TABLE members MODIFY department_id BIGINT UNSIGNED NOT NULL;');
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
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
            $table->dropForeign(['department_id']);
        });
    }
}
