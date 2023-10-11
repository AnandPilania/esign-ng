<?php

namespace Core\Models;

use Core\Traits\Versionable;

class eCDocumentSampleResources extends eCBase2Model
{
    use Versionable;

    protected $table = 'ec_s_document_sample_resources';
    protected $fillable = [
        "document_sample_id",
        "file_name_raw",
        "file_type_raw",
        "file_size_raw",
        "file_path_raw",
        "file_id",
        "status",
        "created_by",
        "updated_by",
    ];

    public function sample() {
        return $this->belongsTo(eCDocumentSample::class, 'document_sample_id');
    }
}
