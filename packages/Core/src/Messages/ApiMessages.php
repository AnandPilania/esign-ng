<?php

namespace Core\Messages;

class ApiMessages
{
    // Authentication
    const AUTH_SUCCESS = 'Đăng nhập thành công!';
    const AUTH_FAILED = 'Thông tin đăng nhập không chính xác vui lòng kiểm tra lại!';
    const VALIDATE_FAILED = 'Thông tin đăng nhập không được để trống!';
    const PASSWORD_SHORT = 'Mật khẩu phải có ít nhất 6 ký tự!';
    const ACCOUNT_NOT_ACCEPTABLE = 'Tài khoản chưa được kích hoạt!';
    const LOG_OUT_SUCCESS = 'Đăng xuất thành công!';
    // actions
    const GET_INFO_LOGIN = 'Lấy thông tin đăng nhập';
    const INITIALIZATION_SUCCESS = 'Khởi tạo thành công';
    // errors
    const ERROR_PROCESSING = 'Đã xảy ra lỗi trong quá trình thực hiện!';
}
