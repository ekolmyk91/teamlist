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

    static function get_count_vacation_days($id)
    {
        $used_days = OffTime::where('user_id', $id)
            ->where('status', 'approved')
            ->get(['start_day', 'end_day', 'type_id'])
            ->sortBy('start_day')->groupBy('type_id');

        $off_time_stat = [];

        foreach ( $used_days as $type_id => $days_by_type ) {

            $off_time_type = OffTimeType::find($type_id);
            $count         = 0;

            foreach ( $days_by_type as $vacation_period ) {
                $begin = new DateTime( $vacation_period->start_day );
                $end   = new DateTime( $vacation_period->end_day );
                for($i = $begin; $i <= $end; $i->modify('+1 day')){
                    if (!Calendar::isHoliday($i->format("Y-m-d"))) {
                        $count++;
                    }
                }
            }

            $off_time_stat['used'][$off_time_type->name] = $count;
            $off_time_stat['rest'][$off_time_type->name] = $off_time_type->days_per_year - $count;
        }
        return $off_time_stat;
    }
}
