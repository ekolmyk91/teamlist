<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnsDescriptionCodeCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE certificates MODIFY code VARCHAR(255) NULL;');
        DB::statement('ALTER TABLE certificates MODIFY description TEXT NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE certificates MODIFY code VARCHAR(255) NOT NULL;');
        DB::statement('ALTER TABLE certificates MODIFY description TEXT NOT NULL;');
    }
}
