<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Solution extends Model
{
    protected $fillable = [
      'title',
      'author',
      'content',
      'archive',
      'active',
    ];

    /**
     * The tags that belong to the Solution.
     */
    public function tags()
    {
        return $this->belongsToMany('App\Tag');
    }

    /**
     * The tags that belong to the Solution.
     */
    public function categories()
    {
        return $this->belongsToMany('App\Category');
    }
}
