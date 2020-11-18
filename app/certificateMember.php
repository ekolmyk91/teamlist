<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class certificateMember extends Model
{
    protected $table = 'certificate_member';

    public function Member()
    {
        return $this->belongsTo(Member::class, 'member');
    }
}
