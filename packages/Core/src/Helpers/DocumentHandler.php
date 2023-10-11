<?php


namespace Core\Helpers;


class DocumentHandler
{
    const __default = self::OK;

    const NearExpire = 1;
    const Expired = 2;
    const DENY = 3;
    const DELETE = 4;
    const DocNearExpire = 5;
    const DocExpired = 6;
}
