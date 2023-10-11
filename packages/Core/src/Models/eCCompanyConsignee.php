<?php

namespace Core\Models;

class eCCompanyConsignee extends eCBaseModel
{
    protected $table = 'ec_company_consignees';
    protected $fillable = [
        "company_id",
        "name",
        "email",
        "phone",
        "role",
        "status",
        "delete_flag",
        "updated_by",
        "created_by",
    ];
}
