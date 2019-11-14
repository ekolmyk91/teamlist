<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
      'name',
      'surname',
      'birthday',
      'email',
      'phone_1',
      'phone_2',
      'about'
    ];
}
