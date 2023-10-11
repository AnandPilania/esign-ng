<?php

namespace Core\Models;

class eCSearcherSignature extends eCBase2Model
{
    protected $table = 'ec_searcher_signature';
    protected $fillable = [
        "searcher_id",
        "image_signature",
    ];
}
