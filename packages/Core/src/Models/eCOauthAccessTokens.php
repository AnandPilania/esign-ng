<?php
/**
 * Created by IntelliJ IDEA.
 * User: namnv
 * Date: 8/17/18
 * Time: 11:08 PM
 */

namespace Core\Models;

use Illuminate\Database\Eloquent\Model;

class eCOauthAccessTokens extends Model
{

    public $table = 'oauth_access_tokens';

    public $timestamps = false;

    public $fillable = [
        'user_id',
        'client_id',
        'name',
        'scopes',
        'revoked',
        'created_at',
        'updated_at',
        'expires_at',
        'token'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'string',
        'user_id' => 'integer',
        'client_id' => 'integer',
        'name' => 'string',
        'scopes' => 'string',
        'revoked' => 'boolean',
        'token' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];


}
