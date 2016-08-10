<?php

/*
  |--------------------------------------------------------------------------
  | Model Binding
  |--------------------------------------------------------------------------
 */

Route::model('applicant', 'App\Applicant');
Route::model('competition', 'App\Competition');
Route::model('commission', 'App\Commission');
Route::model('association', 'App\Association');
Route::model('competition', 'App\Competition');
Route::model('tastingnumber', 'App\TastingNumber');
Route::model('tastingsession', 'App\TastingSession');
Route::model('user', 'User');
Route::model('wine', 'Wine');
Route::model('winesort', 'WineSort');

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
 */

Route::get('', array(
    'as' => 'start',
    'uses' => 'StartController@index'
));

Route::group(array('prefix' => 'competition/{competition}', 'before' => 'auth'), function() {
	Route::get('', array(
	    'as' => 'competition/show',
	    'uses' => 'CompetitionController@show',
	));
	Route::get('complete-tasting/{tasting}', array(
	    'as' => 'competition/complete-tasting',
	    'uses' => 'CompetitionController@completeTasting',
	));
	Route::post('complete-tasting/{tasting}', array(
	    'uses' => 'CompetitionController@lockTasting'
	));
	Route::get('complete-tastingnumbers/{tasting}', array(
	    'as' => 'competition/complete-tastingnumbers',
	    'uses' => 'CompetitionController@completeTastingNumbers',
	));
	Route::post('complete-tastingnumbers/{tasting}', array(
	    'uses' => 'CompetitionController@lockTastingnumbers'
	));
	Route::get('complete-kdb', array(
	    'as' => 'competition/complete-kdb',
	    'uses' => 'CompetitionController@completeKdb',
	));
	Route::post('complete-kdb', array(
	    'uses' => 'CompetitionController@lockKdb'
	));
	Route::get('complete-excluded', array(
	    'as' => 'competition/complete-excluded',
	    'uses' => 'CompetitionController@completeExcluded',
	));
	Route::post('complete-excluded', array(
	    'uses' => 'CompetitionController@lockExcluded'
	));
	Route::get('complete-sosi', array(
	    'as' => 'competition/complete-sosi',
	    'uses' => 'CompetitionController@completeSosi',
	));
	Route::post('complete-sosi', array(
	    'uses' => 'CompetitionController@lockSosi'
	));
	Route::get('complete-choosing', array(
	    'as' => 'competition/complete-choosing',
	    'uses' => 'CompetitionController@completeChoosing',
	));
	Route::post('complete-choosing', array(
	    'uses' => 'CompetitionController@lockChoosing'
	));
	Route::get('reset', array(
	    'as' => 'competition/reset',
	    'uses' => 'CompetitionController@getReset',
	));
	Route::post('reset', array(
	    'uses' => 'CompetitionController@postReset'
	));

	/*
	 * Wines
	 */
	Route::group(array('prefix' => 'wines'), function() {
		Route::get('', array(
		    'as' => 'enrollment.wines',
		    'uses' => 'WineController@index'
		));
		Route::get('redirect/{nr}', array(
		    'as' => 'enrollment.wines/redirect',
		    'uses' => 'WineController@redirect'
		));
		Route::get('kdb', array(
		    'as' => 'enrollment.wines/kdb',
		    'uses' => 'WineController@kdb'
		));
		Route::get('import-kdb', array(
		    'as' => 'enrollment.wines/import-kdb',
		    'uses' => 'WineController@importKdb'
		));
		Route::post('import-kdb', array(
		    'uses' => 'WineController@importKdbStore'
		));
		Route::get('excluded', array(
		    'as' => 'enrollment.wines/excluded',
		    'uses' => 'WineController@excluded'
		));
		Route::get('import-excluded', array(
		    'as' => 'enrollment.wines/import-excluded',
		    'uses' => 'WineController@importExcluded'
		));
		Route::post('import-excluded', array(
		    'uses' => 'WineController@importExcludedStore'
		));
		Route::get('sosi', array(
		    'as' => 'enrollment.wines/sosi',
		    'uses' => 'WineController@sosi'
		));
		Route::get('import-sosi', array(
		    'as' => 'enrollment.wines/import-sosi',
		    'uses' => 'WineController@importSosi'
		));
		Route::post('import-sosi', array(
		    'uses' => 'WineController@importSosiStore'
		));
		Route::get('chosen', array(
		    'as' => 'enrollment.wines/chosen',
		    'uses' => 'WineController@chosen'
		));
		Route::get('import-chosen', array(
		    'as' => 'enrollment.wines/import-chosen',
		    'uses' => 'WineController@importChosen'
		));
		Route::post('import-chosen', array(
		    'uses' => 'WineController@importChosenStore'
		));

		Route::get('create', array(
		    'as' => 'enrollment.wines/create',
		    'uses' => 'WineController@create'
		));
		Route::post('create', array(
		    'uses' => 'WineController@store'
		));
		Route::get('export', array(
		    'as' => 'enrollment.wines/export',
		    'uses' => 'WineController@exportAll'
		));
		Route::get('export-kdb', array(
		    'as' => 'enrollment.wines/export-kdb',
		    'uses' => 'WineController@exportKdb'
		));
		Route::get('export-sosi', array(
		    'as' => 'enrollment.wines/export-sosi',
		    'uses' => 'WineController@exportSosi'
		));
		Route::get('export-chosen', array(
		    'as' => 'enrollment.wines/export-chosen',
		    'uses' => 'WineController@exportChosen'
		));
		Route::get('export-flaws', array(
		    'as' => 'enrollment.wines/export-flaws',
		    'uses' => 'WineController@exportFlaws'
		));
	});

	/*
	 * Tasting Numbers
	 */
	Route::group(array('prefix' => 'numbers'), function() {
		Route::get('', array(
		    'as' => 'tasting.numbers',
		    'uses' => 'TastingNumberController@index'
		));
		Route::get('assign', array(
		    'as' => 'tasting.numbers/assign',
		    'uses' => 'TastingNumberController@assign'
		));
		Route::post('assign', array(
		    'uses' => 'TastingNumberController@store'
		));
		Route::get('import', array(
		    'as' => 'tasting.numbers/import',
		    'uses' => 'TastingNumberController@import'
		));
		Route::post('import', array(
		    'uses' => 'TastingNumberController@importStore'
		));
		Route::get('translate/{id}', array(
		    'as' => 'tasting.numbers/translate',
		    'uses' => 'TastingNumberController@translate'
		));
	});
	/*
	 * Tasting sessions
	 */
	Route::group(array('prefix' => 'sessions'), function() {
		Route::get('', array(
		    'as' => 'tasting.sessions',
		    'uses' => 'TastingSessionController@index'
		));
		Route::get('add', array(
		    'as' => 'tasting.sessions/add',
		    'uses' => 'TastingSessionController@add'
		));
		Route::post('add', array(
		    'uses' => 'TastingSessionController@store'
		));
	});

	Route::get('', array(
	    'as' => 'evaluation',
	    'uses' => 'EvaluationController@index'
	));

	/*
	 * Tasting protocols
	 */
	Route::group(array('prefix' => 'protocols'), function() {
		Route::get('', array(
		    'as' => 'evaluation.protocols',
		    'uses' => 'EvaluationController@protocols'
		));
	});
	/*
	 * Catalogues
	 */
	Route::group(array('prefix' => 'catalogues'), function() {
		Route::get('tasting', array(
		    'as' => 'evaluation.catalogues/tasting',
		    'uses' => 'CatalogueController@tastingCatalogue',
		));
		Route::get('web', array(
		    'as' => 'evaluation.catalogues/web',
		    'uses' => 'CatalogueController@webCatalogue',
		));
		Route::get('address', array(
		    'as' => 'evaluation.catalogues/address',
		    'uses' => 'CatalogueController@addressCatalogue',
		));
	});
});

Route::group(array('before' => 'auth'), function() {

	/*
	 * Wines
	 */
	Route::group(array('prefix' => 'wines/{wine}'), function() {
		Route::get('', array(
		    'as' => 'enrollment.wines/show',
		    'uses' => 'WineController@show'
		));
		Route::get('enrollment-pdf', array(
		    'as' => 'enrollment.wines/enrollment-pdf',
		    'uses' => 'WineController@enrollmentPdf'
		));
		Route::get('edit', array(
		    'as' => 'enrollment.wines/edit',
		    'uses' => 'WineController@edit'
		));
		Route::post('edit', array(
		    'uses' => 'WineController@update'
		));
		Route::get('delete', array(
		    'as' => 'enrollment.wines/delete',
		    'uses' => 'WineController@delete'
		));
		Route::post('delete', array(
		    'uses' => 'WineController@destroy'
		));
		Route::post('update-kdb', array(
		    'as' => 'enrollment.wines/update-kdb',
		    'uses' => 'WineController@updateKdb',
		));
		Route::post('update-excluded', array(
		    'as' => 'enrollment.wines/update-excluded',
		    'uses' => 'WineController@updateExcluded',
		));
		Route::post('update-sosi', array(
		    'as' => 'enrollment.wines/update-sosi',
		    'uses' => 'WineController@updateSosi',
		));
		Route::post('update-chosen', array(
		    'as' => 'enrollment.wines/update-chosen',
		    'uses' => 'WineController@updateChosen',
		));
	});

	/*
	 * Tasting Numbers
	 */
	Route::group(array('prefix' => 'number/{tastingnumber}'), function() {
		Route::get('deallocate', array(
		    'as' => 'tasting.numbers/deallocate',
		    'uses' => 'TastingNumberController@deallocate'
		));
		Route::post('deallocate', array(
		    'uses' => 'TastingNumberController@delete'
		));
	});
	/*
	 * Tasting sessions
	 */
	Route::group(array('prefix' => 'session/{tastingsession}'), function() {
		Route::get('', array(
		    'as' => 'tasting.session/show',
		    'uses' => 'TastingSessionController@show'
		));
		Route::get('edit', array(
		    'as' => 'tasting.sessions/edit',
		    'uses' => 'TastingSessionController@edit'
		));
		Route::post('edit', array(
		    'uses' => 'TastingSessionController@update'
		));
		Route::get('commission/{commission}/tasters', array(
		    'uses' => 'TastingSessionController@tasters',
		    'before' => 'ajax',
		    'as' => 'tasting.session/tasters',
		));
		Route::post('addtaster', array(
		    'uses' => 'TastingSessionController@addTaster',
		    'before' => 'ajax',
		    'as' => 'tasting.session/addtaster',
		));
		Route::get('complete', array(
		    'as' => 'tasting.sessions/complete',
		    'uses' => 'TastingSessionController@complete'
		));
		Route::post('complete', array(
		    'uses' => 'TastingSessionController@lock'
		));
		Route::get('delete', array(
		    'as' => 'tasting.sessions/delete',
		    'uses' => 'TastingSessionController@delete'
		));
		Route::post('delete', array(
		    'uses' => 'TastingSessionController@destroy'
		));
		Route::get('export-result/{commission}', array(
		    'as' => 'tasting.sessions/export-result',
		    'uses' => 'TastingSessionController@exportResult'
		));
		Route::get('export-protocol', array(
		    'as' => 'tasting.sessions/protocol',
		    'uses' => 'TastingSessionController@exportProtocol'
		));
		Route::get('statistics', array(
		    'as' => 'tasting.sessions/statistics',
		    'uses' => 'TastingSessionController@statistics',
		));
		Route::group(array('prefix' => 'taste'), function() {
			Route::get('', array(
			    'as' => 'tasting.session/taste',
			    'uses' => 'TastingController@add'
			));
			Route::post('', array(
			    'uses' => 'TastingController@store'
			));
		});
		Route::group(array('prefix' => 'retaste/{tastingnumber}/commission/{commission}'), function() {
			Route::get('', array(
			    'as' => 'tasting.session/retaste',
			    'uses' => 'TastingController@edit'
			));
			Route::post('', array(
			    'uses' => 'TastingController@update'
			));
		});
	});
});

/*
  |--------------------------------------------------------------------------
  | Settings
  |--------------------------------------------------------------------------
 */
Route::group(array('before' => 'auth'), function() {
	Route::get('', array(
	    'as' => 'settings',
	    'uses' => 'SettingsController@index')
	);

	/*
	 * Activity log
	 */
	Route::get('activitylog', array(
	    'as' => 'settings.activitylog',
	    'uses' => 'ActivityLogController@index',
	));

	/*
	 * Applicant
	 */
	Route::group(array('prefix' => 'applicants'), function() {
		Route::get('', array(
		    'as' => 'settings.applicants',
		    'uses' => 'ApplicantController@index'
		));
		Route::get('import', array(
		    'as' => 'settings.applicants/import',
		    'uses' => 'ApplicantController@getImport',
		));
		Route::post('import', array(
		    'uses' => 'ApplicantController@postImport'
		));
		Route::get('create', array(
		    'as' => 'settings.applicants/create',
		    'uses' => 'ApplicantController@create',
		));
		Route::post('create', array(
		    'uses' => 'ApplicantController@store',
		));
	});

	Route::group(array('prefix' => 'applicant/{applicant}'), function() {
		Route::get('', array(
		    'as' => 'settings.applicant/show',
		    'uses' => 'ApplicantController@show'
		));
		Route::get('edit', array(
		    'as' => 'settings.applicants/edit',
		    'uses' => 'ApplicantController@edit',
		));
		Route::post('edit', array(
		    'uses' => 'ApplicantController@update',
		));
	});

	/*
	 * Associations
	 */
	Route::group(array('prefix' => 'associations'), function() {
		Route::get('', array(
		    'as' => 'settings.associations',
		    'uses' => 'AssociationController@index',
		));
		Route::get('create', array(
		    'as' => 'settings.associations/create',
		    'uses' => 'AssociationController@create'
		));
		Route::post('create', array(
		    'uses' => 'AssociationController@store'
		));
	});
	Route::group(array('prefix' => 'association/{association}'), function() {
		Route::get('', array(
		    'as' => 'settings.association/show',
		    'uses' => 'AssociationController@show'
		));
		Route::get('edit', array(
		    'as' => 'settings.associations/edit',
		    'uses' => 'AssociationController@edit'
		));
		Route::post('edit', array(
		    'uses' => 'AssociationController@update'
		));
	});

	/*
	 * Competition
	 */
	Route::group(array('prefix' => 'competitions'), function() {
		Route::get('', array(
		    'as' => 'settings.competitions',
		    'uses' => 'CompetitionController@index'
		));
	});

	/*
	 * Users
	 */
	Route::group(array('prefix' => 'users'), function() {
		Route::get('', array(
		    'as' => 'settings.users',
		    'uses' => 'UserController@index'
		));
		Route::get('create', array(
		    'as' => 'settings.users/create',
		    'uses' => 'UserController@create'
		));
		Route::post('create', array(
		    'uses' => 'UserController@store'
		));
	});
	Route::group(array('prefix' => 'user/{user}'), function() {
		Route::get('', array(
		    'as' => 'settings.user/show',
		    'uses' => 'UserController@show'
		));
		Route::get('edit', array(
		    'as' => 'settings.users/edit',
		    'uses' => 'UserController@edit'
		));
		Route::post('edit', array(
		    'uses' => 'UserController@update'
		));
		Route::get('delete', array(
		    'as' => 'settings.users/delete',
		    'uses' => 'UserController@delete'
		));
		Route::post('delete', array(
		    'uses' => 'UserController@destroy'
		));
	});

	/*
	 * Sorts
	 */
	Route::group(array('prefix' => 'winesorts'), function() {
		Route::get('', array(
		    'as' => 'settings.winesorts',
		    'uses' => 'WineSortController@index'
		));
		Route::get('create', array(
		    'as' => 'settings.winesorts/create',
		    'uses' => 'WineSortController@create'
		));
		Route::post('create', array(
		    'uses' => 'WineSortController@store'
		));
	});
	Route::group(array('prefix' => 'winesort/{winesort}'), function() {
		Route::get('edit', array(
		    'as' => 'settings.winesorts/edit',
		    'uses' => 'WineSortController@edit'
		));
		Route::post('edit', array(
		    'uses' => 'WineSortController@update'
		));
	});
});


Route::get('account', array(
    'as' => 'account',
    'uses' => 'AuthenticationController@account'
));
Route::get('login', array(
    'as' => 'login',
    'uses' => 'AuthenticationController@login'
));
Route::post('login', array(
    'as' => 'postLogin',
    'uses' => 'AuthenticationController@auth'
));
Route::get('logout', array(
    'as' => 'logout',
    'uses' => 'AuthenticationController@logout'
));
