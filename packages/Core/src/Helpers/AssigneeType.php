<?php
/**
 * Created by IntelliJ IDEA.
 * User: namnv
 * Time: 1:24 AM
 */

namespace Core\Helpers;

class AssigneeType
{
    const __default = self::OK;

    const CREATOR = 0;
    const APPROVAL = 1;
    const SIGN = 2;
    const STORAGE = 3;
}
