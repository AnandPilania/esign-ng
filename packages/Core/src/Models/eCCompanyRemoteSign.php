<?php

namespace Core\Models;

class eCCompanyRemoteSign extends eCBaseModel
{
    protected $table = 'ec_company_remote_sign';
    protected $fillable = [
        "company_id",
        "provider",
        "service_signing",
        "login",
        "password",
        "status",
        "delete_flag",
        "updated_by",
        "created_by",
    ];
}
