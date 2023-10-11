<?php

namespace Core\Models;

use Carbon\Carbon;

class eCDocumentTextInfo extends eCBase2Model
{
    protected $table = 'ec_document_text_info';
    protected $fillable = [
        "document_id",
        "matruong",
        "data_type",
        "content",
        "font_size",
        "font_style",
        "page_sign",
        "width_size",
        "height_size",
        "x",
        "y",
        "page_width",
        "page_height",
        "created_by",
        "updated_by",
    ];
}
