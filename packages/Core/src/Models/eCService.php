<?php

namespace Core\Models;

class eCService extends eCBaseModel
{
    protected $table = 's_service_config';
    protected $fillable = [
        "service_code",
        "service_name",
        "description",
        "service_type",
        "status",
        "price",
        "quantity",
        "expires_time",
        "delete_flag",
        "created_by",
        "updated_by",
    ];
    public function detail()
    {
        return $this->hasMany(eCServiceConfig::class, 'service_config_id');
    }
}
