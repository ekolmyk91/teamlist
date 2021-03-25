<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
      'user_id',
      'name',
      'surname',
      'birthday',
      'start_work_day',
      'email',
      'phone_1',
      'phone_2',
      'department_id',
      'position_id',
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
    public function department()
    {
        return $this->belongsTo('App\Department');
    }

    /**
     * Get the position related to the member.
     */
    public function position()
    {
        return $this->belongsTo('App\Position');
    }

    /**
     * Get the certificates related to the member.
     */
    public function certificates()
    {
        return $this->belongsToMany(Certificate::class, 'certificate_member', 'member', 'certificate');
    }

    /**
     * Get a list of members depending on the date of birth or employment.
     */
    public function getMembersListAccordingDate($typeDay, $monthNumber)
    {

        return $this->select(['user_id', 'members.name', 'surname', "$typeDay"])
                    ->join('users', 'members.user_id', '=', 'users.id')
                    ->where('active', 1)
                    ->whereMonth("$typeDay", '=', $monthNumber)
                    ->orderByRaw($typeDay .' asc')
                    ->get();
    }
}
