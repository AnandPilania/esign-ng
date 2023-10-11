<?php

namespace Core\Models;

use App\User;
use Carbon\Carbon;
use Core\Casts\Base64;
use Core\Models\Scopes\DeleteFlagScope;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class eCDocumentHsm extends eCBase2Model
{
    protected $table = 'ec_document_hsm';
}
