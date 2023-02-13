<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    protected $table = 'calendar';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = ['is_weekday', 'is_holiday'];

    /**
     * Check day is holiday or not.
     *
     * @param $date
     * @return bool
     */
    static function isHoliday($date)
    {
        $day = Calendar::where('dt', $date)->first();

        return empty($day->is_weekday) || !empty($day->is_holiday);
    }
}
