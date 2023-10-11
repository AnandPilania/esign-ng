<?php

namespace Core\Models;

class eCPermissions extends eCBase2Model
{
    protected $table = 'ec_s_permissions';
    protected $fillable = [
        "permission",
        "note",
        "parent_permission",
        "is_view",
        "is_write",
        "is_approval",
        "is_decision",
        "status",
        "created_by",
        "updated_by",
    ];
}
