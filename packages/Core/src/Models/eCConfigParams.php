<?php

namespace Core\Models;

class eCConfigParams extends eCBase2Model
{
    protected $table = 'ec_s_config_params';
    protected $fillable = [
        "company_id",
        "send_email_remind_day",
        "near_expired_date",
        "near_doc_expired_date",
        "document_expire_day",
    ];
}
