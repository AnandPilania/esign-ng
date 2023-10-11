<?php

namespace Core\Models;

class eCDocumentSampleInfo extends eCBaseModel
{
    protected $table = 'ec_s_document_sample_info';
    protected $fillable = [
        "document_sample_id",
        "data_type",
        "content",
        "description",
        "is_required",
        "is_editable",
        "form_name",
        "field_code",
        "form_description",
        "font_size",
        "font_style",
        "page_sign",
        "width_size",
        "height_size",
        "x",
        "y",
        "page_width",
        "page_height",
        "order_assignee",
        "is_my_organisation",
        "is_auto_sign",
        "sign_method",
        "image_signature",
        "full_name",
        "email",
        "phone",
        "national_id",
        "noti_type",
        "delete_flag",
        "created_by",
        "updated_by",
    ];
    public function sample() {
        return $this->belongsTo(eCDocumentSample::class, 'document_sample_id');
    }
}
