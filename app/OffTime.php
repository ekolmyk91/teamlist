<?php

namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;

class OffTime extends Model
{

    protected $table = 'off_time';

    /**
     * @var array
     */
    protected $fillable = ['start_day', 'end_day', 'user_id', 'type_id', 'status'];

    /**
     * Get the user that owns the off-time.
     */
    public function member()
    {
        return $this->belongsTo('App\Member', 'user_id', 'user_id');
    }

    /**
     * Get the off_time_type related to the off_time item.
     */
    public function offTimeType()
    {
        return $this->belongsTo('App\OffTimeType', 'type_id', 'id');
    }

    /**
     * Get off time days count by user_id
     *
     * @param $user_id
     * @return array
     * @throws \Exception
     */
    static function getOffTimeDaysCount($user_id)
    {
        $usedDays = OffTime::where('user_id', $user_id)
            ->where('status', 'approved')
            ->get(['start_day', 'end_day', 'type_id'])
            ->sortBy('start_day')->groupBy('type_id');

        $statistic = [];

        foreach ( $usedDays as $typeID => $daysByType ) {

            $offTimeType = OffTimeType::find($typeID);
            $count       = 0;

            foreach ( $daysByType as $offTimePeriod ) {
                $begin = new DateTime( $offTimePeriod->start_day );
                $end   = new DateTime( $offTimePeriod->end_day );
                for($i = $begin; $i <= $end; $i->modify('+1 day')){
                    if (!Calendar::isHoliday($i->format("Y-m-d"))) {
                        $count++;
                    }
                }
            }

            $statistic['used'][$offTimeType->name] = $count;
            $statistic['rest'][$offTimeType->name] = $offTimeType->days_per_year - $count;
        }

        $offTimeTypes = OffTimeType::all();

        if ( ! empty( $offTimeTypes ) ) {

            foreach ( $offTimeTypes as $typeItem ) {
                if ( empty( $statistic['rest'] ) || ! array_key_exists( $typeItem->name, $statistic['rest'] ) ) {
                    $statistic['rest'][$typeItem->name] = $typeItem->days_per_year;
                }
                if ( empty( $statistic['used'] ) || ! array_key_exists( $typeItem->name, $statistic['used'] ) ) {
                    $statistic['used'][$typeItem->name] = 0;
                }
            }
        }

        return $statistic;
    }
}
