<?php

namespace Core\Models;

class eCCompanyConversationTemplates extends eCBaseModel
{
    protected $table = 'ec_s_company_conversation_templates';
    protected $fillable = [
        "template_id",
        "company_id",
        "template",
        "type",
        "status",
        "created_by",
        "updated_by",
    ];
}
