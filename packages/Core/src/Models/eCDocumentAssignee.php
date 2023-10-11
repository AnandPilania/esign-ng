<?php


namespace Core\Models;


use Carbon\Carbon;
use Core\Models\Scopes\DeleteFlagScope;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class eCDocumentAssignee extends Authenticatable implements JWTSubject
{
    protected $table = 'ec_document_assignees';
    protected $fillable = [
        "company_id",
        "full_name",
        "email",
        "phone",
        "national_id",
        "address",
        "ext_info",
        "document_id",
        "partner_id",
        "message",
        "noti_type",
        "order",
        'status',
        "state",
        "is_internal",
        "reason",
        "submit_time",
        "assign_type",
        "sign_method",
        "is_required",
        "is_auto_sign",
        "password",
        "url_code",
        "otp",
        "user_id",
        "credential_id",
        "created_by",
        "updated_by",
        "created_at",
        "updated_at",
        "agree",
    ];
    protected $hidden = [
        'password'
    ];
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new DeleteFlagScope);

        self::creating(function ($data) {
            $data->created_at = Carbon::now();
            $data->updated_at = Carbon::now();
        });

        self::saving(function ($data) {
            $data->updated_at = Carbon::now();
        });
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function document()
    {
        return $this->belongsTo(eCDocuments::class, 'document_id');
    }
    public function partner()
    {
        return $this->belongsTo(eCDocumentPartners::class, 'partner_id');
    }

}
