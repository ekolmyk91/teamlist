<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
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
     * Get the members for the position.
     */
    public function members()
    {
        return $this->hasMany('App\Member');

    }
}
