<?php

use Illuminate\Database\Seeder;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ec_s_permissions')->insert([
            [
                "permission" => "CONFIG",
                "note" => "Thiết lập",
                "parent_permission" => NULL,
                "is_view" => 0,
                "is_write" => 0,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "CONFIG_ACCOUNT",
                "note" => "Thông tin thuê bao",
                "parent_permission" => "CONFIG",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "CONFIG_SEND_DOC",
                "note" => "Cấu hình gửi tài liệu",
                "parent_permission" => "CONFIG",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "CONFIG_PERMISSION",
                "note" => "Cấu hình phân quyền hệ thống",
                "parent_permission" => "CONFIG",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "CONFIG_USERS",
                "note" => "Quản lý đăng nhập người dùng",
                "parent_permission" => "CONFIG",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            //Tai lieu noi bo
            [
                "permission" => "INTERNAL",
                "note" => "Nội bộ",
                "parent_permission" => NULL,
                "is_view" => 0,
                "is_write" => 0,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "INTERNAL_CREATE",
                "note" => "Tạo tài liệu mới",
                "parent_permission" => "INTERNAL",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "INTERNAL_DOCUMENT_LIST",
                "note" => "Danh sách tài liệu điện tử",
                "parent_permission" => "INTERNAL",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "INTERNAL_APPROVAL_MANAGE",
                "note" => "Quản lý phê duyệt tài liệu",
                "parent_permission" => "INTERNAL",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 1,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "INTERNAL_SIGN_MANAGE",
                "note" => "Quản lý ký số tài liệu",
                "parent_permission" => "INTERNAL",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 1,
                'status' => 1
            ],
            [
                "permission" => "INTERNAL_SEND_EMAIL",
                "note" => "Quản lý gửi email",
                "parent_permission" => "INTERNAL",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "INTERNAL_SEND_SMS",
                "note" => "Quản lý gửi tin nhắn",
                "parent_permission" => "INTERNAL",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "INTERNAL_AUTHORISED_MANAGE",
                "note" => "Lịch sử xác thực tài liệu",
                "parent_permission" => "INTERNAL",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            //Tai lieu thuong mai
            [
                "permission" => "COMMERCE",
                "note" => "Thương mại",
                "parent_permission" => NULL,
                "is_view" => 0,
                "is_write" => 0,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "COMMERCE_CREATE",
                "note" => "Tạo tài liệu mới",
                "parent_permission" => "COMMERCE",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "COMMERCE_DOCUMENT_LIST",
                "note" => "Danh sách tài liệu điện tử",
                "parent_permission" => "COMMERCE",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "COMMERCE_APPROVAL_MANAGE",
                "note" => "Quản lý phê duyệt tài liệu",
                "parent_permission" => "COMMERCE",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 1,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "COMMERCE_SIGN_MANAGE",
                "note" => "Quản lý ký số tài liệu",
                "parent_permission" => "COMMERCE",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 1,
                'status' => 1
            ],
            [
                "permission" => "COMMERCE_SEND_EMAIL",
                "note" => "Quản lý gửi email",
                "parent_permission" => "COMMERCE",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "COMMERCE_SEND_SMS",
                "note" => "Quản lý gửi tin nhắn",
                "parent_permission" => "COMMERCE",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "COMMERCE_AUTHORISED_MANAGE",
                "note" => "Lịch sử xác thực tài liệu",
                "parent_permission" => "COMMERCE",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            //Xu ly tai lieu
            [
                "permission" => "DOCUMENT_HANDLER",
                "note" => "Xử lý tài liệu",
                "parent_permission" => NULL,
                "is_view" => 0,
                "is_write" => 0,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "DOCUMENT_HANDLER_NEAR_EXPIRE",
                "note" => "Tài liệu sắp quá hạn",
                "parent_permission" => "DOCUMENT_HANDLER",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "DOCUMENT_HANDLER_EXPIRE",
                "note" => "Tài liệu đã bị quá hạn",
                "parent_permission" => "DOCUMENT_HANDLER",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "DOCUMENT_HANDLER_DENY",
                "note" => "Tài liệu đã bị từ chối",
                "parent_permission" => "DOCUMENT_HANDLER",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "DOCUMENT_HANDLER_DELETE",
                "note" => "Xóa bỏ tài liệu",
                "parent_permission" => "DOCUMENT_HANDLER",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            //bao cao
            [
                "permission" => "REPORT",
                "note" => "Báo cáo",
                "parent_permission" => NULL,
                "is_view" => 0,
                "is_write" => 0,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "REPORT_INTERNAL",
                "note" => "Báo cáo tài liệu nội bộ",
                "parent_permission" => "REPORT",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "REPORT_COMMERCE",
                "note" => "Báo cáo tài liệu thương mại",
                "parent_permission" => "REPORT",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "REPORT_SEND_MESSAGE",
                "note" => "Báo cáo gửi tin",
                "parent_permission" => "REPORT",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            //Tien ich
            [
                "permission" => "UTILITIES",
                "note" => "Tiện ích",
                "parent_permission" => NULL,
                "is_view" => 0,
                "is_write" => 0,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "UTILITIES_POSITION",
                "note" => "Chức vụ",
                "parent_permission" => "UTILITIES",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "UTILITIES_DEPARTMENT",
                "note" => "Phòng ban",
                "parent_permission" => "UTILITIES",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "UTILITIES_EMPLOYEE",
                "note" => "Nhân viên",
                "parent_permission" => "UTILITIES",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "UTILITIES_CUSTOMER",
                "note" => "Khách hàng đối tác",
                "parent_permission" => "UTILITIES",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "UTILITIES_DOCUMENT_TYPE",
                "note" => "Phân loại tài liệu",
                "parent_permission" => "UTILITIES",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "UTILITIES_DOCUMENT_SAMPLE",
                "note" => "Quản lý tài liệu mẫu",
                "parent_permission" => "UTILITIES",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "CONFIG_TEMPLATE",
                "note" => "Quản lý mẫu thông báo",
                "parent_permission" => "CONFIG",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "VIEW_ACTION_HISTORY",
                "note" => "Lịch sử tương tác",
                "parent_permission" => "UTILITIES",
                "is_view" => 1,
                "is_write" => 0,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "DOCUMENT_STYLE_BUY_IN",
                "note" => "Mua vào",
                "parent_permission" => "DOCUMENT_STYLE",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "DOCUMENT_STYLE_SELL_OUT",
                "note" => "Bán ra",
                "parent_permission" => "DOCUMENT_STYLE",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "DOCUMENT_STYLE_ELSE",
                "note" => "Khác",
                "parent_permission" => "DOCUMENT_STYLE",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "UTILITIES_BRANCH",
                "note" => "Chi nhánh",
                "parent_permission" => "UTILITIES",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "REPORT_SIGN_ASSIGNEE",
                "note" => "Danh sách người ký",
                "parent_permission" => "REPORT",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
            [
                "permission" => "REPORT_SIGN_EKYC",
                "note" => "Báo cáo kyc",
                "parent_permission" => "REPORT",
                "is_view" => 1,
                "is_write" => 1,
                "is_approval" => 0,
                "is_decision" => 0,
                'status' => 1
            ],
        ]);
    }
}
