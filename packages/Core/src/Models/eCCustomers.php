<?php

namespace Core\Models;

class eCCustomers extends eCBaseModel
{
    protected $table = 'ec_s_customers';
    protected $fillable = [
        "company_id",
        "name",
        "code",
        "tax_number",
        "address",
        "phone",
        "email",
        "bank_info",
        "bank_account",
        "bank_number",
        "representative",
        "representative_position",
        "contact_name",
        "contact_phone",
        "note",
        "customer_type",
        "status",
        "delete_flag",
        "created_by",
        "updated_by",
    ];
}
