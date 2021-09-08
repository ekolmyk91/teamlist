<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Nicolaslopezj\Searchable\SearchableTrait;

class Member extends Model {

    use SearchableTrait;

    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchable = [
        /**
         * Columns and their priority in search results.
         * Columns with higher values are more important.
         * Columns with equal values have equal importance.
         *
         * @var array
         */
        'columns' => [
            'members.name' => 10,
            'members.surname' => 10,
            'members.email' => 10,
            ]
    ];


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
	  'trainee',
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
        if ( 'birthday' == $typeDay) {
            return DB::select('SELECT user_id, members.name, surname, date_format(members.birthday, \'%d/%m\') as formatted_birthday
                                    FROM members
                                    JOIN users ON users.id = members.user_id
                                    WHERE users.active = 1 AND MONTH(birthday) = :monthNumber ORDER BY formatted_birthday', [$monthNumber]);
        } elseif ('start_work_day' == $typeDay) {
            return DB::select('SELECT user_id, members.name, surname, (YEAR(CURDATE()) - date_format(members.start_work_day, \'%Y\')) as exp_years
                                    FROM members
                                    JOIN users ON users.id = members.user_id
                                    WHERE users.active = 1 AND MONTH(start_work_day) = :monthNumber ORDER BY exp_years DESC', [$monthNumber]);
        }
    }
}
