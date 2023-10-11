<?php

namespace Core\Models;

class eCRolePermission extends eCBase2Model
{
    protected $table = 'ec_s_role_permission';
    protected $fillable = [
        "role_id",
        "permission_id",
        "is_view",
        "is_write",
        "is_approval",
        "is_decision",
        "created_at",
        "updated_at",
    ];
    public function role()
    {
        return $this->belongsTo(eCRolePermission::class, 'role_id');
    }
}
