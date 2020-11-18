<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeOnDeleteCertificateMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('certificate_member', function (Blueprint $table) {
            $table->primary(array('member', 'certificate'));
            $table->dropForeign('certificate_member_certificate_foreign');
            $table->foreign('certificate')
                ->references('id')->on('certificates')
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
        Schema::table('certificate_member', function (Blueprint $table) {
            $table->dropForeign('certificate_member_member_foreign');
            $table->dropForeign('certificate_member_certificate_foreign');
            $table->dropPrimary(array('member', 'certificate'));
            $table->foreign('certificate')
                ->references('id')->on('certificates')
                ->onDelete('cascade');
        });
    }
}
