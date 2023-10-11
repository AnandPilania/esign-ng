<?php

namespace Core\Models;

use Carbon\Carbon;

class eCDocumentSignatureKyc extends eCBase2Model
{
    protected $table = 'ec_document_signature_kyc';
    protected $fillable = [
        "assign_id",
        "sign_type",
        "x509_certificate",
        "signed_at",
        "password",
        "pri_key",
        "pub_key",
        "image_signature",
        "front_image_url",
        "back_image_url",
        "face_image_url",
        "created_by",
        "updated_by",
        "national_id",
        "birthday",
        "sex",
        "hometown",
        "address",
        "issueDate",
        "issueBy",
        "sim",
    ];
}
