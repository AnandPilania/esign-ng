<?php

namespace Core\Models;

class eCPositions extends eCBaseModel
{
    protected $table = 'ec_s_positions';
    protected $fillable = [
        "company_id",
        "name",
        "position_code",
        "note",
        "status",
        "delete_flag",
        "created_by",
        "updated_by",
    ];
}
