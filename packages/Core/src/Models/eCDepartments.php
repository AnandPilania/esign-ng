<?php

namespace Core\Models;

class eCDepartments extends eCBaseModel
{
    protected $table = 'ec_s_departments';
    protected $fillable = [
        "company_id",
        "department_code",
        "name",
        "note",
        "status",
        "delete_flag",
        "created_by",
        "updated_by",
    ];
}
