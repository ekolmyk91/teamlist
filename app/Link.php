<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['title', 'url', 'icon', 'order_number'];

    /**
     * @var bool
     */
    public $timestamps = false;
}
