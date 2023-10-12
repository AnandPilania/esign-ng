<?php
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Customer\Controllers'], function () {

    Route::prefix('api/v1')->group(function () {
        Route::post('login', 'eCCustomerLoginController@login');
        Route::get('test', 'eCTestController@uploadFileV2');
    });

    Route::prefix('api/v1')->group(function () {
        Route::group(['middleware' => ['auth_api:api,jwt', 'cors']], function () {
            Route::get('auth-check', 'eCCustomerLoginController@isLogin');
            Route::get('init', 'eCCustomerLoginController@initData');
            Route::post('initDashboard', 'eCCustomerLoginController@initDashboardData');
            Route::post('changeUserInfo', 'eCCustomerLoginController@changeUserInfo');
            Route::post('changePwd', 'eCCustomerLoginController@changePwd');
            Route::post('updateUserSignature', 'eCCustomerLoginController@updateUserSignature');
            Route::post('deleteUserSignature', 'eCCustomerLoginController@deleteUserSignature');
            Route::get('logout', 'eCCustomerLoginController@logout');
            Route::post('lich-su-tai-lieu', 'eCInternalController@getHistoryDocument');
            Route::post('lich-su-xac-thuc', 'eCInternalController@getHistoryTransactionDocument');

            Route::prefix('thiet-lap')->group(function(){
                Route::post('thong-tin-thue-bao/khoi-tao/', 'eCConfigController@initCompany');
                Route::post('thong-tin-thue-bao/cap-nhat', 'eCConfigController@updateCompany');
                Route::post('thong-tin-thue-bao/tim-kiem-nguoi-giao-ket', 'eCConfigController@searchCompanyConsignee');
                Route::post('thong-tin-thue-bao/them-moi-nguoi-giao-ket', 'eCConfigController@createCompanyConsignee');
                Route::post('thong-tin-thue-bao/cap-nhat-nguoi-giao-ket', 'eCConfigController@updateCompanyConsignee');
                Route::post('thong-tin-thue-bao/xoa-nguoi-giao-ket', 'eCConfigController@deleteCompanyConsignee');
                Route::post('thong-tin-thue-bao/cap-nhat-remote-signing', 'eCConfigController@updateCompanyRemoteSign');
                Route::post('thong-tin-thue-bao/cap-nhat-chu-ky', 'eCConfigController@updateCompanySignature');

                Route::get('phan-quyen/khoi-tao', 'eCConfigController@initPermission');
                Route::post('phan-quyen/khoi-tao', 'eCConfigController@initDetailPermission');
                Route::post('phan-quyen/tim-kiem', 'eCConfigController@searchPermission');
                Route::post('phan-quyen/them-moi', 'eCConfigController@createNewPermission');
                Route::post('phan-quyen/cap-nhat', 'eCConfigController@updatePermission');
                Route::post('phan-quyen/xoa', 'eCConfigController@deletePermission');
                Route::post('phan-quyen/xoa-nhieu', 'eCConfigController@deleteMultiPermission');

                Route::post('tham-so/khoi-tao', 'eCConfigController@initSendDocument');
                Route::post('tham-so/cap-nhat-thoi-gian', 'eCConfigController@updateSendTime');
                Route::post('tham-so/cap-nhat-email', 'eCConfigController@updateEmail');
                Route::post('tham-so/cap-nhat-sms', 'eCConfigController@updateSms');

                Route::get('quan-ly-dang-nhap-nguoi-dung/khoi-tao/', 'eCConfigController@initUser');
                Route::post('quan-ly-dang-nhap-nguoi-dung/tim-kiem/', 'eCConfigController@searchUser');
                Route::post('quan-ly-dang-nhap-nguoi-dung/them-moi', 'eCConfigController@createUser');
                Route::post('quan-ly-dang-nhap-nguoi-dung/cap-nhat', 'eCConfigController@updateUser');
                Route::post('quan-ly-dang-nhap-nguoi-dung/doi-mat-khau', 'eCConfigController@changePasswordUser');
                Route::post('quan-ly-dang-nhap-nguoi-dung/xoa', 'eCConfigController@deleteUser');
                Route::post('quan-ly-dang-nhap-nguoi-dung/xoa-nhieu', 'eCConfigController@deleteMultiUser');
                Route::get('quan-ly-mau-thong-bao/khoi-tao/', 'eCConfigController@initTemplate');
                Route::post('quan-ly-mau-thong-bao/tim-kiem/', 'eCConfigController@searchTemplate');
                Route::post('quan-ly-mau-thong-bao/them-moi', 'eCConfigController@createTemplate');
                Route::post('quan-ly-mau-thong-bao/cap-nhat', 'eCConfigController@updateTemplate');
                Route::post('quan-ly-mau-thong-bao/xoa', 'eCConfigController@deleteTemplate');
                Route::post('quan-ly-mau-thong-bao/xoa-nhieu', 'eCConfigController@deleteMultiTemplate');
            });

            Route::prefix('tien-ich')->group(function() {
                Route::get('chi-nhanh/khoi-tao', 'eCUtilitiesController@initBranch');
                Route::post('chi-nhanh/tim-kiem', 'eCUtilitiesController@searchChiNhanh');
                Route::post('chi-nhanh/them-moi', 'eCUtilitiesController@createNewBranch');
                Route::post('chi-nhanh/cap-nhat', 'eCUtilitiesController@updateBranch');
                Route::post('chi-nhanh/cap-nhat-trang-thai', 'eCUtilitiesController@changeBranchStatus');
                Route::post('chi-nhanh/xoa', 'eCUtilitiesController@deleteBranch');
                Route::post('chi-nhanh/xoa-nhieu', 'eCUtilitiesController@deleteMultiBranch');

                Route::get('chuc-vu/khoi-tao', 'eCUtilitiesController@initPosition');
                Route::post('chuc-vu/tim-kiem', 'eCUtilitiesController@searchChucVu');
                Route::post('chuc-vu/them-moi', 'eCUtilitiesController@createNewPosition');
                Route::post('chuc-vu/cap-nhat', 'eCUtilitiesController@updatePosition');
                Route::post('chuc-vu/xoa', 'eCUtilitiesController@deletePosition');
                Route::post('chuc-vu/xoa-nhieu', 'eCUtilitiesController@deleteMultiPosition');

                Route::post('phong-ban/tim-kiem', 'eCUtilitiesController@searchPhongBan');
                Route::get('phong-ban/khoi-tao', 'eCUtilitiesController@initDepartment');
                Route::post('phong-ban/them-moi', 'eCUtilitiesController@createNewDepartment');
                Route::post('phong-ban/cap-nhat', 'eCUtilitiesController@updateDepartment');
                Route::post('phong-ban/xoa', 'eCUtilitiesController@deleteDepartment');
                Route::post('phong-ban/xoa-nhieu', 'eCUtilitiesController@deleteMultiDepartment');

                Route::get('nhan-vien/khoi-tao', 'eCUtilitiesController@initEmployee');
                Route::post('nhan-vien/tim-kiem', 'eCUtilitiesController@searchEmployee');
                Route::post('nhan-vien/them-moi', 'eCUtilitiesController@createNewEmployee');
                Route::post('nhan-vien/cap-nhat', 'eCUtilitiesController@updateEmployee');
                Route::post('nhan-vien/xoa', 'eCUtilitiesController@deleteEmployee');
                Route::post('nhan-vien/xoa-nhieu', 'eCUtilitiesController@deleteMultiEmployee');
                Route::post('nhan-vien/kiem-tra-ton-tai', 'eCUtilitiesController@checkExistEmployee');
                Route::get('danh-muc-khach-hang-doi-tac/khoi-tao', 'eCUtilitiesController@initCustomer');
                Route::post('danh-muc-khach-hang-doi-tac/tim-kiem', 'eCUtilitiesController@searchKhachHangDoiTac');
                Route::post('danh-muc-khach-hang-doi-tac/them-moi', 'eCUtilitiesController@createNewCustomer');
                Route::post('danh-muc-khach-hang-doi-tac/cap-nhat', 'eCUtilitiesController@updateCustomer');
                Route::post('danh-muc-khach-hang-doi-tac/xoa', 'eCUtilitiesController@deleteCustomer');
                Route::post('danh-muc-khach-hang-doi-tac/xoa-nhieu', 'eCUtilitiesController@deleteMultiCustomer');
                Route::post('danh-muc-khach-hang-doi-tac/kiem-tra-ton-tai', 'eCUtilitiesController@checkExistCustomer');
                Route::get('phan-loai-tai-lieu/khoi-tao', 'eCUtilitiesController@initDocumentType');
                Route::post('phan-loai-tai-lieu/tim-kiem', 'eCUtilitiesController@searchPhanLoaiTaiLieu');
                Route::post('phan-loai-tai-lieu/them-moi', 'eCUtilitiesController@createNewDocumentType');
                Route::post('phan-loai-tai-lieu/cap-nhat', 'eCUtilitiesController@updateDocumentType');
                Route::post('phan-loai-tai-lieu/xoa', 'eCUtilitiesController@deleteDocumentType');
                Route::post('phan-loai-tai-lieu/xoa-nhieu', 'eCUtilitiesController@deleteMultiDocumentType');
            });

            Route::prefix('noi-bo')->group(function() {
                Route::post('tao-moi/khoi-tao', 'eCInternalController@initCreateInternalDocument');
                Route::post('tao-moi/lay-so-tai-lieu', 'eCInternalController@getDocumentCodeByDocumentTypeId');
                Route::post('tao-moi/chi-tiet-tai-lieu-mau', 'eCInternalController@getDetailDocumentSampleById');
                Route::post('tao-moi/upload-file', 'eCInternalController@uploadDocumentFiles');
                Route::post('tao-moi/tai-tai-lieu-ky', 'eCInternalController@getSignDocument');
                Route::post('tao-moi/hoan-thanh-buoc-1', 'eCInternalController@goStep2');
                Route::post('tao-moi/hoan-thanh-buoc-2', 'eCInternalController@goStep3');
                Route::post('tao-moi/hoan-thanh-soan-thao', 'eCInternalController@finishDrafting');
                Route::post('tao-moi/tao-moi-tai-lieu', 'eCInternalController@createDocumentFromTemplate');
                Route::post('tao-moi/chi-tiet-tai-lieu-cha', 'eCInternalController@getFatherInfo');

                Route::post('chi-tiet/khoi-tao', 'eCInternalController@initViewInternalDocument');
                Route::post('chi-tiet/tim-kiem', 'eCInternalController@searchDocumentList');
                Route::post('chon-chu-ky', 'eCInternalController@selectSignature');
                Route::post('phe-duyet', 'eCInternalController@approveDocument');
                Route::post('tu-choi', 'eCInternalController@denyDocument');
                Route::post('ky-so', 'eCInternalController@signDocument');
                Route::post('ma-hoa-tai-lieu', 'eCInternalController@hashDoc');
                Route::post('cap-nhat-chu-ky', 'eCInternalController@updateSignatureLocation');
                Route::post('ky-so-otp', 'eCInternalController@signOtpDocument');
                Route::post('ky-so-kyc', 'eCInternalController@signKycDocument');
                Route::post('chinh-sua', 'eCInternalController@editDenyDocument');
                Route::post('gui-otp', 'eCInternalController@sendOtp');
                Route::post('xac-minh-ocr', 'eCInternalController@verifyOcr');
                Route::post('xoa-tai-lieu', 'eCInternalController@removeDocumentFile');
                Route::post('lay-list-chung-thu-so', 'eCInternalController@getListCts');
                Route::post('ky-my-sign', 'eCInternalController@signMySignDocument');
                Route::post('dang-ky-cts-ica', 'eCInternalController@registerCTS');
                Route::post('ky-ica', 'eCInternalController@signICA');

                Route::post('danh-sach-tai-lieu/khoi-tao', 'eCInternalController@initDocumentList');
                Route::post('danh-sach-tai-lieu/tim-kiem', 'eCInternalController@searchDocumentList');
                Route::post('danh-sach-tai-lieu/xoa-nhieu', 'eCInternalController@deleteMultiDocumentList');

                Route::post('ky-so-tai-lieu/khoi-tao', 'eCInternalController@initSignManage');
                Route::post('ky-so-tai-lieu/tim-kiem', 'eCInternalController@searchSignManage');
                Route::post('ky-so-tai-lieu/ky-so', 'eCInternalController@signDocument');
                Route::post('ky-so-tai-lieu/ky-so-nhieu', 'eCInternalController@signMultiDocument');

                Route::post('phe-duyet-tai-lieu/khoi-tao', 'eCInternalController@initApprovalManage');
                Route::post('phe-duyet-tai-lieu/tim-kiem', 'eCInternalController@searchApprovalManage');
                Route::post('phe-duyet-tai-lieu/phe-duyet', 'eCInternalController@approveDenyApprovalDocument');
                Route::post('phe-duyet-tai-lieu/phe-duyet-nhieu', 'eCInternalController@approveDenyMultiApprovalDocument');

                Route::post('lich-su-gui-email/khoi-tao', 'eCInternalController@initSendEmail');
                Route::post('lich-su-gui-email/tim-kiem', 'eCInternalController@searchSendEmail');
                Route::post('lich-su-gui-email/gui', 'eCInternalController@sendSingleSendEmail');
                Route::post('lich-su-gui-email/gui-nhieu', 'eCInternalController@sendMultiSendEmail');
                Route::post('lich-su-gui-email/xoa', 'eCInternalController@deleteSendEmail');
                Route::post('lich-su-gui-email/xoa-nhieu', 'eCInternalController@deleteMultiSendEmail');
                Route::post('lich-su-gui-sms/khoi-tao', 'eCInternalController@initSendSms');
                Route::post('lich-su-gui-sms/tim-kiem', 'eCInternalController@searchSendSms');
                Route::post('lich-su-gui-sms/gui', 'eCInternalController@sendSingleSendSms');
                Route::post('lich-su-gui-sms/gui-nhieu', 'eCInternalController@sendMultiSendSms');
                Route::post('lich-su-gui-sms/xoa', 'eCInternalController@deleteSendSms');
                Route::post('lich-su-gui-sms/xoa-nhieu', 'eCInternalController@deleteMultiSendSms');
            });

            Route::prefix('thuong-mai')->group(function() {
                Route::post('tao-moi/khoi-tao', 'eCCommerceController@initCreateCommerceDocument');
                Route::post('tao-moi/lay-so-tai-lieu', 'eCCommerceController@getDocumentCodeByDocumentTypeId');
                Route::post('tao-moi/chi-tiet-tai-lieu-mau', 'eCCommerceController@getDetailDocumentSampleById');
                Route::post('tao-moi/upload-file', 'eCCommerceController@uploadDocumentFiles');
                Route::post('tao-moi/tai-tai-lieu-ky', 'eCCommerceController@getSignDocument');
                Route::post('tao-moi/hoan-thanh-buoc-1', 'eCCommerceController@goStep2');
                Route::post('tao-moi/hoan-thanh-buoc-2', 'eCCommerceController@goStep3');
                Route::post('tao-moi/hoan-thanh-soan-thao', 'eCCommerceController@finishDrafting');
                Route::post('tao-moi/tao-moi-tai-lieu', 'eCCommerceController@createDocumentFromTemplate');
                Route::post('tao-moi/chi-tiet-tai-lieu-cha', 'eCCommerceController@getFatherInfo');

                Route::post('chi-tiet/khoi-tao', 'eCCommerceController@initViewCommerceDocument');
                Route::post('chi-tiet/tim-kiem', 'eCCommerceController@searchDocumentList');
                Route::post('phe-duyet', 'eCCommerceController@approveDocument');
                Route::post('tu-choi', 'eCCommerceController@denyDocument');
                Route::post('ky-so', 'eCCommerceController@signDocument');
                Route::post('ma-hoa-tai-lieu', 'eCCommerceController@hashDoc');
                Route::post('chinh-sua', 'eCCommerceController@editDenyDocument');

                Route::post('chon-chu-ky', 'eCCommerceController@selectSignature');
                Route::post('cap-nhat-chu-ky', 'eCCommerceController@updateSignatureLocation');
                Route::post('ky-so-kyc', 'eCCommerceController@signKycDocument');
                Route::post('ky-so-otp', 'eCCommerceController@signOtpDocument');
                Route::post('gui-otp', 'eCCommerceController@sendOtp');
                Route::post('xac-minh-ocr', 'eCCommerceController@verifyOcr');
                Route::post('xoa-tai-lieu', 'eCCommerceController@removeDocumentFile');
                Route::post('lay-list-chung-thu-so', 'eCCommerceController@getListCts');
                Route::post('ky-my-sign', 'eCCommerceController@signMySignDocument');
                Route::post('dang-ky-cts-ica', 'eCCommerceController@registerCTS');
                Route::post('ky-ica', 'eCCommerceController@signICA');

                Route::post('danh-sach-tai-lieu/khoi-tao', 'eCCommerceController@initDocumentList');
                Route::post('danh-sach-tai-lieu/tim-kiem', 'eCCommerceController@searchDocumentList');
                Route::post('danh-sach-tai-lieu/xoa-nhieu', 'eCCommerceController@deleteMultiDocumentList');

                Route::post('ky-so-tai-lieu/khoi-tao', 'eCCommerceController@initSignManage');
                Route::post('ky-so-tai-lieu/tim-kiem', 'eCCommerceController@searchSignManage');
                Route::post('ky-so-tai-lieu/ky-so', 'eCCommerceController@signDocument');
                Route::post('ky-so-tai-lieu/ky-so-nhieu', 'eCCommerceController@signMultiDocument');


                Route::post('phe-duyet-tai-lieu/khoi-tao', 'eCCommerceController@initApprovalManage');
                Route::post('phe-duyet-tai-lieu/tim-kiem', 'eCCommerceController@searchApprovalManage');
                Route::post('phe-duyet-tai-lieu/phe-duyet', 'eCCommerceController@approveDenyApprovalDocument');
                Route::post('phe-duyet-tai-lieu/phe-duyet-nhieu', 'eCCommerceController@approveDenyMultiApprovalDocument');

                Route::post('lich-su-gui-email/khoi-tao', 'eCCommerceController@initSendEmail');
                Route::post('lich-su-gui-email/tim-kiem', 'eCCommerceController@searchSendEmail');
                Route::post('lich-su-gui-email/gui', 'eCCommerceController@sendSingleSendEmail');
                Route::post('lich-su-gui-email/gui-nhieu', 'eCCommerceController@sendMultiSendEmail');
                Route::post('lich-su-gui-email/xoa', 'eCCommerceController@deleteSendEmail');
                Route::post('lich-su-gui-email/xoa-nhieu', 'eCCommerceController@deleteMultiSendEmail');
                Route::post('lich-su-gui-sms/khoi-tao', 'eCCommerceController@initSendSms');
                Route::post('lich-su-gui-sms/tim-kiem', 'eCCommerceController@searchSendSms');
                Route::post('lich-su-gui-sms/gui', 'eCCommerceController@sendSingleSendSms');
                Route::post('lich-su-gui-sms/gui-nhieu', 'eCCommerceController@sendMultiSendSms');
                Route::post('lich-su-gui-sms/xoa', 'eCCommerceController@deleteSendSms');
                Route::post('lich-su-gui-sms/xoa-nhieu', 'eCCommerceController@deleteMultiSendSms');
            });

            Route::prefix('xu-ly-tai-lieu')->group(function() {
                Route::post('tai-lieu-sap-qua-han/khoi-tao', 'eNearExpireDocumentController@initDocumentList');
                Route::post('tai-lieu-sap-qua-han/tim-kiem', 'eNearExpireDocumentController@searchDocumentList');
                Route::post('tai-lieu-sap-qua-han/thay-doi-nhom', 'eNearExpireDocumentController@changeDocumentGroup');
                Route::post('tai-lieu-sap-qua-han/gia-han-nhieu', 'eNearExpireDocumentController@renewMultiDocumentList');
                Route::post('tai-lieu-sap-het-hieu-luc/khoi-tao', 'eNearDocExpireDocumentController@initDocumentList');
                Route::post('tai-lieu-sap-het-hieu-luc/tim-kiem', 'eNearDocExpireDocumentController@searchDocumentList');
                Route::post('tai-lieu-sap-het-hieu-luc/thay-doi-nhom', 'eNearDocExpireDocumentController@changeDocumentGroup');
                Route::post('tai-lieu-sap-het-hieu-luc/gia-han-nhieu', 'eNearDocExpireDocumentController@renewMultiDocumentList');
                Route::post('tai-lieu-het-hieu-luc/khoi-tao', 'eDocExpiredDocumentController@initDocumentList');
                Route::post('tai-lieu-het-hieu-luc/tim-kiem', 'eDocExpiredDocumentController@searchDocumentList');
                Route::post('tai-lieu-het-hieu-luc/thay-doi-nhom', 'eDocExpiredDocumentController@changeDocumentGroup');
                Route::post('tai-lieu-het-hieu-luc/xoa-nhieu', 'eDocExpiredDocumentController@deleteMultiDocumentList');
                Route::post('tai-lieu-bi-qua-han/khoi-tao', 'eExpiredDocumentController@initDocumentList');
                Route::post('tai-lieu-bi-qua-han/tim-kiem', 'eExpiredDocumentController@searchDocumentList');
                Route::post('tai-lieu-bi-qua-han/thay-doi-nhom', 'eExpiredDocumentController@changeDocumentGroup');
                Route::post('tai-lieu-bi-qua-han/xoa-nhieu', 'eExpiredDocumentController@deleteMultiDocumentList');
                Route::post('tai-lieu-bi-qua-han/gui-lai-nhieu', 'eExpiredDocumentController@sendMultiDocumentList');

                Route::post('tai-lieu-bi-tu-choi/khoi-tao', 'eDenyDocumentController@initDocumentList');
                Route::post('tai-lieu-bi-tu-choi/tim-kiem', 'eDenyDocumentController@searchDocumentList');
                Route::post('tai-lieu-bi-tu-choi/thay-doi-nhom', 'eDenyDocumentController@changeDocumentGroup');
                Route::post('tai-lieu-bi-tu-choi/xoa-nhieu', 'eDenyDocumentController@deleteMultiDocumentList');
                Route::post('tai-lieu-bi-tu-choi/gui-lai-nhieu', 'eDenyDocumentController@sendMultiDocumentList');

                Route::post('tai-lieu-bi-huy-bo/khoi-tao', 'eDeleteDocumentController@initDocumentList');
                Route::post('tai-lieu-bi-huy-bo/tim-kiem', 'eDeleteDocumentController@searchDocumentList');
                Route::post('tai-lieu-bi-huy-bo/thay-doi-nhom', 'eDeleteDocumentController@changeDocumentGroup');
                Route::post('tai-lieu-bi-huy-bo/khoi-phuc-nhieu', 'eDeleteDocumentController@restoreMultiDocumentList');
            });

            Route::prefix('bao-cao')->group(function(){
                Route::post('bao-cao-tai-lieu-noi-bo/khoi-tao', 'eCReportController@initInternalDocument');
                Route::post('bao-cao-tai-lieu-noi-bo/tim-kiem', 'eCReportController@searchInternalDocument');
                Route::post('bao-cao-tai-lieu-noi-bo/xuat-bao-cao', 'eCReportController@exportInternalDocument');
                Route::post('bao-cao-tai-lieu-thuong-mai/khoi-tao', 'eCReportController@initCommerceDocument');
                Route::post('bao-cao-tai-lieu-thuong-mai/tim-kiem', 'eCReportController@searchCommerceDocument');
                Route::post('bao-cao-tai-lieu-thuong-mai/xuat-bao-cao', 'eCReportController@exportCommerceDocument');
                Route::post('bao-cao-gui-tin/khoi-tao', 'eCReportController@initSendMessage');
                Route::post('bao-cao-gui-tin/tim-kiem', 'eCReportController@searchSendMessage');
                Route::post('bao-cao-gui-tin/xuat-bao-cao', 'eCReportController@exportSendMessage');
                Route::post('bao-cao-ekyc/khoi-tao', 'eCReportController@initSignEkyc');
                Route::post('bao-cao-ekyc/tim-kiem', 'eCReportController@searchSignEkyc');
                Route::post('bao-cao-ekyc/xuat-bao-cao', 'eCReportController@exportSignEkyc');
                Route::post('danh-sach-nguoi-ky/khoi-tao', 'eCReportController@initSignAssignee');
                Route::post('danh-sach-nguoi-ky/tim-kiem', 'eCReportController@searchSignAssignee');
                Route::post('danh-sach-nguoi-ky/xuat-danh-sach', 'eCReportController@exportSignAssignee');
            });

            Route::prefix('quan-ly-tai-lieu-mau')->group(function(){
                Route::get('khoi-tao', 'eCDocumentSampleController@initDocumentSample');
                Route::post('tim-kiem', 'eCDocumentSampleController@searchDocumentSample');
                Route::post('them-moi', 'eCDocumentSampleController@createNewDocumentSample');
                Route::post('cap-nhat', 'eCDocumentSampleController@updateDocumentSample');
                Route::post('xoa', 'eCDocumentSampleController@deleteDocumentSample');
                Route::post('xoa-nhieu', 'eCDocumentSampleController@deleteMultiDocumentSample');
                Route::post('upload-file', 'eCDocumentSampleController@uploadDocumentFiles');
                Route::post('lay-file', 'eCDocumentSampleController@getFileSampleDocument');
                Route::post('xoa-tai-lieu', 'eCDocumentSampleController@removeDocumentFile');
                Route::post('chi-tiet', 'eCDocumentSampleController@getDocumentDetail');
                Route::post('tai-tai-lieu', 'eCDocumentSampleController@getSampleDocument');
                Route::post('luu-chi-tiet', 'eCDocumentSampleController@saveDetailSampleDocument');
            });

            Route::prefix('tai-lieu-huong-dan')->group(function(){
                Route::get('khoi-tao', 'eCGuideController@initGuide');
                Route::post('tim-kiem', 'eCGuideController@searchGuide');
                Route::post('chi-tiet', 'eCGuideController@getDocumentDetail');
                Route::post('tai-tai-lieu', 'eCGuideController@getGuideDocument');
            });

            Route::prefix('video-huong-dan')->group(function(){
                Route::get('khoi-tao', 'eCGuideController@initGuideVideo');
                Route::post('tim-kiem', 'eCGuideController@searchGuideVideo');
                Route::post('chi-tiet', 'eCGuideController@getGuideVideoDetail');
            });

            Route::prefix('lich-su-tac-dong')->group(function(){
                Route::get('khoi-tao', 'eCLogController@initLog');
                Route::post('tim-kiem', 'eCLogController@searchLog');
            });

        });
    });
});
