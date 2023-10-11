<?php

namespace Core\Models;

use Core\Traits\Versionable;

class eCCompanyConfig extends eCBase2Model
{
    use Versionable;

    protected $table = 'ec_s_company_config';
    protected $fillable = [
        "company_id",
        "logo_dashboard",
        "logo_login",
        "logo_sign",
        "logo_background",
        "fa_icon",
        "theme_header_color",
        "theme_footer_color",
        "file_size_upload",
        "step_color",
        "text_color",
        "company_code",
        "loading",
        "name_app",
    ];
    public function company()
    {
        return $this->belongsTo(eCCompany::class, 'company_id');
    }
}
