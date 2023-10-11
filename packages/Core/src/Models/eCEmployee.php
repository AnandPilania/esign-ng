<?php

namespace Core\Models;

class eCEmployee extends eCBaseModel
{
    protected $table = 'ec_s_employees';
    protected $fillable = [
        "company_id",
        "department_id",
        "position_id",
        "emp_code",
        "emp_name",
        "reference_code",
        "dob",
        "sex",
        "ethnic",
        "nationality",
        "province",
        "address1",
        "address2",
        "national_id",
        "national_date",
        "national_address_provide",
        "degree",
        "degree_subject",
        "contract_type",
        "contract_duration",
        "contract_bg",
        "contract_ed",
        "address_office",
        "working_time",
        "salary",
        "salary_base",
        "salary_extra",
        "salary_bonus",
        "salary_bonus_extra",
        "email",
        "phone",
        "note",
        "status",
        "delete_flag",
        "created_by",
        "updated_by",
    ];
}
