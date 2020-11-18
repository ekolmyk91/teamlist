<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['name', 'logo'];

    /**
     * Get the certificates logo path.
     *
     * @param  string  $value
     * @return string
     */
    public function getLogoAttribute($value)
    {
        return '/storage/logo/' . $value;
    }

    /**
     * Get the members related to the certificate.
     */
    public function members()
    {
        return $this->belongsToMany(Member::class, 'certificate_member', 'certificate', 'member');
    }
}
