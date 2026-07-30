<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoffeeMeetingAnswer extends Model
{
    protected $table = 'coffee_meeting_answers';

    protected $fillable = [
        'meeting_id',
        'user_id',
        'question_key',
        'question_label',
        'answer_value',
        'answer_text',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(CoffeeMeeting::class, 'meeting_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
