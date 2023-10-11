<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/vendor')->namespace('Viettel\Controllers')->group(function () {
    // Authentication Routes...
    Route::post('login', 'VTLoginController@login');

    Route::group(['middleware' => ['viettel_api:jwt', 'viettel']], function () {
        Route::post('document_templates', 'VTApiController@getDocumentTemplate');
        Route::post('choose_template', 'VTApiController@getDetailTemplate');
        Route::post('create_document', 'VTApiController@createDocumentFromTemplate');
        Route::post('documents', 'VTApiController@searchDocumentList');
        Route::post('document', 'VTApiController@getViewDocument');
        Route::post('download_document', 'VTApiController@downloadDocument');
        Route::post('idcard_check', 'VTApiController@checkIdCard');
        Route::post('idcard_ocr', 'VTApiController@ocrIdCard');
        Route::post('sign_ekyc', 'VTApiController@signEkyc');

    });
});
