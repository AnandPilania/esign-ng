<?php

namespace Core\Models;

use Carbon\Carbon;
use Core\Models\Scopes\DeleteFlagScope;
use Core\Traits\Versionable;
use Illuminate\Database\Eloquent\Model;

class eCDocuments extends eCBaseModel
{
    use Versionable;
    protected $table = 'ec_documents';
    protected $fillable = [
        "company_id",
        "document_type",
        "document_type_id",
        "parent_id",
        "addendum_type",
        "status",
        "is_order_approval",
        "is_verify_content",
        "is_request_confirmed",
        "is_request_org_confirmed",
        "document_draft_state",
        "document_state",
        "sent_date",
        "expired_date",
        "finished_date",
        "name",
        "code",
        "expired_type",
        "doc_expired_date",
        "expired_month",
        "transaction_id",
        "description",
        "version",
        "delete_flag",
        "remimder_type",
        "current_assignee_id",
        "customer_id",
        "branch_id",
        "source",
        "branch_id",
        "document_sample_id",
        "created_by",
        "updated_by",
        "source_method",
        "is_encrypt",
        "save_password",
//        "use_file_attachment",//
    ];
    public function assignee()
    {
        return $this->hasMany(eCDocumentAssignee::class, 'document_id', 'id');
    }

    public function documentType() {
        return $this->belongsTo(eCDocumentTypes::class, 'document_type_id');
    }
}
