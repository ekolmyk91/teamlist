<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coffee_meeting_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id');
            $table->unsignedBigInteger('user_id');
            $table->string('question_key', 64);
            // Snapshot of the wording the employee actually saw: the question
            // list lives in config and may be re-worded later.
            $table->string('question_label', 500);
            // Closed answers keep their canonical value (yes / no / maybe...),
            // free-form ones go to answer_text.
            $table->string('answer_value', 32)->nullable();
            $table->text('answer_text')->nullable();
            $table->timestamps();

            $table->foreign('meeting_id')->references('id')->on('coffee_meetings')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // One answer per participant per question - re-submitting the form
            // updates the row instead of piling up duplicates.
            $table->unique(['meeting_id', 'user_id', 'question_key'], 'coffee_answers_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coffee_meeting_answers');
    }
};
