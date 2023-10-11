<?php

namespace Core\Models;

use Core\Casts\Base64;

class eCAgencies extends eCBaseModel
{
    protected $table = 'ec_agencies';
    protected $casts = [
        'agency_name' => Base64::class,
        'agency_phone' => Base64::class,
        'agency_email' => Base64::class
    ];
    protected $fillable = [
        "agency_name",
        "agency_phone",
        "agency_fax",
        "agency_email",
        "agency_address",
        "status",
        "state",
        "delete_flag",
        "created_by",
        "updated_by",
        "version",
    ];

    public function admin()
    {
        return $this->hasMany(eCAdmin::class, 'agency_id');
    }
}
