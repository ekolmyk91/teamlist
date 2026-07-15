<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coffee_meetings', function (Blueprint $table) {
            $table->date('cycle_date')->nullable()->after('user_two_id')->index();
            $table->timestamp('user_one_asked_at')->nullable()->after('user_one_attended');
            $table->timestamp('user_two_asked_at')->nullable()->after('user_two_attended');
        });
    }

    public function down(): void
    {
        Schema::table('coffee_meetings', function (Blueprint $table) {
            $table->dropColumn(['cycle_date', 'user_one_asked_at', 'user_two_asked_at']);
        });
    }
};
