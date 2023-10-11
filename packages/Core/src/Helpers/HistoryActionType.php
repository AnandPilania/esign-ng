<?php
/**
 * Created by IntelliJ IDEA.
 * User: namnv
 * Time: 1:24 AM
 */

namespace Core\Helpers;

class HistoryActionType
{
    const __default = self::OK;

    const WEB_ACTION = 0;
    const ADMIN_ACTION = 1;
    const REMOTE_ACTION = 2;
    const API_ACTION = 3;
}
