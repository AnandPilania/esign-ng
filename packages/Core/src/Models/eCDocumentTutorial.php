<?php

namespace Core\Models;
use Core\Traits\Versionable;
class eCDocumentTutorial extends eCBaseModel
{
    use Versionable;

    protected $table = 'ec_tutorial_documents';
    protected $fillable = [
        "status",
        "name",
        "description",
        "version",
        "delete_flag",
        "created_by",
        "updated_by",
    ];
    public  function resource()
    {
        return $this->hasOne(eCDocumentTutorialResources::class, 'document_tutorial_id');
    }
}
