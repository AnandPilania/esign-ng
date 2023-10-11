<?php

Route::group(['prefix' => 'api/v1/search', 'namespace' => 'Customer\Controllers'], function () {

    Route::post('login', 'eCSearchController@login');
    Route::post('test1', 'eCTestController@uploadFile');
    Route::post('getPassword', 'eCSearchController@getPassword');
    Route::post('forgetPassword', 'eCSearchController@forgetPassword');

    Route::group(['middleware' => ['mix_api:jwt', 'cors']], function () {
        Route::get('auth-check', 'eCSearchController@isLogin');
        Route::get('logout', 'eCSearchController@logout');
        Route::get('init', 'eCSearchController@initData');
        Route::post('changeUserInfo', 'eCSearchController@changeUserInfo');
        Route::post('changePwd', 'eCSearchController@changePwd');
        Route::post('updateUserSignature', 'eCSearchController@updateUserSignature');
        Route::post('tim-kiem', 'eCSearchController@searchDocument');
        Route::post('khoi-tao', 'eCSearchController@initDocumentList');
        Route::post('chi-tiet/khoi-tao', 'eCSearchController@initViewDocument');
        Route::post('chi-tiet/tai-tai-lieu-ky', 'eCSearchController@getSignDocument');
        Route::post('lich-su-tai-lieu', 'eCSearchController@getHistoryDocument');
        Route::post('lich-su-xac-thuc', 'eCSearchController@getHistoryTransactionDocument');
    });
});
