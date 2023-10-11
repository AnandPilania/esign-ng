<?php

namespace Core\Models;
use Core\Models\Scopes\DeleteFlagScope;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Core\Casts\Base64;
use Tymon\JWTAuth\Contracts\JWTSubject;

class eCSearcher extends Authenticatable implements JWTSubject
{

    protected $table = 'ec_searchers';
    protected $fillable = [
        "name",
        "email",
        "password",
        "phone",
        "dob",
        "address",
        "sex",
        "language",
        "is_first_login",
        "status",
        "remember_token",
        "delete_flag",
        "expiration_time",
        "source",
    ];

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
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $casts = [
        'email' => Base64::class,
    ];
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
}
