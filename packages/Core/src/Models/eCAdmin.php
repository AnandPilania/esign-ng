<?php

namespace Core\Models;
use Core\Models\Scopes\DeleteFlagScope;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Core\Casts\Base64;
use Tymon\JWTAuth\Contracts\JWTSubject;

class eCAdmin extends Authenticatable implements JWTSubject
{

    protected $table = 'ec_admins';
    protected $fillable = [
        "email",
        "password",
        "full_name",
        "address",
        "sex",
        "note",
        "dob",
        "phone",
        "status",
        "is_first_login",
        "latest_active",
        "remember_token",
        "delete_flag",
        "created_by",
        "updated_by",
        "role_id",
        "agency_id",
        "language",
    ];
    const ADMIN = 1;
    /**
     * Admin constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * DeleteFlagScope
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new DeleteFlagScope);
    }
    /**
     * The attributes that should be cast.
     *
     * @var array
     */

    protected $casts = [
        'full_name' => Base64::class,
        'email' => Base64::class,
        'phone' => Base64::class
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function agency()
    {
        return $this->belongsTo(eCAgencies::class, 'agency_id');
    }
}
