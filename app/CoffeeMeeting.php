<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class CoffeeMeeting extends Model
{
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_HELD = 'held';
    public const STATUS_NOT_HELD = 'not_held';

    public const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_HELD,
        self::STATUS_NOT_HELD,
    ];

    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'cycle_date',
        'scheduled_at',
        'meeting_url',
        'user_one_attended',
        'user_two_attended',
        'user_one_asked_at',
        'user_two_asked_at',
        'status',
    ];

    protected $casts = [
        'cycle_date' => 'date',
        'scheduled_at' => 'datetime',
        'user_one_attended' => 'boolean',
        'user_two_attended' => 'boolean',
        'user_one_asked_at' => 'datetime',
        'user_two_asked_at' => 'datetime',
    ];

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(CoffeeMeetingAnswer::class, 'meeting_id');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $query) use ($userId) {
            $query->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
        });
    }

    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function hasParticipant(int $userId): bool
    {
        return (int) $this->user_one_id === $userId || (int) $this->user_two_id === $userId;
    }

    /**
     * This participant's "did it happen?" answer: true, false, or null when
     * they have not answered yet. Callers must check hasParticipant() first -
     * for an outsider the value of the second participant would be returned.
     */
    public function attendanceOf(int $userId): ?bool
    {
        $column = (int) $this->user_one_id === $userId ? 'user_one_attended' : 'user_two_attended';

        return $this->{$column} === null ? null : (bool) $this->{$column};
    }

    /**
     * Store one participant's "did the meeting happen?" answer and refresh
     * status. Runs under a row lock so two simultaneous answers cannot both
     * recompute the status from a stale snapshot.
     */
    public function applyAnswer(int $userId, bool $attended): void
    {
        DB::transaction(function () use ($userId, $attended) {
            /** @var self $meeting */
            $meeting = self::query()->lockForUpdate()->findOrFail($this->id);

            $column = (int) $meeting->user_one_id === $userId ? 'user_one_attended' : 'user_two_attended';
            $meeting->{$column} = $attended;
            $meeting->status = $meeting->resolveStatus();
            $meeting->save();

            $this->setRawAttributes($meeting->getAttributes(), true);
        });
    }

    /**
     * Status from the two attendance answers. A single answer already decides
     * it - waiting for the partner would leave the meeting "scheduled" long
     * after it is over, and the partner may never click at all.
     *
     * "It happened" wins over "it did not": the one who showed up knows more
     * than the one who forgot, and a meeting one side confirms is held.
     * Nothing answered yet - still scheduled.
     */
    private function resolveStatus(): string
    {
        if ($this->user_one_attended === true || $this->user_two_attended === true) {
            return self::STATUS_HELD;
        }

        if ($this->user_one_attended === false || $this->user_two_attended === false) {
            return self::STATUS_NOT_HELD;
        }

        return self::STATUS_SCHEDULED;
    }
}