<?php

namespace Core\Models;

class eCActivities extends eCBase2Model
{
    protected $table = 'ec_s_activities';
    protected $fillable = [
        "company_id",
        "action_group",
        "action_type",
        "data_table",
        "action",
        "name",
        "email",
        "note",
        "raw_log",
        "created_by",
    ];
}
