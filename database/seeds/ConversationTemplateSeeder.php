<?php

use Illuminate\Database\Seeder;

class ConversationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ec_s_conversation_templates')->insert([
            [
                "template_name" => "APPROVAL_REQUEST_DOCUMENT",
                "template_description" => "Thông báo yêu cầu duyệt tài liệu",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "SIGN_REQUEST_DOCUMENT",
                "template_description" => "Thông báo yêu cầu ký tài liệu",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "AGREE_APPROVAL_DOCUMENT",
                "template_description" => "Thông báo đồng ý duyệt tài liệu",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "AGREE_SIGN_DOCUMENT",
                "template_description" => "Thông báo đồng ý ký tài liệu",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "REMIND_APPROVAL_DOCUMENT",
                "template_description" => "Thông báo nhắc nhở duyệt tài liệu",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "REMIND_SIGN_DOCUMENT",
                "template_description" => "Thông báo nhắc nhở ký tài liệu",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "NEARLY_OVERDUE_DOCUMENT",
                "template_description" => "Thông báo tài liệu sắp quá hạn",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "OVERDUE_DOCUMENT",
                "template_description" => "Thông báo tài liệu đã quá hạn",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "DENY_DOCUMENT",
                "template_description" => "Thông báo tài liệu đã bị từ chối",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "EXTEND_DOCUMENT",
                "template_description" => "Thông báo gia hạn tài liệu",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "RESTORE_DOCUMENT",
                "template_description" => "Thông báo khoi phuc tài liệu",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "COMPLETE_DOCUMENT",
                "template_description" => "Thông báo hoàn thành tài liệu",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "STORAGE_DOCUMENT",
                "template_description" => "Thông báo lưu trữ tài liệu",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "AUTHORISED_DOCUMENT",
                "template_description" => "Thông báo tài liệu đã được xác thực trên trục",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "SEND_OTP_DOCUMENT",
                "template_description" => "Thông báo gửi mã OTP",
                'template' => "",
                'type' => 0,
                'is_ams' => 1
            ],
            [
                "template_name" => "APPROVAL_REQUEST_DOCUMENT",
                "template_description" => "Thông báo yêu cầu duyệt tài liệu",
                'template' => '<p>K&iacute;nh gửi <em>[[${ten_nguoi_nhan}]],</em></p>

                <p><em>Để ho&agrave;n tất [[${ten_tai_lieu}]]&nbsp;, anh (chị) vui l&ograve;ng truy cập v&agrave;o hệ thống <strong>Hợp đồng điện tử hoặc link truy cập </strong>để&nbsp; xem v&agrave; duyệt t&agrave;i liệu/hợp đồng</em></p>

                <p>M&atilde; truy cập t&agrave;i liệu: [[${ma_tra_cuu}]]</p>

                <p>Thời hạn t&agrave;i liệu/hợp đồng: [[${han_tai_lieu}]]</p>

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
                "template_name" => "SIGN_REQUEST_DOCUMENT",
                "template_description" => "Thông báo yêu cầu ký tài liệu",
                'template' => '<p>K&iacute;nh gửi <em>[[${ten_nguoi_nhan}]],</em></p>

                <p><em>Để ho&agrave;n tất [[${ten_tai_lieu}]] , anh (chị) vui l&ograve;ng truy cập v&agrave;o hệ thống <strong>Hợp đồng điện tử hoặc link truy cập </strong>để xem v&agrave; k&yacute; t&agrave;i liệu/hợp đồng</em></p>

                <p>M&atilde; truy cập t&agrave;i liệu: [[${ma_tra_cuu}]]</p>

                <p>Thời hạn t&agrave;i liệu/hợp đồng: [[${han_tai_lieu}]]</p>

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
                "template_name" => "AGREE_APPROVAL_DOCUMENT",
                "template_description" => "Thông báo đồng ý duyệt tài liệu",
                'template' => '<p>Kính gửi <em>[[${ten_nguoi_nhan}]],</em></p>

                <p><em>E-contract th&ocirc;ng b&aacute;o, hợp đồng/t&agrave;i liệu của anh (chị) đ&atilde; duyệt th&agrave;nh c&ocirc;ng.</em></p>

                <ul>
                    <li><em>T&agrave;i liệu duyệt : [[${ten_tai_lieu}]]</em></li>
                    <li><em>Ng&agrave;y duyệt: [[${ngay_duyet}]]</em></li>
                    <li><em>Người duyệt: [[${nguoi_duyet}]]</em></li>
                    <li><em>Thời hạn xử l&yacute; t&agrave;i liệu: [[${han_tai_lieu}]]</em></li>
                </ul>

                <p><em>&nbsp;Để xem th&ocirc;ng tin chi tiết, vui l&ograve;ng truy cập v&agrave;o hệ thống <strong>Hợp đồng điện tử </strong>để biết th&ecirc;m th&ocirc;ng tin.</em></p>

                <p>--------------------------------------</p>

                <p><em>Đ&acirc;y chỉ l&agrave; thư điện tử x&aacute;c nhận của dịch vụ Hợp đồng điện tử, vui l&ograve;ng kh&ocirc;ng trả lời v&agrave;o hộp thư n&agrave;y.</em></p>

                <p><em>Tr&acirc;n trọng.</em></p>',
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "AGREE_SIGN_DOCUMENT",
                "template_description" => "Thông báo đồng ý ký tài liệu",
                'template' => '<p>Kính gửi <em>[[${ten_nguoi_nhan}]],</em></p>

                <p><em>E-contract th&ocirc;ng b&aacute;o, hợp đồng/t&agrave;i liệu của anh (chị) đ&atilde; ký th&agrave;nh c&ocirc;ng.</em></p>

                <ul>
                    <li><em>T&agrave;i liệu ký : [[${ten_tai_lieu}]]</em></li>
                    <li><em>Ng&agrave;y ký: [[${ngay_ky}]]</em></li>
                    <li><em>Người ký: [[${nguoi_ky}]]</em></li>
                    <li><em>Thời hạn xử l&yacute; t&agrave;i liệu: [[${han_tai_lieu}]]</em></li>
                </ul>

                <p><em>&nbsp;Để xem th&ocirc;ng tin chi tiết, vui l&ograve;ng truy cập v&agrave;o hệ thống <strong>Hợp đồng điện tử </strong>để biết th&ecirc;m th&ocirc;ng tin.</em></p>

                <p>--------------------------------------</p>

                <p><em>Đ&acirc;y chỉ l&agrave; thư điện tử x&aacute;c nhận của dịch vụ Hợp đồng điện tử, vui l&ograve;ng kh&ocirc;ng trả lời v&agrave;o hộp thư n&agrave;y.</em></p>

                <p><em>Tr&acirc;n trọng.</em></p>',
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "REMIND_APPROVAL_DOCUMENT",
                "template_description" => "Thông báo nhắc nhở duyệt tài liệu",
                'template' => 'Kính gửi [[${ten_nguoi_nhan}]],

                Để hoàn tất [[${ten_tai_lieu}]] , anh (chị) vui lòng truy cập vào hệ thống Hợp đồng điện tử hoặc link truy cập để  xem và duyệt tài liệu/hợp đồng

                Mã truy cập tài liệu: [[${ma_tra_cuu}]]

                Thời hạn tài liệu/hợp đồng: [[${han_tai_lieu}]]

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
                "template_name" => "REMIND_SIGN_DOCUMENT",
                "template_description" => "Thông báo nhắc nhở ký tài liệu",
                'template' => 'Kính gửi [[${ten_nguoi_nhan}]],

                Để hoàn tất [[${ten_tai_lieu}]] , anh (chị) vui lòng truy cập vào hệ thống Hợp đồng điện tử hoặc link truy cập để xem và ký tài liệu/hợp đồng

                Mã truy cập tài liệu: [[${ma_tra_cuu}]]

                Thời hạn tài liệu/hợp đồng: [[${han_tai_lieu}]]

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
                "template_name" => "NEARLY_OVERDUE_DOCUMENT",
                "template_description" => "Thông báo tài liệu sắp quá hạn",
                'template' => 'Kính gửi [[${ten_nguoi_nhan}]],

                E-contract thông báo, hợp đồng/tài liệu [[${ten_tai_lieu}]] của anh (chị) sắp hết hạn, vui lòng đăng nhập hệ thống Hợp đồng điện tử hoặc link truy cập để xử lý tài liệu trước ngày hết hạn.

                Hạn xử lý tài liệu: [[${han_tai_lieu}]]

                Mã truy cập tài liệu: [[${ma_tra_cuu}]]

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
                "template_name" => "OVERDUE_DOCUMENT",
                "template_description" => "Thông báo tài liệu đã quá hạn",
                'template' => '<p>K&iacute;nh gửi <em>[[${ten_nguoi_nhan}]],</em></p>

                <p><em>E-contract th&ocirc;ng b&aacute;o, hợp đồng/t&agrave;i liệu [[${ten_tai_lieu}]] của anh (chị) đ&atilde; hết hạn. Anh (chị) kh&ocirc;ng thể tiếp tục giao kết t&agrave;i liệu/hợp đồng [[${ten_tai_lieu}]].</em></p>

                <p>Hạn t&agrave;i liệu: [[${han_tai_lieu}]]</p>

                <p><em>&nbsp;Để tiếp tục giao kết, vui l&ograve;ng truy cập v&agrave;o hệ thống <strong>Hợp đồng điện tử </strong>để gia hạn th&ecirc;m thời hạn hợp đồng. </em></p>

                <p>--------------------------------------</p>

                <p><em>Đ&acirc;y chỉ l&agrave; thư điện tử x&aacute;c nhận của dịch vụ Hợp đồng điện tử, vui l&ograve;ng kh&ocirc;ng trả lời v&agrave;o hộp thư n&agrave;y.</em></p>

                <p>Tr&acirc;n trọng.</p>',
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "DENY_DOCUMENT",
                "template_description" => "Thông báo tài liệu đã bị từ chối",
                'template' => '<p>K&iacute;nh gửi <em>[[${ten_nguoi_nhan}]],</em></p>

                <p><em>E-contract th&ocirc;ng b&aacute;o, hợp đồng/t&agrave;i liệu của anh (chị) đ&atilde; bị từ chối [duyệt/k&yacute;]</em></p>

                <ul>
                    <li><em>T&agrave;i liệu bị từ chối : [[${ten_tai_lieu}]]</em></li>
                    <li><em>Người từ chối: [[${nguoi_tu_choi}]]</em></li>
                    <li><em>L&yacute; do từ chối: [[${ly_do_tu_choi}]].</em></li>
                </ul>

                <p><em>&nbsp;Để xem th&ocirc;ng tin chi tiết, vui l&ograve;ng truy cập v&agrave;o hệ thống <strong>Hợp đồng điện tử </strong>để gia hạn th&ecirc;m thời hạn hợp đồng. </em></p>

                <p>--------------------------------------</p>

                <p><em>Đ&acirc;y chỉ l&agrave; thư điện tử x&aacute;c nhận của dịch vụ Hợp đồng điện tử, vui l&ograve;ng kh&ocirc;ng trả lời v&agrave;o hộp thư n&agrave;y.</em></p>',
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "EXTEND_DOCUMENT",
                "template_description" => "Thông báo gia hạn tài liệu",
                'template' => '<p>K&iacute;nh gửi <em>[[${ten_nguoi_nhan}]],</em></p>

                <p><em>E-contract th&ocirc;ng b&aacute;o, hợp đồng/t&agrave;i liệu của anh (chị) đ&atilde; bị từ chối.</em></p>

                <ul>
                    <li><em>T&agrave;i liệu được gia hạn : [[${ten_tai_lieu}]]</em></li>
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
                "template_name" => "RESTORE_DOCUMENT",
                "template_description" => "Thông báo khôi phục tài liệu",
                'template' => '<p>K&iacute;nh gửi <em>[[${ten_nguoi_nhan}]],</em></p>

                <p><em>E-contract th&ocirc;ng b&aacute;o, hợp đồng/t&agrave;i liệu của anh (chị) đ&atilde; bị từ chối.</em></p>

                <ul>
                    <li><em>T&agrave;i liệu được khôi phục : [[${ten_tai_lieu}]]</em></li>

                </ul>

                <p><em>&nbsp;Để xem th&ocirc;ng tin chi tiết, vui l&ograve;ng truy cập v&agrave;o hệ thống <strong>Hợp đồng điện tử </strong>để biết th&ecirc;m th&ocirc;ng tin.</em></p>

                <p>--------------------------------------</p>

                <p><em>Đ&acirc;y chỉ l&agrave; thư điện tử x&aacute;c nhận của dịch vụ Hợp đồng điện tử, vui l&ograve;ng kh&ocirc;ng trả lời v&agrave;o hộp thư n&agrave;y.</em></p>',
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "COMPLETE_DOCUMENT",
                "template_description" => "Thông báo hoàn thành tài liệu",
                'template' => '<p>K&iacute;nh gửi <em>[[${ten_nguoi_nhan}]],</em></p>

                <p><em>E-contract th&ocirc;ng b&aacute;o, hợp đồng/t&agrave;i liệu của anh (chị) đ&atilde; ho&agrave;n th&agrave;nh.</em></p>

                <ul>
                    <li><em>T&agrave;i liệu duyệt : [[${ten_tai_lieu}]]</em></li>
                    <li><em>Thời gian ho&agrave;n th&agrave;nh: [[${thoi_gian_hoan_thanh}]]</em></li>
                    <li><em>M&atilde; tra cứu: [[${ma_tra_cuu}]]</em></li>
                    <li><em>Trang tra cứu: [[${trang_tra_cuu}]]</em></li>
                </ul>

                <p><em>Vui l&ograve;ng truy cập v&agrave;o hệ thống <strong>Hợp đồng điện tử </strong>hoặc truy cập v&agrave;o trang tra cứu th&ocirc;ng tin t&agrave;i liệu để biết th&ecirc;m chi tiết.</em></p>

                <p>--------------------------------------</p>

                <p><em>Đ&acirc;y chỉ l&agrave; thư điện tử x&aacute;c nhận của dịch vụ Hợp đồng điện tử, vui l&ograve;ng kh&ocirc;ng trả lời v&agrave;o hộp thư n&agrave;y.</em></p>

                <p><em>Tr&acirc;n trọng</em></p>',
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "STORAGE_DOCUMENT",
                "template_description" => "Thông báo lưu trữ tài liệu",
                'template' => "",
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "AUTHORISED_DOCUMENT",
                "template_description" => "Thông báo tài liệu đã được xác thực trên trục",
                'template' => "",
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "SEND_OTP_DOCUMENT",
                "template_description" => "Thông báo gửi mã OTP",
                'template' => 'Mã OTP của quý khách là [[${ma_otp}]]',
                'type' => 1,
                'is_ams' => 1
            ],
            [
                "template_name" => "CREATE_COMPANY",
                "template_description" => "Fcontract - Thông báo thông tin tài khoản",
                'template' => '<p>K&iacute;nh gửi qu&yacute; kh&aacute;ch h&agrave;ng,<br />
Xin cảm ơn qu&yacute; kh&aacute;ch đ&atilde; sử dụng dịch vụ Hợp đồng điện tử Fcontract.&nbsp;<br />
T&agrave;i khoản để truy cập v&agrave;o hệ thống hợp đồng điện tử của qu&yacute; kh&aacute;ch l&agrave;:<br />
Mật khẩu: [[${mat_khau}]]<br />
T&ecirc;n đăng nhập: [[${ten_dang_nhap}]]<br />

Qu&yacute; kh&aacute;ch vui l&ograve;ng, truy cập v&agrave;o đường dẫn &hellip;&hellip; để sử dụng dịch vụ.<br />
Lưu l&yacute;:<br />
Mọi th&ocirc;ng tin của email n&agrave;y cần được bảo mật, kh&ocirc;ng được chia sẻ dưới mọi h&igrave;nh thức.<br />
Đ&acirc;y l&agrave; hệ thống trả lời tự động, vui l&ograve;ng kh&ocirc;ng trả lời thư n&agrave;y.</p>',
                'type' => 1,
                'is_ams' => 0
            ],
            [
                "template_name" => "REMIND_EXPIRED_SERVICE",
                "template_description" => "Fcontract - Thông báo sắp hết hạn gói cước",
                'template' => '<p>K&iacute;nh gửi qu&yacute; kh&aacute;ch h&agrave;ng,<br />
Xin cảm ơn qu&yacute; kh&aacute;ch đ&atilde; sử dụng dịch vụ Hợp đồng điện tử Fcontract.&nbsp;<br />
Hệ thống ợp đồng điện tử Fcontract: xin thông b&aacute;o thời hạn sử dụng gói cươc của quý khách còn 5 ngày.<br />
Xin quý khách để ý thời gian và liên hệ với bên cung cấp dịch vụ để gia hạn hoặc đăng ký mới gói cước<br />

Đ&acirc;y l&agrave; hệ thống trả lời tự động, vui l&ograve;ng kh&ocirc;ng trả lời thư n&agrave;y.</p>',
                'type' => 1,
                'is_ams' => 0
            ],
            [
                "template_name" => "SEND_PASSWORD",
                "template_description" => "Thông báo gửi mã đăng nhập trang tra cứu",
                'template' => 'Mã đăng nhập của quý khách là [[${ma_tra_cuu}]]',
                'type' => 1,
                'is_ams' => 1
            ],
        ]);
    }
}
