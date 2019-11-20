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

    protected $primaryKey = 'user_id';

    /**
     * Get the user that owns the phone.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get the department related to the member.
     */
    public function departmentMember()
    {
        return $this->belongsTo('App\Department');
    }
}
