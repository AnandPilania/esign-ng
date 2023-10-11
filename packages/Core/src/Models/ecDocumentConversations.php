<?php

namespace Core\Models;

use Carbon\Carbon;
use Core\Models\Scopes\DeleteFlagScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ecDocumentConversations extends eCBaseModel
{
    protected $table = 'ec_document_conversations';
    protected $fillable = [
        "company_id",
        "document_id",
        "notify_type",
        "send_id",
        "send_type",
        "content",
        "template_id",
        "status",
        "delete_flag",
        "created_by",
        "updated_by",
    ];
}
