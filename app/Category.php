<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
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
     * The solutions that belong to the Category.
     */
    public function solutions()
    {
        return $this->belongsToMany('App\Solution');
    }
}
