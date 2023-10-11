<?php

namespace Core\Models;

use Carbon\Carbon;
use Core\Models\Scopes\DeleteFlagScope;

class eCDocumentResourcesEx extends eCBaseModel
{
    protected $table = 'ec_document_resources_ex';
    protected $fillable = [
        "document_id",
        "company_id",
        "parent_id",
        "document_path_original",
        "document_path_sign",
        "created_by",
        "created_at",
        "updated_at",
        "delete_flag",
        "hash",
        "save_password",
        "document_watermark",
    ];
}
