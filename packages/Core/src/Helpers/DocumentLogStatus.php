<?php
/**
 * Created by IntelliJ IDEA.
 * User: namnv
 * Time: 1:24 AM
 */

namespace Core\Helpers;

class DocumentLogStatus
{
    const __default = self::OK;

    const CREATE_DOCUMENT = 1;
    const EDIT_DOCUMENT = 2;
    const FINISH_DRAFTING = 3;
    const SEND_EMAIL = 4;
    const DENY_APPROVAL = 5;
    const AGREE_APPROVAL = 6;
    const DENY_SIGNING = 7;
    const AGREE_SIGNING = 8;
    const FINISH_DOCUMENT = 9;
    const AFTER_FINISH = 10;
}
