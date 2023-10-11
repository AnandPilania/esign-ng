<?php

Route::group(['prefix' => 'api/v1/assignee', 'namespace' => 'Customer\Controllers'], function () {

    Route::post('login', 'eCDocumentAssigneeController@login');
    Route::post('test1', 'eCTestController@uploadFile');

    Route::group(['middleware' => ['mix_api:jwt', 'cors']], function () {
        Route::post('auth-check', 'eCDocumentAssigneeController@isLogin');
        Route::post('logout', 'eCDocumentAssigneeController@logout');
        Route::post('init', 'eCDocumentAssigneeController@init');
        Route::post('getConfigData', 'eCDocumentAssigneeController@getConfigData');
        Route::post('getSignDocument', 'eCDocumentAssigneeController@getSignDocument');
        Route::post('updateSignature', 'eCDocumentAssigneeController@updateSignature');
        Route::post('saveSignatureLocation', 'eCDocumentAssigneeController@saveSignatureLocation');
        Route::post('approval', 'eCDocumentAssigneeController@approval');
        Route::post('signToken', 'eCDocumentAssigneeController@signToken');
        Route::post('deny', 'eCDocumentAssigneeController@deny');
        Route::post('sendOtp', 'eCDocumentAssigneeController@sendOtp');
        Route::post('signOtp', 'eCDocumentAssigneeController@signOtp');
        Route::post('transferDocument', 'eCDocumentAssigneeController@transferDocument');
        Route::post('verifyOcr', 'eCDocumentAssigneeController@verifyOcr');
        Route::post('signKyc', 'eCDocumentAssigneeController@signKyc');
        Route::post('encodeDocument', 'eCDocumentAssigneeController@hashDoc');
        Route::post('addApprovalAssignee', 'eCDocumentAssigneeController@addApprovalAssignee');
        Route::post('getListCts', 'eCDocumentAssigneeController@getListCts');
        Route::post('signMySign', 'eCDocumentAssigneeController@signMySignDocument');
        Route::post('dang-ky-cts-ica', 'eCDocumentAssigneeController@registerCTS');
        Route::post('ky-ica', 'eCDocumentAssigneeController@signICA');

        Route::prefix('tai-lieu-huong-dan')->group(function () {
            Route::get('khoi-tao', 'eCDocumentAssigneeController@initGuide');
            Route::post('tim-kiem', 'eCDocumentAssigneeController@searchGuide');
            Route::post('chi-tiet', 'eCDocumentAssigneeController@getDocumentDetail');
            Route::post('tai-tai-lieu', 'eCDocumentAssigneeController@getGuideDocument');
        });

        Route::prefix('video-huong-dan')->group(function () {
            Route::get('khoi-tao', 'eCDocumentAssigneeController@initGuideVideo');
            Route::post('tim-kiem', 'eCDocumentAssigneeController@searchGuideVideo');
            Route::post('chi-tiet', 'eCDocumentAssigneeController@getGuideVideoDetail');
        });

    });

});
