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
}
