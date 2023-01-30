<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OffTimeType extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['name', 'days_per_year'];

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

    /**
     * check is request off-time vacation
     *
     * @param $id
     * @return bool
     */
    static function isVacation($id): bool
    {
        return 'vacation' === strtolower( OffTimeType::find($id)->name );
    }
}
