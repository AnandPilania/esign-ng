<?php

namespace Core\Models;
use Core\Traits\Versionable;
class eCDocumentTutorialResources extends eCBase2Model
{
    use Versionable;

    protected $table = 'ec_tutorial_document_resources';
    protected $fillable = [
        "document_tutorial_id",
        "file_name_raw",
        "file_type_raw",
        "file_size_raw",
        "file_path_raw",
        "file_id",
        "status",
        "created_by",
        "updated_by",
    ];
    public  function tutorial()
    {
        return $this->belongsTo(eCDocumentTutorial::class, 'document_tutorial_id');
    }
}
