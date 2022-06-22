<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OffTimeType extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the off-time item for the type.
     */
    public function offTimeList()
    {
        return $this->hasMany('App\OffTime');

    }
}
