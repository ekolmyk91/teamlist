<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->boolean('random_coffee')->default(true)->after('trainee');
            $table->time('work_time_from')->nullable()->after('random_coffee');
            $table->time('work_time_to')->nullable()->after('work_time_from');
            $table->string('telegram_chat_id', 32)->nullable()->after('work_time_to')->index();
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['random_coffee', 'work_time_from', 'work_time_to', 'telegram_chat_id']);
        });
    }
};