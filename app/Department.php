<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name'];

    /**
     * Get the members for the department.
     */
    public function members()
    {
//        return $this->hasMany('App\Member');

        return $this->belongsToMany('App\Member')
                    ->using('App\DepartmentMember')
                    ->withPivot([
                      'created_by',
                      'updated_by'
                    ]);
    }
}
