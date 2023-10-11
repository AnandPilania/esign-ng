<?php

namespace Core\Models;

class eCDocumentTypes extends eCBaseModel
{
    protected $table = 's_document_types';
    protected $fillable = [
        "company_id",
        "document_group_id",
        "dc_type_code",
        "dc_type_name",
        "dc_style",
        "is_auto_reset",
        "is_order_auto",
        "dc_length",
        "dc_format",
        "note",
        "status",
        "delete_flag",
        "created_by",
        "updated_by",
    ];
}
