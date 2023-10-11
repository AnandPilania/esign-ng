<?php

namespace Core\Models;

class eCAmsRole extends eCBaseModel
{
    protected $table = 'ec_ams_roles';
    protected $fillable = [
        "role_name",
        "created_by",
        "updated_by",
        "delete_flag",
    ];
}
