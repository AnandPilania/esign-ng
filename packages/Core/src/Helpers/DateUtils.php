<?php


namespace Core\Helpers;


use Core\Models\LogSiteAuthentication;
use Carbon\Carbon;
use DateTime;

class DateUtils
{
    public static function getDob($dob)
    {
        $now = new DateTime(Carbon::now());
        $dob = new DateTime($dob);
        if ($dob >= $now) {
            return LogSiteAuthentication::UNKNOWN_AGE;
        }
        $age = $dob->diff($now)->y;
        foreach (LogSiteAuthentication::ARRAY_AGE as $key => $value) {
            if ($age >= $value) {
                return $key;
            }
        }
        return LogSiteAuthentication::UNKNOWN_AGE;
    }
}
