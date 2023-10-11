<?php

namespace Core\Models;

class eCSendMail extends eCBase2Model
{
    protected $table = 'ec_s_send_email';
    protected $fillable = [
        "company_id",
        "email_host",
        "email_protocol",
        "email_address",
        "email_password",
        "email_name",
        "port",
        "is_use_ssl",
        "is_relay",
        "status",
        "created_by",
        "updated_by",
    ];
}
