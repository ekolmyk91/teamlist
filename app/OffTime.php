<?php

namespace App;

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
}
