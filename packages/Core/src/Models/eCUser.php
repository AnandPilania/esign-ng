<?php

namespace Core\Models;

use App\User;
use Carbon\Carbon;
use Core\Casts\Base64;
use Core\Models\Scopes\DeleteFlagScope;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class eCUser extends User
{
    use Notifiable;
    use HasApiTokens;

    protected $table = 'ec_users';
    protected $casts = [
        'name' => Base64::class,
        'email' => Base64::class,
        'phone' => Base64::class
    ];
    protected $fillable = [
        "company_id",
        "name",
        "email",
        "password",
        "phone",
        "dob",
        "address",
        "note",
        "sex",
        "otp",
        "avatar",
        "language",
        "lasted_active",
        "is_personal",
        "status",
        "role_id",
        "is_first_login",
        "remember_token",
        "expiration_time",
        "source",
        "branch_id",
        "delete_flag",
        "created_by",
        "updated_by",
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
    public function company()
    {
        return $this->belongsTo(eCCompany::class, 'company_id');
    }
    public function role()
    {
        return $this->belongsTo(eCRole::class, 'role_id');
    }
}
