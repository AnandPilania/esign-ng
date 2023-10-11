<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateAddendumSeeder extends Seeder
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
                 "permission" => "DOCUMENT_HANDLER_DOC_NEAR_EXPIRE",
                 "note" => "Tài liệu sắp hết hiệu lực",
                 "parent_permission" => "DOCUMENT_HANDLER",
                 "is_view" => 1,
                 "is_write" => 1,
                 "is_approval" => 0,
                 "is_decision" => 0,
                 'status' => 1
             ],
             [
                 "permission" => "DOCUMENT_HANDLER_DOC_EXPIRE",
                 "note" => "Tài liệu hết hiệu lực",
                 "parent_permission" => "DOCUMENT_HANDLER",
                 "is_view" => 1,
                 "is_write" => 1,
                 "is_approval" => 0,
                 "is_decision" => 0,
                 'status' => 1
             ],
         ]);
        DB::table('ec_s_conversation_templates')->insert([
            [
                "template_name" => "APPROVAL_REQUEST_ADDENDUM",
                "template_description" => "Thông báo yêu cầu duyệt phụ lục",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "SIGN_REQUEST_ADDENDUM",
                "template_description" => "Thông báo yêu cầu ký phụ lục",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "AGREE_APPROVAL_ADDENDUM",
                "template_description" => "Thông báo đồng ý duyệt phụ lục",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "AGREE_SIGN_ADDENDUM",
                "template_description" => "Thông báo đồng ý ký phụ lục",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "REMIND_APPROVAL_ADDENDUM",
                "template_description" => "Thông báo nhắc nhở duyệt phụ lục",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "REMIND_SIGN_ADDENDUM",
                "template_description" => "Thông báo nhắc nhở ký phụ lục",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "NEARLY_OVERDUE_ADDENDUM",
                "template_description" => "Thông báo phụ lục sắp quá hạn",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "OVERDUE_ADDENDUM",
                "template_description" => "Thông báo phụ lục đã quá hạn",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "DENY_ADDENDUM",
                "template_description" => "Thông báo phụ lục đã bị từ chối",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "EXTEND_ADDENDUM",
                "template_description" => "Thông báo gia hạn phụ lục",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "RESTORE_ADDENDUM",
                "template_description" => "Thông báo khoi phuc phụ lục",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "COMPLETE_ADDENDUM",
                "template_description" => "Thông báo hoàn thành phụ lục",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "APPROVAL_REQUEST_ADDENDUM",
                "template_description" => "Thông báo yêu cầu duyệt phụ lục",
                'template' => '<p>K&iacute;nh gửi <em>[[${ten_nguoi_nhan}]],</em></p>

                <p><em>Để ho&agrave;n tất [[${ten_tai_lieu}]]&nbsp; - phụ lục [[${loai_phu_luc}]] tài liệu [[${tai_lieu_cha}]] , anh (chị) vui l&ograve;ng truy cập v&agrave;o hệ thống <strong>Hợp đồng điện tử hoặc link truy cập </strong>để&nbsp; xem v&agrave; duyệt phụ lục</em></p>

                <p>M&atilde; truy cập phụ lục: [[${ma_tra_cuu}]]</p>

                <p>Thời hạn phụ lục: [[${han_tai_lieu}]]</p>

                <p>Link truy cập: [[${trang_tra_cuu}]]</p>

                <p><strong>KH&Ocirc;NG CHIA SẺ</strong></p>

                <p>Email n&agrave;y chứa m&atilde; bảo mật, vui l&ograve;ng kh&ocirc;ng chia sẻ dưới mọi h&igrave;nh thức.</p>

                <p>--------------------------------------</p>

                <p><em>Đ&acirc;y chỉ l&agrave; thư điện tử x&aacute;c nhận của dịch vụ Hợp đồng điện tử, vui l&ograve;ng kh&ocirc;ng trả lời v&agrave;o hộp thư n&agrave;y.</em></p>

                <p>Tr&acirc;n trọng.</p>',
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "SIGN_REQUEST_ADDENDUM",
                "template_description" => "Thông báo yêu cầu ký phụ lục",
                'template' => '<p>K&iacute;nh gửi <em>[[${ten_nguoi_nhan}]],</em></p>

                <p><em>Để ho&agrave;n tất [[${ten_tai_lieu}]]&nbsp; - phụ lục [[${loai_phu_luc}]] tài liệu [[${tai_lieu_cha}]] , anh (chị) vui l&ograve;ng truy cập v&agrave;o hệ thống <strong>Hợp đồng điện tử hoặc link truy cập </strong>để&nbsp; xem v&agrave; ký phụ lục</em></p>

                <p>M&atilde; truy cập phụ lục: [[${ma_tra_cuu}]]</p>

                <p>Thời hạn phụ lục: [[${han_tai_lieu}]]</p>

                <p>Link truy cập: [[${trang_tra_cuu}]]</p>

                <p><strong>KH&Ocirc;NG CHIA SẺ</strong></p>

                <p>Email n&agrave;y chứa m&atilde; bảo mật, vui l&ograve;ng kh&ocirc;ng chia sẻ dưới mọi h&igrave;nh thức.</p>

                <p>--------------------------------------</p>

                <p><em>Đ&acirc;y chỉ l&agrave; thư điện tử x&aacute;c nhận của dịch vụ Hợp đồng điện tử, vui l&ograve;ng kh&ocirc;ng trả lời v&agrave;o hộp thư n&agrave;y.</em></p>

                <p>Tr&acirc;n trọng.</p>',
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "AGREE_APPROVAL_ADDENDUM",
                "template_description" => "Thông báo đồng ý duyệt phụ lục",
                'template' => '<p>Kính gửi <em>[[${ten_nguoi_nhan}]],</em></p>

                <p><em>E-contract th&ocirc;ng b&aacute;o, phụ lục [[${loai_phu_luc}]] tài liệu [[${tai_lieu_cha}]] của anh (chị) đ&atilde; duyệt th&agrave;nh c&ocirc;ng.</em></p>

                <ul>
                    <li><em>Phụ lục duyệt : [[${ten_tai_lieu}]]</em></li>
                    <li><em>Ng&agrave;y duyệt: [[${ngay_duyet}]]</em></li>
                    <li><em>Người duyệt: [[${nguoi_duyet}]]</em></li>
                    <li><em>Thời hạn xử l&yacute; phụ lục: [[${han_tai_lieu}]]</em></li>
                </ul>

                <p><em>&nbsp;Để xem th&ocirc;ng tin chi tiết, vui l&ograve;ng truy cập v&agrave;o hệ thống <strong>Hợp đồng điện tử </strong>để biết th&ecirc;m th&ocirc;ng tin.</em></p>

                <p>--------------------------------------</p>

                <p><em>Đ&acirc;y chỉ l&agrave; thư điện tử x&aacute;c nhận của dịch vụ Hợp đồng điện tử, vui l&ograve;ng kh&ocirc;ng trả lời v&agrave;o hộp thư n&agrave;y.</em></p>

                <p><em>Tr&acirc;n trọng.</em></p>',
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "AGREE_APPROVAL_ADDENDUM",
                "template_description" => "Thông báo đồng ý ký phụ lục",
                'template' => '<p>Kính gửi <em>[[${ten_nguoi_nhan}]],</em></p>

                <p><em>E-contract th&ocirc;ng b&aacute;o, phụ lục [[${loai_phu_luc}]] tài liệu [[${tai_lieu_cha}]] của anh (chị) đ&atilde; ký th&agrave;nh c&ocirc;ng.</em></p>

                <ul>
                    <li><em>Phụ lục ký : [[${ten_tai_lieu}]]</em></li>
                    <li><em>Ng&agrave;y ký: [[${ngay_duyet}]]</em></li>
                    <li><em>Người ký: [[${nguoi_duyet}]]</em></li>
                    <li><em>Thời hạn xử l&yacute; phụ lục: [[${han_tai_lieu}]]</em></li>
                </ul>

                <p><em>&nbsp;Để xem th&ocirc;ng tin chi tiết, vui l&ograve;ng truy cập v&agrave;o hệ thống <strong>Hợp đồng điện tử </strong>để biết th&ecirc;m th&ocirc;ng tin.</em></p>

                <p>--------------------------------------</p>

                <p><em>Đ&acirc;y chỉ l&agrave; thư điện tử x&aacute;c nhận của dịch vụ Hợp đồng điện tử, vui l&ograve;ng kh&ocirc;ng trả lời v&agrave;o hộp thư n&agrave;y.</em></p>

                <p><em>Tr&acirc;n trọng.</em></p>',
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "REMIND_SIGN_ADDENDUM",
                "template_description" => "Thông báo nhắc nhở ký phụ lục",
                'template' => 'Kính gửi [[${ten_nguoi_nhan}]],

                Để hoàn tất [[${ten_tai_lieu}]] - phụ lục [[${loai_phu_luc}]] tài liệu [[${tai_lieu_cha}]], anh (chị) vui lòng truy cập vào hệ thống Hợp đồng điện tử hoặc link truy cập để xem và ký phụ lục

                Mã truy cập phụ lục: [[${ma_tra_cuu}]]

                Thời hạn phụ lục: [[${han_tai_lieu}]]

                Link truy cập: [[${trang_tra_cuu}]]

                KHÔNG CHIA SẺ

                Email này chứa mã bảo mật, vui lòng không chia sẻ dưới mọi hình thức.

                --------------------------------------

                Đây chỉ là thư điện tử xác nhận của dịch vụ Hợp đồng điện tử, vui lòng không trả lời vào hộp thư này.

                Trân trọng.',
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "REMIND_APPROVAL_ADDENDUM",
                "template_description" => "Thông báo nhắc nhở duyệt phụ lục",
                'template' => 'Kính gửi [[${ten_nguoi_nhan}]],

                Để hoàn tất [[${ten_tai_lieu}]] - phụ lục [[${loai_phu_luc}]] tài liệu [[${tai_lieu_cha}]], anh (chị) vui lòng truy cập vào hệ thống Hợp đồng điện tử hoặc link truy cập để xem và duyệt phụ lục

                Mã truy cập phụ lục: [[${ma_tra_cuu}]]

                Thời hạn phụ lục: [[${han_tai_lieu}]]

                Link truy cập: [[${trang_tra_cuu}]]

                KHÔNG CHIA SẺ

                Email này chứa mã bảo mật, vui lòng không chia sẻ dưới mọi hình thức.

                --------------------------------------

                Đây chỉ là thư điện tử xác nhận của dịch vụ Hợp đồng điện tử, vui lòng không trả lời vào hộp thư này.

                Trân trọng.',
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "NEARLY_OVERDUE_ADDENDUM",
                "template_description" => "Thông báo phụ lục sắp quá hạn",
                'template' => 'Kính gửi [[${ten_nguoi_nhan}]],

                E-contract thông báo,[[${ten_tai_lieu}]] - phụ lục [[${loai_phu_luc}]] tài liệu [[${tai_lieu_cha}]] của anh (chị) sắp hết hạn, vui lòng đăng nhập hệ thống Hợp đồng điện tử hoặc link truy cập để xử lý phụ lục trước ngày hết hạn.

                Hạn xử lý phụ lục: [[${han_tai_lieu}]]

                Mã truy cập phụ lục: [[${ma_tra_cuu}]]

                Link truy cập: [[${trang_tra_cuu}]]

                KHÔNG CHIA SẺ

                Email này chứa mã bảo mật, vui lòng không chia sẻ dưới mọi hình thức.

                --------------------------------------

                Đây chỉ là thư điện tử xác nhận của dịch vụ Hợp đồng điện tử, vui lòng không trả lời vào hộp thư này.

                Trân trọng.',
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "OVERDUE_ADDENDUM",
                "template_description" => "Thông báo phụ lục đã quá hạn",
                'template' => '<p>K&iacute;nh gửi <em>[[${ten_nguoi_nhan}]],</em></p>

                <p><em>E-contract th&ocirc;ng b&aacute;o, [[${ten_tai_lieu}]] - phụ lục [[${loai_phu_luc}]] tài liệu [[${tai_lieu_cha}]] của anh (chị) đ&atilde; hết hạn. Anh (chị) kh&ocirc;ng thể tiếp tục giao kết phụ lục [[${ten_tai_lieu}]].</em></p>

                <p>Hạn phụ lục: [[${han_tai_lieu}]]</p>

                <p><em>&nbsp;Để tiếp tục giao kết, vui l&ograve;ng truy cập v&agrave;o hệ thống <strong>Hợp đồng điện tử </strong>để gia hạn th&ecirc;m thời hạn phụ lục. </em></p>

                <p>--------------------------------------</p>

                <p><em>Đ&acirc;y chỉ l&agrave; thư điện tử x&aacute;c nhận của dịch vụ Hợp đồng điện tử, vui l&ograve;ng kh&ocirc;ng trả lời v&agrave;o hộp thư n&agrave;y.</em></p>

                <p>Tr&acirc;n trọng.</p>',
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "DENY_ADDENDUM",
                "template_description" => "Thông báo phụ lục đã bị từ chối",
                'template' => '<p>K&iacute;nh gửi <em>[[${ten_nguoi_nhan}]],</em></p>

                <p><em>E-contract th&ocirc;ng b&aacute;o, phụ lục [[${loai_phu_luc}]] tài liệu [[${tai_lieu_cha}]] của anh (chị) đ&atilde; bị từ chối [duyệt/k&yacute;]</em></p>

                <ul>
                    <li><em>Phụ lục bị từ chối : [[${ten_tai_lieu}]]</em></li>
                    <li><em>Người từ chối: [[${nguoi_tu_choi}]]</em></li>
                    <li><em>L&yacute; do từ chối: [[${ly_do_tu_choi}]].</em></li>
                </ul>

                <p><em>&nbsp;Để xem th&ocirc;ng tin chi tiết, vui l&ograve;ng truy cập v&agrave;o hệ thống <strong>Hợp đồng điện tử </strong>để xem và chỉnh sửa phụ lục. </em></p>

                <p>--------------------------------------</p>

                <p><em>Đ&acirc;y chỉ l&agrave; thư điện tử x&aacute;c nhận của dịch vụ Hợp đồng điện tử, vui l&ograve;ng kh&ocirc;ng trả lời v&agrave;o hộp thư n&agrave;y.</em></p>',
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "EXTEND_ADDENDUM",
                "template_description" => "Thông báo gia hạn phụ lục",
                'template' => '<p>K&iacute;nh gửi <em>[[${ten_nguoi_nhan}]],</em></p>

                <p><em>E-contract th&ocirc;ng b&aacute;o, phụ lục [[${loai_phu_luc}]] tài liệu [[${tai_lieu_cha}]] của anh (chị) đ&atilde; bị từ chối.</em></p>

                <ul>
                    <li><em>Phụ lục được gia hạn : [[${ten_tai_lieu}]]</em></li>
                    <li><em>Thời hạn cũ: [[${thoi_han_cu}]]</em></li>
                    <li><em>Thời hạn mới: [[${thoi_han_moi}]].</em></li>
                </ul>

                <p><em>&nbsp;Để xem th&ocirc;ng tin chi tiết, vui l&ograve;ng truy cập v&agrave;o hệ thống <strong>Hợp đồng điện tử </strong>để biết th&ecirc;m th&ocirc;ng tin.</em></p>

                <p>--------------------------------------</p>

                <p><em>Đ&acirc;y chỉ l&agrave; thư điện tử x&aacute;c nhận của dịch vụ Hợp đồng điện tử, vui l&ograve;ng kh&ocirc;ng trả lời v&agrave;o hộp thư n&agrave;y.</em></p>',
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "COMPLETE_ADDENDUM",
                "template_description" => "Thông báo hoàn thành phụ lục",
                'template' => '<p>K&iacute;nh gửi <em>[[${ten_nguoi_nhan}]],</em></p>

                <p><em>E-contract th&ocirc;ng b&aacute;o, phụ lục [[${loai_phu_luc}]] tài liệu [[${tai_lieu_cha}]] của anh (chị) đ&atilde; ho&agrave;n th&agrave;nh.</em></p>

                <ul>
                    <li><em>Phụ lục duyệt : [[${ten_tai_lieu}]]</em></li>
                    <li><em>Thời gian ho&agrave;n th&agrave;nh: [[${thoi_gian_hoan_thanh}]]</em></li>
                    <li><em>M&atilde; tra cứu: [[${ma_tra_cuu}]]</em></li>
                    <li><em>Trang tra cứu: [[${trang_tra_cuu}]]</em></li>
                </ul>

                <p><em>Vui l&ograve;ng truy cập v&agrave;o hệ thống <strong>Hợp đồng điện tử </strong>hoặc truy cập v&agrave;o trang tra cứu th&ocirc;ng tin phụ lục để biết th&ecirc;m chi tiết.</em></p>

                <p>--------------------------------------</p>

                <p><em>Đ&acirc;y chỉ l&agrave; thư điện tử x&aacute;c nhận của dịch vụ Hợp đồng điện tử, vui l&ograve;ng kh&ocirc;ng trả lời v&agrave;o hộp thư n&agrave;y.</em></p>

                <p><em>Tr&acirc;n trọng</em></p>',
                'type' => 1,
                'is_ams' => 1
            ]
        ]);
    }
}
