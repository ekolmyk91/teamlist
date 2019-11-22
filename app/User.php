<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'name',
      'email',
      'password',
      'avatar',
      'active',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany('App\Role');
    }

    /**
     * Get the phone record associated with the user.
     */
    public function member()
    {
        return $this->hasOne('App\Member');
    }


    /**
     * Check if user has choosen role.
     */
    public function hasRole($role)
    {
        $roles = $this->roles()->where('name', $role)->count();

        if($roles == 1){
            return true;
        }

        return false;
    }

    public static function generatePassword()
    {
        // Generate random string and encrypt it.
        return bcrypt(Str::random(35));
    }
}
