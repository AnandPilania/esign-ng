<?php

namespace Core\Models;
use Core\Traits\Versionable;
class eCGuideVideo extends eCBaseModel
{
    use Versionable;

    protected $table = 'ec_guide_video';
    protected $fillable = [
        "status",
        "name",
        "description",
        "link",
        "version",
        "delete_flag",
        "created_by",
        "updated_by",
    ];
}
