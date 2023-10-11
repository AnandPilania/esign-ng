<?php

namespace Core\Models;

class eCServiceConfig extends eCBaseModel
{
    protected $table = 's_service_config_detail';
    protected $fillable = [
        "service_config_id",
        "from",
        "to",
        "fee",
        "delete_flag",
        "created_by",
        "updated_by",
    ];
    public function service()
    {
        return $this->belongsTo(eCService::class, 'service_config_id');
    }
}
