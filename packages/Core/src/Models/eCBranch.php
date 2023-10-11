<?php

namespace Core\Models;

class eCBranch extends eCBaseModel
{
    protected $table = 'ec_branches';
    protected $fillable = [
        "company_id",
        "tax_number",
        "branch_code",
        "name",
        "address",
        "phone",
        "email",
        "status",
        "created_by",
        "updated_by",
        "delete_flag",
    ];
}
