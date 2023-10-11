<?php

namespace Core\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class eCBase2Model extends Model
{
    protected static function boot()
    {
        parent::boot();

        self::creating(function ($data) {
            $data->created_at = Carbon::now();
            $data->updated_at = Carbon::now();
        });

        self::saving(function ($data) {
            $data->updated_at = Carbon::now();
        });
    }
}
