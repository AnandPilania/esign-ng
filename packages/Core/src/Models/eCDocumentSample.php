<?php

namespace Core\Models;

use Core\Traits\Versionable;

class eCDocumentSample extends eCBaseModel
{

    protected $table = 'ec_s_document_samples';
    protected $fillable = [
        "company_id",
        "document_type",
        "document_type_id",
        "name",
        "description",
        "expired_type",
        "expired_month",
        "is_verify_content",
        "document_path_original",
        "is_encrypt",
        "save_password",
        "delete_flag",
        "created_by",
        "updated_by",
    ];

    public function resources() {
        return $this->hasMany(eCDocumentSampleResources::class, 'document_sample_id');
    }
    public function sample_info() {
        return $this->hasMany(eCDocumentSampleInfo::class, 'document_sample_id');
    }
}
