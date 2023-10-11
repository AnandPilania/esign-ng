<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/admin')->namespace('Admin\Controllers')->group(function () {
    // Authentication Routes...
    Route::post('login', 'LoginController@login');

    Route::group(['middleware' => ['admin_api:jwt', 'admin']], function () {
        Route::get('auth-check', 'LoginController@isLogin');
        Route::get('init', 'LoginController@initData');
        Route::post('initDashboard', 'LoginController@initDashboardData');
        Route::post('changeUserInfo', 'LoginController@changeUserInfo');
        Route::post('changePwd', 'LoginController@changePwd');
        Route::get('logout', 'LoginController@logout');

        Route::prefix('thiet-lap')->group(function(){
            Route::get('quan-ly-khach-hang/khoi-tao/', 'CustomerController@init');
            Route::post('quan-ly-khach-hang/tim-kiem/', 'CustomerController@search');
            Route::post('quan-ly-khach-hang/them-moi', 'CustomerController@create');
            Route::post('quan-ly-khach-hang/cap-nhat', 'CustomerController@update');
            Route::post('quan-ly-khach-hang/xoa', 'CustomerController@delete');
            Route::post('quan-ly-khach-hang/xoa-nhieu', 'CustomerController@deleteMulti');
            Route::post('quan-ly-khach-hang/chi-tiet-dich-vu', 'CustomerController@getServiceDetail');
            Route::post('quan-ly-khach-hang/danh-sach-tai-lieu', 'CustomerController@getDocumentList');
            Route::post('quan-ly-khach-hang/lay-cau-hinh-company', 'CustomerController@getDataConfigCompany');
            Route::post('quan-ly-khach-hang/cau-hinh-company', 'CustomerController@updateConfigCompany');
            Route::post('quan-ly-khach-hang/doi-mat-khau', 'CustomerController@changePassword');
            Route::post('quan-ly-khach-hang/gia-han-goi-cuoc', 'CustomerController@reNewService');

            Route::get('quan-ly-dang-nhap-nguoi-dung/khoi-tao/', 'AdminConfigController@initUser');
            Route::post('quan-ly-dang-nhap-nguoi-dung/tim-kiem/', 'AdminConfigController@searchUser');
            Route::post('quan-ly-dang-nhap-nguoi-dung/them-moi', 'AdminConfigController@createUser');
            Route::post('quan-ly-dang-nhap-nguoi-dung/cap-nhat', 'AdminConfigController@updateUser');
            Route::post('quan-ly-dang-nhap-nguoi-dung/doi-mat-khau', 'AdminConfigController@changePasswordUser');
            Route::post('quan-ly-dang-nhap-nguoi-dung/xoa', 'AdminConfigController@deleteUser');
            Route::post('quan-ly-dang-nhap-nguoi-dung/xoa-nhieu', 'AdminConfigController@deleteMultiUser');
            Route::get('quan-ly-mau-thong-bao/khoi-tao/', 'AdminConfigController@initTemplate');
            Route::post('quan-ly-mau-thong-bao/tim-kiem/', 'AdminConfigController@searchTemplate');
            Route::post('quan-ly-mau-thong-bao/them-moi', 'AdminConfigController@createTemplate');
            Route::post('quan-ly-mau-thong-bao/cap-nhat', 'AdminConfigController@updateTemplate');
            Route::post('quan-ly-mau-thong-bao/xoa', 'AdminConfigController@deleteTemplate');
            Route::post('quan-ly-mau-thong-bao/xoa-nhieu', 'AdminConfigController@deleteMultiTemplate');
            Route::get('quan-ly-dai-ly/khoi-tao/', 'AdminConfigController@initAgency');
            Route::post('quan-ly-dai-ly/tim-kiem/', 'AdminConfigController@searchAgency');
            Route::post('quan-ly-dai-ly/them-moi', 'AdminConfigController@createAgency');
            Route::post('quan-ly-dai-ly/cap-nhat', 'AdminConfigController@updateAgency');
            Route::post('quan-ly-dai-ly/xoa', 'AdminConfigController@deleteAgency');
            Route::post('quan-ly-dai-ly/xoa-nhieu', 'AdminConfigController@deleteMultiAgency');
            Route::post('quan-ly-dai-ly/cap-nhat-trang-thai', 'AdminConfigController@changeAgencyStatus');
            Route::get('quan-ly-goi-cuoc/khoi-tao/', 'AdminConfigController@initServiceConfig');
            Route::post('quan-ly-goi-cuoc/tim-kiem/', 'AdminConfigController@searchServiceConfig');
            Route::post('quan-ly-goi-cuoc/them-moi', 'AdminConfigController@createServiceConfig');
            Route::post('quan-ly-goi-cuoc/cap-nhat', 'AdminConfigController@updateServiceConfig');
            Route::post('quan-ly-goi-cuoc/xoa', 'AdminConfigController@deleteServiceConfig');
            Route::post('quan-ly-goi-cuoc/xoa-nhieu', 'AdminConfigController@deleteMultiServiceConfig');
            Route::post('quan-ly-goi-cuoc/cap-nhat-trang-thai', 'AdminConfigController@changeServiceConfigStatus');
            Route::post('quan-ly-goi-cuoc/chi-tiet-goi-cuoc', 'AdminConfigController@getServiceConfigDetail');
            Route::post('quan-ly-goi-cuoc/them-thiet-lap-cuoc', 'AdminConfigController@saveServiceConfigDetail');
            Route::post('quan-ly-goi-cuoc/xoa-thiet-lap-cuoc', 'AdminConfigController@deleteServiceConfigDetail');

            Route::get('quan-ly-tai-lieu-huong-dan/khoi-tao', 'AdminConfigController@initDocumentTutorial');
            Route::post('quan-ly-tai-lieu-huong-dan/tim-kiem', 'AdminConfigController@searchDocumentTutorial');
            Route::post('quan-ly-tai-lieu-huong-dan/them-moi', 'AdminConfigController@createNewDocumentTutorial');
            Route::post('quan-ly-tai-lieu-huong-dan/cap-nhat', 'AdminConfigController@updateDocumentTutorial');
            Route::post('quan-ly-tai-lieu-huong-dan/xoa', 'AdminConfigController@deleteDocumentTutorial');
            Route::post('quan-ly-tai-lieu-huong-dan/xoa-nhieu', 'AdminConfigController@deleteMultiDocumentTutorial');
            Route::post('quan-ly-tai-lieu-huong-dan/upload-file', 'AdminConfigController@uploadDocumentFiles');
            Route::post('quan-ly-tai-lieu-huong-dan/xoa-tai-lieu', 'AdminConfigController@removeDocumentFile');
            Route::post('quan-ly-tai-lieu-huong-dan/chi-tiet', 'AdminConfigController@getDocumentDetail');
            Route::post('quan-ly-tai-lieu-huong-dan/tai-tai-lieu', 'AdminConfigController@getTutorialDocument');

            Route::get('quan-ly-video-huong-dan/khoi-tao', 'AdminConfigController@initGuideVideo');
            Route::post('quan-ly-video-huong-dan/tim-kiem', 'AdminConfigController@searchGuideVideo');
            Route::post('quan-ly-video-huong-dan/them-moi', 'AdminConfigController@createNewGuideVideo');
            Route::post('quan-ly-video-huong-dan/cap-nhat', 'AdminConfigController@updateGuideVideo');
            Route::post('quan-ly-video-huong-dan/xoa', 'AdminConfigController@deleteGuideVideo');
            Route::post('quan-ly-video-huong-dan/xoa-nhieu', 'AdminConfigController@deleteMultiGuideVideo');
            Route::post('quan-ly-video-huong-dan/chi-tiet', 'AdminConfigController@getGuideVideoDetail');
        });

        Route::prefix('bao-cao')->group(function(){
            Route::get('khoi-tao', 'ReportController@init');
            Route::post('tim-kiem', 'ReportController@search');
            Route::post('xuat-bao-cao', 'ReportController@export');
        });
    });
});
