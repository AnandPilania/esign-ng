<?php

namespace Core\Models;
use Core\Models\Scopes\DeleteFlagScope;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Core\Casts\Base64;
use Tymon\JWTAuth\Contracts\JWTSubject;

class eCVendor extends Authenticatable implements JWTSubject
{

    protected $table = 'ec_vendors';

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
    }
    /**
     * The attributes that should be cast.
     *
     * @var array
     */

    protected $casts = [
        'vendor_name' => Base64::class
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
