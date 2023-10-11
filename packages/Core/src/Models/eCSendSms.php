<?php

namespace Core\Models;

class eCSendSms extends eCBase2Model
{
    protected $table = 'ec_s_send_sms';
    protected $fillable = [
        "company_id",
        "service_provider",
        "service_url",
        "brandname",
        "sms_account",
        "sms_password",
        "status",
        "created_by",
        "updated_by",
    ];
}
