<?php
/**
 * Created by IntelliJ IDEA.
 * User: namnv
 * Time: 1:24 AM
 */

namespace Core\Helpers;

class AssigneeState
{
    const __default = self::OK;

    const NOT_RECEIVED = 0;
    const RECEIVED = 1;
    const COMPLETED = 2;
    const REJECT = 3;
}
