<?php

namespace Core\Models;

use Carbon\Carbon;
use Core\Models\Scopes\DeleteFlagScope;
use Illuminate\Database\Eloquent\Model;

class eCBaseModel extends Model
{
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
}
