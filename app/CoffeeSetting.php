<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;

class CoffeeSetting extends Model
{
    private const SINGLETON_ID = 1;

    protected $fillable = [
        'id',
        'enabled',
        'frequency_weeks',
        'matching_day',
        'meeting_time',
        'meeting_duration',
        'work_time_from',
        'work_time_to',
        'exclusion_weeks',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'frequency_weeks' => 'integer',
        'matching_day' => 'integer',
        'meeting_duration' => 'integer',
        'exclusion_weeks' => 'integer',
    ];

    /**
     * The single settings row (created with defaults on first access).
     */
    public static function current(): self
    {
        $settings = static::query()->find(self::SINGLETON_ID);

        if ($settings !== null) {
            return $settings;
        }

        // createOrFirst survives a concurrent insert of the same fixed id;
        // refresh() pulls the DB column defaults into the freshly created row.
        return static::query()->createOrFirst(['id' => self::SINGLETON_ID])->refresh();
    }
}