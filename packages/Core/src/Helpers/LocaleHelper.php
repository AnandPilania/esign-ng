<?php

namespace Core\Helpers;

use Illuminate\Support\Facades\App;

class LocaleHelper
{
    /**
     * getLocale
     * @return Number
     */
    public static function getLocale()
    {
        $locale = App::getLocale();
        if ($locale == LANGUAGE_EN) {
            return EN;
        }
        return JP;
    }

    /**
     * Set Locale From Local Cookie
     * @param $locale
     * @return Void
     */
    public static function setLocale($locale)
    {
        $lang = LANGUAGE_JP;
        if ($locale == EN) {
            $lang = LANGUAGE_EN;
        }
        App::setLocale($lang);
    }
}
