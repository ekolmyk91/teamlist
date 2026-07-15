<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coffee_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->unsignedTinyInteger('frequency_weeks')->default(1);
            $table->unsignedTinyInteger('matching_day')->default(1);
            $table->time('meeting_time')->default('12:00:00');
            $table->unsignedSmallInteger('meeting_duration')->default(30);
            $table->time('work_time_from')->default('10:00:00');
            $table->time('work_time_to')->default('18:00:00');
            $table->unsignedTinyInteger('exclusion_weeks')->default(4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coffee_settings');
    }
};