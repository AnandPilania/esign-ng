<?php

namespace Core\Models;

use Carbon\Carbon;

class eCDocumentSignature extends eCBase2Model
{
    protected $table = 'ec_document_signature';
    protected $fillable = [
        "document_id",
        "assign_id",
        "page_sign",
        "width_size",
        "height_size",
        "page_width",
        "page_height",
        "x",
        "y",
        "is_auto_sign",
        "created_by",
        "updated_by",
    ];
}
