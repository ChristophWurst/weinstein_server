<?php

Route::get('', [
    'as' => 'start',
    'uses' => 'StartController@index',
]);

Route::group(['prefix' => 'competition/{competition}', 'middleware' => 'auth'], function () {
    Route::get('', [
        'as' => 'competition/show',
        'uses' => 'CompetitionController@show',
    ]);
    Route::get('complete-tasting/{tasting}', [
        'as' => 'competition/complete-tasting',
        'uses' => 'CompetitionController@completeTasting',
    ]);
    Route::post('complete-tasting/{tasting}', [
        'uses' => 'CompetitionController@lockTasting',
    ]);
    Route::get('complete-tastingnumbers/{tasting}', [
        'as' => 'competition/complete-tastingnumbers',
        'uses' => 'CompetitionController@completeTastingNumbers',
    ]);
    Route::post('complete-tastingnumbers/{tasting}', [
        'uses' => 'CompetitionController@lockTastingnumbers',
    ]);
    Route::get('complete-kdb', [
        'as' => 'competition/complete-kdb',
        'uses' => 'CompetitionController@completeKdb',
    ]);
    Route::post('complete-kdb', [
        'uses' => 'CompetitionController@lockKdb',
    ]);
    Route::get('complete-excluded', [
        'as' => 'competition/complete-excluded',
        'uses' => 'CompetitionController@completeExcluded',
    ]);
    Route::post('complete-excluded', [
        'uses' => 'CompetitionController@lockExcluded',
    ]);
    Route::get('complete-sosi', [
        'as' => 'competition/complete-sosi',
        'uses' => 'CompetitionController@completeSosi',
    ]);
    Route::post('complete-sosi', [
        'uses' => 'CompetitionController@lockSosi',
    ]);
    Route::get('sign-chosen', [
        'as' => 'competition/sign-chosen',
        'uses' => 'CompetitionController@showSignChosen',
    ]);
    Route::post('sign-choosing/{association}', [
        'as' => 'competition/sign-chosen-submit',
        'uses' => 'CompetitionController@signChosen',
    ]);
    Route::get('complete-choosing', [
        'as' => 'competition/complete-choosing',
        'uses' => 'CompetitionController@completeChoosing',
    ]);
    Route::post('complete-choosing', [
        'uses' => 'CompetitionController@lockChoosing',
    ]);
    Route::get('complete-catalogue-numbers', [
        'as' => 'competition/complete-catalogue-numbers',
        'uses' => 'CompetitionController@completeCatalogueNumbers',
    ]);
    Route::post('complete-catalogue-numbers', [
        'uses' => 'CompetitionController@lockCatalogueNumbers',
    ]);
    Route::get('reset', [
        'as' => 'competition/reset',
        'uses' => 'CompetitionController@getReset',
    ]);
    Route::post('reset', [
        'uses' => 'CompetitionController@postReset',
    ]);

    /*
     * Wines
     */
    Route::group(['prefix' => 'wines'], function () {
        Route::get('', [
            'as' => 'enrollment.wines',
            'uses' => 'WineController@index',
        ]);
        Route::get('redirect/{nr}', [
            'as' => 'enrollment.wines/redirect',
            'uses' => 'WineController@redirect',
        ]);
        Route::get('import-kdb', [
            'as' => 'enrollment.wines/import-kdb',
            'uses' => 'WineController@importKdb',
        ]);
        Route::post('import-kdb', [
            'uses' => 'WineController@importKdbStore',
        ]);
        Route::get('import-excluded', [
            'as' => 'enrollment.wines/import-excluded',
            'uses' => 'WineController@importExcluded',
        ]);
        Route::post('import-excluded', [
            'uses' => 'WineController@importExcludedStore',
        ]);
        Route::get('import-sosi', [
            'as' => 'enrollment.wines/import-sosi',
            'uses' => 'WineController@importSosi',
        ]);
        Route::post('import-sosi', [
            'uses' => 'WineController@importSosiStore',
        ]);

        Route::get('create', [
            'as' => 'enrollment.wines/create',
            'uses' => 'WineController@create',
        ]);
        Route::post('create', [
            'uses' => 'WineController@store',
        ]);
        Route::get('export', [
            'as' => 'enrollment.wines/export',
            'uses' => 'WineController@exportAll',
        ]);
        Route::get('export-kdb', [
            'as' => 'enrollment.wines/export-kdb',
            'uses' => 'WineController@exportKdb',
        ]);
        Route::get('export-sosi', [
            'as' => 'enrollment.wines/export-sosi',
            'uses' => 'WineController@exportSosi',
        ]);
        Route::get('export-chosen', [
            'as' => 'enrollment.wines/export-chosen',
            'uses' => 'WineController@exportChosen',
        ]);
        Route::get('export-flaws', [
            'as' => 'enrollment.wines/export-flaws',
            'uses' => 'WineController@exportFlaws',
        ]);
    });

    /*
     * Tasting Numbers
     */
    Route::group(['prefix' => 'numbers'], function () {
        Route::get('', [
            'as' => 'tasting.numbers',
            'uses' => 'TastingNumberController@index',
        ]);
        Route::get('assign', [
            'as' => 'tasting.numbers/assign',
            'uses' => 'TastingNumberController@assign',
        ]);
        Route::post('assign', [
            'uses' => 'TastingNumberController@store',
        ]);
        Route::get('reset', [
            'as' => 'tasting.numbers/reset',
            'uses' => 'TastingNumberController@resetForm',
        ]);
        Route::post('reset', [
            'uses' => 'TastingNumberController@reset',
        ]);
        Route::get('import', [
            'as' => 'tasting.numbers/import',
            'uses' => 'TastingNumberController@import',
        ]);
        Route::post('import', [
            'uses' => 'TastingNumberController@importStore',
        ]);
        Route::get('translate/{id}', [
            'as' => 'tasting.numbers/translate',
            'uses' => 'TastingNumberController@translate',
        ]);
    });
    /*
     * Tasting sessions
     */
    Route::group(['prefix' => 'sessions'], function () {
        Route::get('', [
            'as' => 'tasting.sessions',
            'uses' => 'TastingSessionController@index',
        ]);
        Route::get('add', [
            'as' => 'tasting.sessions/add',
            'uses' => 'TastingSessionController@add',
        ]);
        Route::post('add', [
            'uses' => 'TastingSessionController@store',
        ]);
    });

    /*
     * Catalogue Number assignment
     */
    Route::group(['prefix' => 'cataloguenumbers'], function () {
        Route::get('import', [
            'as' => 'cataloguenumbers.import',
            'uses' => 'CatalogueNumberController@import',
        ]);
        Route::post('import', [
            'uses' => 'CatalogueNumberController@store',
        ]);
    });

    Route::get('evaluations', [
        'as' => 'evaluation',
        'uses' => 'EvaluationController@index',
    ]);

    /*
     * Tasting protocols
     */
    Route::group(['prefix' => 'protocols'], function () {
        Route::get('', [
            'as' => 'evaluation.protocols',
            'uses' => 'EvaluationController@protocols',
        ]);
    });
    /*
     * Catalogues
     */
    Route::group(['prefix' => 'catalogues'], function () {
        Route::get('tasting', [
            'as' => 'evaluation.catalogues/tasting',
            'uses' => 'CatalogueController@tastingCatalogue',
        ]);
        Route::get('web', [
            'as' => 'evaluation.catalogues/web',
            'uses' => 'CatalogueController@webCatalogue',
        ]);
        Route::get('address', [
            'as' => 'evaluation.catalogues/address',
            'uses' => 'CatalogueController@addressCatalogue',
        ]);
    });
});

Route::group(['middleware' => 'auth'], function () {

    /*
     * Wines
     */
    Route::group(['prefix' => 'wines/{wine}'], function () {
        Route::get('', [
            'as' => 'enrollment.wines/show',
            'uses' => 'WineController@show',
        ]);
        Route::get('enrollment-pdf', [
            'as' => 'enrollment.wines/enrollment-pdf',
            'uses' => 'WineController@enrollmentPdf',
        ]);
        Route::get('edit', [
            'as' => 'enrollment.wines/edit',
            'uses' => 'WineController@edit',
        ]);
        Route::post('edit', [
            'uses' => 'WineController@update',
        ]);
        Route::get('delete', [
            'as' => 'enrollment.wines/delete',
            'uses' => 'WineController@delete',
        ]);
        Route::post('delete', [
            'uses' => 'WineController@destroy',
        ]);
        Route::post('update-kdb', [
            'as' => 'enrollment.wines/update-kdb',
            'uses' => 'WineController@updateKdb',
        ]);
        Route::post('update-excluded', [
            'as' => 'enrollment.wines/update-excluded',
            'uses' => 'WineController@updateExcluded',
        ]);
        Route::post('update-sosi', [
            'as' => 'enrollment.wines/update-sosi',
            'uses' => 'WineController@updateSosi',
        ]);
    });

    /*
     * Tasting Numbers
     */
    Route::group(['prefix' => 'number/{tastingnumber}'], function () {
        Route::get('deallocate', [
            'as' => 'tasting.numbers/deallocate',
            'uses' => 'TastingNumberController@deallocate',
        ]);
        Route::post('deallocate', [
            'uses' => 'TastingNumberController@delete',
        ]);
    });
    /*
     * Tasting sessions
     */
    Route::group(['prefix' => 'session/{tastingsession}'], function () {
        Route::get('', [
            'as' => 'tasting.session/show',
            'uses' => 'TastingSessionController@show',
        ]);
        Route::get('edit', [
            'as' => 'tasting.sessions/edit',
            'uses' => 'TastingSessionController@edit',
        ]);
        Route::post('edit', [
            'uses' => 'TastingSessionController@update',
        ]);
        Route::get('complete', [
            'as' => 'tasting.sessions/complete',
            'uses' => 'TastingSessionController@complete',
        ]);
        Route::post('complete', [
            'uses' => 'TastingSessionController@lock',
        ]);
        Route::get('delete', [
            'as' => 'tasting.sessions/delete',
            'uses' => 'TastingSessionController@delete',
        ]);
        Route::post('delete', [
            'uses' => 'TastingSessionController@destroy',
        ]);
        Route::get('export-result/{commission}', [
            'as' => 'tasting.sessions/export-result',
            'uses' => 'TastingSessionController@exportResult',
        ]);
        Route::get('export-protocol', [
            'as' => 'tasting.sessions/protocol',
            'uses' => 'TastingSessionController@exportProtocol',
        ]);
        Route::get('statistics', [
            'as' => 'tasting.sessions/statistics',
            'uses' => 'TastingSessionController@statistics',
        ]);
        Route::group(['prefix' => 'taste'], function () {
            Route::get('', [
                'as' => 'tasting.session/taste',
                'uses' => 'TastingController@add',
            ]);
            Route::post('', [
                'uses' => 'TastingController@store',
            ]);
        });
        Route::group(['prefix' => 'retaste/{tastingnumber}/commission/{commission}'], function () {
            Route::get('', [
                'as' => 'tasting.session/retaste',
                'uses' => 'TastingController@edit',
            ]);
            Route::post('', [
                'uses' => 'TastingController@update',
            ]);
        });
    });
    Route::resource('tasters', 'TasterController', [
        'only' => [
            'index',
            'store',
            'update',
        ],
    ]);
    Route::resource('wines', 'WineApiController', ['only' => [
        'index',
        'update',
    ]]);
});

/*
  |--------------------------------------------------------------------------
  | Settings
  |--------------------------------------------------------------------------
 */
Route::group(['prefix' => 'settings', 'middleware' => 'auth'], function () {
    Route::get('', [
        'as' => 'settings',
        'uses' => 'SettingsController@index', ]
    );

    /*
     * Activity log
     */
    Route::get('activitylog', [
        'as' => 'settings.activitylog',
        'uses' => 'ActivityLogController@index',
    ]);

    /*
     * Applicant
     */
    Route::group(['prefix' => 'applicants'], function () {
        Route::get('', [
            'as' => 'settings.applicants',
            'uses' => 'ApplicantController@index',
        ]);
        Route::get('import', [
            'as' => 'settings.applicants/import',
            'uses' => 'ApplicantController@getImport',
        ]);
        Route::post('import', [
            'uses' => 'ApplicantController@postImport',
        ]);
        Route::get('create', [
            'as' => 'settings.applicants/create',
            'uses' => 'ApplicantController@create',
        ]);
        Route::post('create', [
            'uses' => 'ApplicantController@store',
        ]);
    });

    Route::group(['prefix' => 'applicants/{applicant}'], function () {
        Route::get('', [
            'as' => 'settings.applicant/show',
            'uses' => 'ApplicantController@show',
        ]);
        Route::get('edit', [
            'as' => 'settings.applicants/edit',
            'uses' => 'ApplicantController@edit',
        ]);
        Route::post('edit', [
            'uses' => 'ApplicantController@update',
        ]);
        Route::get('delete', [
            'as' => 'settings.applicants/delete',
            'uses' => 'ApplicantController@delete',
        ]);
        Route::post('delete', [
            'uses' => 'ApplicantController@destroy',
        ]);
    });

    /*
     * Associations
     */
    Route::group(['prefix' => 'associations'], function () {
        Route::get('', [
            'as' => 'settings.associations',
            'uses' => 'AssociationController@index',
        ]);
        Route::get('create', [
            'as' => 'settings.associations/create',
            'uses' => 'AssociationController@create',
        ]);
        Route::post('create', [
            'uses' => 'AssociationController@store',
        ]);
    });
    Route::group(['prefix' => 'association/{association}'], function () {
        Route::get('', [
            'as' => 'settings.association/show',
            'uses' => 'AssociationController@show',
        ]);
        Route::get('edit', [
            'as' => 'settings.associations/edit',
            'uses' => 'AssociationController@edit',
        ]);
        Route::post('edit', [
            'uses' => 'AssociationController@update',
        ]);
    });

    /*
     * Competition
     */
    Route::group(['prefix' => 'competitions'], function () {
        Route::get('', [
            'as' => 'settings.competitions',
            'uses' => 'CompetitionController@index',
        ]);
    });

    /*
     * Download settings
     */
    Route::group(['prefix' => 'downloads'], function () {
        Route::get('', [
            'as' => 'settings.downloads',
            'uses' => 'DownloadSettingsController@index',
        ]);
        Route::get('create', [
            'as' => 'settings.downloads/create',
            'uses' => 'DownloadSettingsController@create',
        ]);
        Route::post('create', [
            'uses' => 'DownloadSettingsController@store',
        ]);
    });
    Route::group(['prefix' => 'download/{download}'], function () {
        Route::get('', [
            'as' => 'settings.download/show',
            'uses' => 'DownloadSettingsController@show',
        ]);
        Route::get('delete', [
            'as' => 'settings.download/delete',
            'uses' => 'DownloadSettingsController@delete',
        ]);
        Route::post('delete', [
            'uses' => 'DownloadSettingsController@destroy',
        ]);
    });

    /*
     * Announcements
     */
    Route::group(['prefix' => 'announcements'], function () {
        Route::get('', [
            'as' => 'settings.announcements',
            'uses' => 'AnnouncementsController@index',
        ]);
        Route::post('', [
            'uses' => 'AnnouncementsController@send',
        ]);
    });

    /*
     * Users
     */
    Route::group(['prefix' => 'users'], function () {
        Route::get('', [
            'as' => 'settings.users',
            'uses' => 'UserController@index',
        ]);
        Route::get('create', [
            'as' => 'settings.users/create',
            'uses' => 'UserController@create',
        ]);
        Route::post('create', [
            'uses' => 'UserController@store',
        ]);
    });
    Route::group(['prefix' => 'user/{user}'], function () {
        Route::get('', [
            'as' => 'settings.user/show',
            'uses' => 'UserController@show',
        ]);
        Route::get('edit', [
            'as' => 'settings.users/edit',
            'uses' => 'UserController@edit',
        ]);
        Route::post('edit', [
            'uses' => 'UserController@update',
        ]);
        Route::get('delete', [
            'as' => 'settings.users/delete',
            'uses' => 'UserController@delete',
        ]);
        Route::post('delete', [
            'uses' => 'UserController@destroy',
        ]);
    });

    /*
     * Sorts
     */
    Route::group(['prefix' => 'winesorts'], function () {
        Route::get('', [
            'as' => 'settings.winesorts',
            'uses' => 'WineSortController@index',
        ]);
        Route::get('create', [
            'as' => 'settings.winesorts/create',
            'uses' => 'WineSortController@create',
        ]);
        Route::post('create', [
            'uses' => 'WineSortController@store',
        ]);
    });
    Route::group(['prefix' => 'winesort/{winesort}'], function () {
        Route::get('edit', [
            'as' => 'settings.winesorts/edit',
            'uses' => 'WineSortController@edit',
        ]);
        Route::post('edit', [
            'uses' => 'WineSortController@update',
        ]);
    });
});

Route::get('downloads', [
    'as' => 'downloads',
    'uses' => 'DownloadsController@index',
    'middleware' => 'auth',
]);
Route::get('account', [
    'as' => 'account',
    'uses' => 'Auth\LoginController@account',
    'middleware' => 'auth',
]);
Route::get('login', [
    'as' => 'login',
    'uses' => 'Auth\LoginController@login',
]);
Route::post('login', [
    'as' => 'postLogin',
    'uses' => 'Auth\LoginController@auth',
]);
Route::get('login/request', [
    'as' => 'password.request-form',
    'uses' => 'Auth\ForgotPasswordController@showLinkRequestForm',
]);
Route::post('login/request', [
    'as' => 'password.request',
    'uses' => 'Auth\ForgotPasswordController@sendResetLinkUsername',
]);
Route::get('login/reset/{token}', [
    'as' => 'password.reset-form',
    'uses' => 'Auth\ResetPasswordController@showResetForm',
]);
Route::post('login/reset', [
    'as' => 'password.reset',
    'uses' => 'Auth\ResetPasswordController@reset',
]);

Route::get('logout', [
    'as' => 'logout',
    'uses' => 'Auth\LoginController@logout',
    'middleware' => 'auth',
]);
