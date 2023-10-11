<?php


namespace Core\Helpers;


use Illuminate\Support\Facades\Auth;

class GuardUtils
{
    const ADMIN_SITE = 1;
    const CUSTOMER_SITE = 2;
    const UNKNOWN = -1;

    const READ_PERMISSION = 2;
    const WRITE_PERMISSION = 1;
    const UNKNOWN_PERMISSION = -1;

    public function guard()
    {
        if (Auth::guard('admin')->check()) {
            return self::ADMIN_SITE;
        } elseif (Auth::guard('customer')->check()) {
            return self:: CUSTOMER_SITE;
        }

        return self::UNKNOWN;
    }
}
