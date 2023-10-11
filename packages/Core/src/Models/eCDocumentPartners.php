<?php

namespace Core\Models;

use Carbon\Carbon;

class eCDocumentPartners extends eCBase2Model
{
    protected $table = 'ec_document_partners';
    protected $fillable = [
        "document_id",
        "order_assignee",
        "organisation_type",
        "company_name",
        "code",
        "tax",
        "email",
        "address",
        "phone",
        "bank",
        "bank_no",
        "representative",
        "representative_position",
        "status",
        "created_by",
        "updated_by",
        "created_at",
        "updated_at",
        "organisation_name",
    ];

    public function assignee()
    {
        return $this->hasMany(eCDocumentAssignee::class, 'partner_id', 'id');
    }

}
