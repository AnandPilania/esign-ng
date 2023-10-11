<?php

namespace Core\Models;

class eCVerifyTransactionLogs extends eCBase2Model
{
    protected $table = 'ec_verify_transaction_log';
    protected $fillable = [
        "code",
        "source",
        "content",
        "transaction_id",
    ];
}
