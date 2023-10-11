<?php

namespace Core\Models;

class eCConversationTemplates extends eCBase2Model
{
    protected $table = 'ec_s_conversation_templates';
    protected $fillable = [
        "template_name",
        "template_description",
        "template",
        "type",
        "status",
        "is_ams",
    ];
}
