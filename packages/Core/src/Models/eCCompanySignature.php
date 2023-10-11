<?php

namespace Core\Models;

use Carbon\Carbon;

class eCCompanySignature extends eCBase2Model
{
    protected $table = 'ec_company_signature';
    protected $fillable = [
        "company_id",
        "image_signature",
        "created_by",
        "updated_by",
    ];
}
