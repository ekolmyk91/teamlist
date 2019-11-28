<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * The solutions that belong to the Tag.
     */
    public function solutions()
    {
        return $this->belongsToMany('App\Solution');
    }
}
