<?php

namespace Core\Models;

class eCDocumentGroups extends eCBaseModel
{
    protected $table = 'ec_m_document_types';
    protected $fillable = [
        "name",
        "status",
        "delete_flag",
    ];
}
