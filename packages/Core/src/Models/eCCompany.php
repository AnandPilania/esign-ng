<?php

namespace Core\Models;

class eCCompany extends eCBaseModel
{
    protected $table = 'ec_companies';
    protected $fillable = [
        "agency_id",
        "service_id",
        "name",
        "company_code",
        "source_method",
        "tax_number",
        "sign_type",
        "fax_number",
        "address",
        "phone",
        "email",
        "website",
        "representative",
        "representative_position",
        "bank_info",
        "bank_number",
        "contact_name",
        "contact_phone",
        "contact_email",
        "state",
        "total_doc",
        "expired_date",
        "status",
        "version",
        "approved_by",
        "created_by",
        "updated_by",
        "source",
        "delete_flag",
    ];
    public function config()
    {
        return $this->hasOne(eCCompanyConfig::class, 'company_id');
    }
    public function role()
    {
        return $this->hasMany(eCRole::class, 'company_id');
    }
    public function user()
    {
        return $this->hasMany(eCUser::class, 'company_id');
    }
}
