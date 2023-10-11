<?php

namespace Core\Models;

class eCRole extends eCBaseModel
{
    protected $table = 'ec_s_roles';
    protected $fillable = [
        "company_id",
        "role_name",
        "note",
        "status",
        "delete_flag",
        "created_by",
        "updated_by",
    ];

    public function rolePermission()
    {
        return $this->hasMany(eCRolePermission::class, 'role_id');
    }
    public function company()
    {
        return $this->belongsTo(eCCompany::class, 'company_id');
    }
}
