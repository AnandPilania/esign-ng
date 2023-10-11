<?php

namespace Core\Models;

use Carbon\Carbon;
use Core\Models\Scopes\DeleteFlagScope;

class eCDocumentResources extends eCBase2Model
{
    protected $table = 'ec_document_resources';
    protected $fillable = [
        "document_id",
        "parent_id",
        "file_name_raw",
        "file_type_raw",
        "file_size_raw",
        "file_path_raw",
        "file_id",
        "created_by",
        "updated_by",
        "created_at",
        "updated_at",
    ];

}
