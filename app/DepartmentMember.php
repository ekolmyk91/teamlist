<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DepartmentMember extends Model
{
    protected $fillable = ['name'];

    /**
     * Get the members for the department.
     */
    public function members()
    {
        return $this->hasMany('App\Member');
    }
}
