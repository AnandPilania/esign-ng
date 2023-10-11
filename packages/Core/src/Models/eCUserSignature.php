<?php

namespace Core\Models;

class eCUserSignature extends eCBase2Model
{
    protected $table = 'ec_user_signature';
    protected $fillable = [
        "user_id",
        "image_signature",
    ];
}
