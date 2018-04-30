<?php

Route::group(
    [
        'namespace' => 'Sarfraznawaz2005\GitUp\Http\Controllers',
        'prefix' => config('gitup.route', 'gitup')
    ],
    function () {
        Route::get('/', 'GitUpController@index')->name('__gitup__');

        Route::get('commits', 'GitUpController@getCommits')->name('gitup_commits');
        Route::get('files/{commitid?}', 'GitUpController@getFiles')->name('gitup_files');

        Route::post('preview', 'GitUpController@previewUploadFiles')->name('gitup_preview');
        Route::post('upload', 'GitUpController@uploadFiles')->name('gitup_upload');

        Route::get('statistics', 'GitUpController@statistics')->name('gitup_statistics');
    }
);
