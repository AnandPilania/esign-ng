<?php

namespace Core\Models;

class eCDocumentLogs extends eCBase2Model
{
    protected $table = 'ec_document_logs';
    protected $fillable = [
        "prev_status",
        "document_id",
        "content",
        "action",
        "is_show",
        "next_status",
        "note",
        "action_by_email",
        "created_by",
    ];
}
