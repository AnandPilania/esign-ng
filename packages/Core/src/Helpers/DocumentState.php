<?php
/**
 * Created by IntelliJ IDEA.
 * User: namnv
 * Time: 1:24 AM
 */

namespace Core\Helpers;

use MyCLabs\Enum\Enum;

class DocumentState extends Enum
{
//    const __default = self::COMPLETE;

    const DRAFT = 1; // tài liệu chưa tạo xong
    const WAIT_APPROVAL = 2; // chờ duyệt
    const WAIT_SIGNING = 3; // chờ ký
    const DENY = 4; // từ chối
    const OVERDUE = 5; // quá hạn giao kết
    const DROP = 6; // hủy bỏ
    const NOT_AUTHORIZE = 7; // chưa xác thực
    const COMPLETE = 8; // hoàn thành
    const COMPLETING = 9; // đang trong quá trình xác
    const VERIFY_FAIL = 10; // xác thực failed
    const OVERLAPSE = 11; // hết hiệu lực
}
