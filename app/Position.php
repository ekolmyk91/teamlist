<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use SoftDeletes;
    /**
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * @var bool
     */
    protected $softDelete = true;

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
