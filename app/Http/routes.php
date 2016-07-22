<?php

/*
  |--------------------------------------------------------------------------
  | Model Binding
  |--------------------------------------------------------------------------
 */

Route::model('applicant', 'App\Applicant');
Route::model('competition', 'App\Competiton\Competition');
Route::model('commission', 'Commission');
Route::model('association', 'Association');
Route::model('competition', 'Competition');
Route::model('tastingnumber', 'TastingNumber');
Route::model('tastingsession', 'TastingSession');
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
	    'uses' => 'Competition\CompetitionController@show',
	));
	Route::get('complete-tasting/{tasting}', array(
	    'as' => 'competition/complete-tasting',
	    'uses' => 'Competition\CompetitionController@completeTasting',
	));
	Route::post('complete-tasting/{tasting}', array(
	    'uses' => 'Competition\CompetitionController@lockTasting'
	));
	Route::get('complete-tastingnumbers/{tasting}', array(
	    'as' => 'competition/complete-tastingnumbers',
	    'uses' => 'Competition\CompetitionController@completeTastingNumbers',
	));
	Route::post('complete-tastingnumbers/{tasting}', array(
	    'uses' => 'Competition\CompetitionController@lockTastingnumbers'
	));
	Route::get('complete-kdb', array(
	    'as' => 'competition/complete-kdb',
	    'uses' => 'Competition\CompetitionController@completeKdb',
	));
	Route::post('complete-kdb', array(
	    'uses' => 'Competition\CompetitionController@lockKdb'
	));
	Route::get('complete-excluded', array(
	    'as' => 'competition/complete-excluded',
	    'uses' => 'Competition\CompetitionController@completeExcluded',
	));
	Route::post('complete-excluded', array(
	    'uses' => 'Competition\CompetitionController@lockExcluded'
	));
	Route::get('complete-sosi', array(
	    'as' => 'competition/complete-sosi',
	    'uses' => 'Competition\CompetitionController@completeSosi',
	));
	Route::post('complete-sosi', array(
	    'uses' => 'Competition\CompetitionController@lockSosi'
	));
	Route::get('complete-choosing', array(
	    'as' => 'competition/complete-choosing',
	    'uses' => 'Competition\CompetitionController@completeChoosing',
	));
	Route::post('complete-choosing', array(
	    'uses' => 'Competition\CompetitionController@lockChoosing'
	));
	Route::get('reset', array(
	    'as' => 'competition/reset',
	    'uses' => 'Competition\CompetitionController@getReset',
	));
	Route::post('reset', array(
	    'uses' => 'Competition\CompetitionController@postReset'
	));

	/*
	  |--------------------------------------------------------------------------
	  | Enrollment
	  |--------------------------------------------------------------------------
	 */
	Route::group(array('prefix' => 'enrollment'), function() {
		/*
		 * Wines
		 */
		Route::group(array('prefix' => 'wines'), function() {
			Route::get('', array(
			    'as' => 'enrollment.wines',
			    'uses' => 'Competition\Wine\WineController@index'
			));
			Route::get('redirect/{nr}', array(
			    'as' => 'enrollment.wines/redirect',
			    'uses' => 'Competition\Wine\WineController@redirect'
			));
			Route::get('kdb', array(
			    'as' => 'enrollment.wines/kdb',
			    'uses' => 'Competition\Wine\WineController@kdb'
			));
			Route::get('import-kdb', array(
			    'as' => 'enrollment.wines/import-kdb',
			    'uses' => 'Competition\Wine\WineController@importKdb'
			));
			Route::post('import-kdb', array(
			    'uses' => 'Competition\Wine\WineController@importKdbStore'
			));
			Route::get('excluded', array(
			    'as' => 'enrollment.wines/excluded',
			    'uses' => 'Competition\Wine\WineController@excluded'
			));
			Route::get('import-excluded', array(
			    'as' => 'enrollment.wines/import-excluded',
			    'uses' => 'Competition\Wine\WineController@importExcluded'
			));
			Route::post('import-excluded', array(
			    'uses' => 'Competition\Wine\WineController@importExcludedStore'
			));
			Route::get('sosi', array(
			    'as' => 'enrollment.wines/sosi',
			    'uses' => 'Competition\Wine\WineController@sosi'
			));
			Route::get('import-sosi', array(
			    'as' => 'enrollment.wines/import-sosi',
			    'uses' => 'Competition\Wine\WineController@importSosi'
			));
			Route::post('import-sosi', array(
			    'uses' => 'Competition\Wine\WineController@importSosiStore'
			));
			Route::get('chosen', array(
			    'as' => 'enrollment.wines/chosen',
			    'uses' => 'Competition\Wine\WineController@chosen'
			));
			Route::get('import-chosen', array(
			    'as' => 'enrollment.wines/import-chosen',
			    'uses' => 'Competition\Wine\WineController@importChosen'
			));
			Route::post('import-chosen', array(
			    'uses' => 'Competition\Wine\WineController@importChosenStore'
			));

			Route::get('create', array(
			    'as' => 'enrollment.wines/create',
			    'uses' => 'Competition\Wine\WineController@create'
			));
			Route::post('create', array(
			    'uses' => 'Competition\Wine\WineController@store'
			));
			Route::get('export', array(
			    'as' => 'enrollment.wines/export',
			    'uses' => 'Competition\Wine\WineController@exportAll'
			));
			Route::get('export-kdb', array(
			    'as' => 'enrollment.wines/export-kdb',
			    'uses' => 'Competition\Wine\WineController@exportKdb'
			));
			Route::get('export-sosi', array(
			    'as' => 'enrollment.wines/export-sosi',
			    'uses' => 'Competition\Wine\WineController@exportSosi'
			));
			Route::get('export-chosen', array(
			    'as' => 'enrollment.wines/export-chosen',
			    'uses' => 'Competition\Wine\WineController@exportChosen'
			));
			Route::get('export-flaws', array(
			    'as' => 'enrollment.wines/export-flaws',
			    'uses' => 'Competition\Wine\WineController@exportFlaws'
			));
		});
	});

	/*
	  |--------------------------------------------------------------------------
	  | Tasting
	  |--------------------------------------------------------------------------
	 */

	Route::group(array('prefix' => 'tasting'), function() {
		/*
		 * Tasting Numbers
		 */
		Route::group(array('prefix' => 'numbers'), function() {
			Route::get('', array(
			    'as' => 'tasting.numbers',
			    'uses' => 'Competition\TastingNumber\TastingNumberController@index'
			));
			Route::get('assign', array(
			    'as' => 'tasting.numbers/assign',
			    'uses' => 'Competition\TastingNumber\TastingNumberController@assign'
			));
			Route::post('assign', array(
			    'uses' => 'Competition\TastingNumber\TastingNumberController@store'
			));
			Route::get('import', array(
			    'as' => 'tasting.numbers/import',
			    'uses' => 'Competition\TastingNumber\TastingNumberController@import'
			));
			Route::post('import', array(
			    'uses' => 'Competition\TastingNumber\TastingNumberController@importStore'
			));
			Route::get('translate/{id}', array(
			    'as' => 'tasting.numbers/translate',
			    'uses' => 'Competition\TastingNumber\TastingNumberController@translate'
			));
		});
		/*
		 * Tasting sessions
		 */
		Route::group(array('prefix' => 'sessions'), function() {
			Route::get('', array(
			    'as' => 'tasting.sessions',
			    'uses' => 'Competition\TastingSession\TastingSessionController@index'
			));
			Route::get('add', array(
			    'as' => 'tasting.sessions/add',
			    'uses' => 'Competition\TastingSession\TastingSessionController@add'
			));
			Route::post('add', array(
			    'uses' => 'Competition\TastingSession\TastingSessionController@store'
			));
		});
	});

	/*
	  |--------------------------------------------------------------------------
	  | Evaluation
	  |--------------------------------------------------------------------------
	 */

	Route::group(array('prefix' => 'evaluation'), function() {
		Route::get('', array(
		    'as' => 'evaluation',
		    'uses' => 'Competition\Evaluation\EvaluationController@index'
		));

		/*
		 * Tasting protocols
		 */
		Route::group(array('prefix' => 'protocols'), function() {
			Route::get('', array(
			    'as' => 'evaluation.protocols',
			    'uses' => 'Competition\Evaluation\EvaluationController@protocols'
			));
		});
		/*
		 * Catalogues
		 */
		Route::group(array('prefix' => 'catalogues'), function() {
			Route::get('tasting', array(
			    'as' => 'evaluation.catalogues/tasting',
			    'uses' => 'Competition\Evaluation\Catalogue\CatalogueController@tastingCatalogue',
			));
			Route::get('web', array(
			    'as' => 'evaluation.catalogues/web',
			    'uses' => 'Competition\Evaluation\Catalogue\CatalogueController@webCatalogue',
			));
			Route::get('address', array(
			    'as' => 'evaluation.catalogues/address',
			    'uses' => 'Competition\Evaluation\Catalogue\CatalogueController@addressCatalogue',
			));
		});
	});
});

Route::group(array('prefix' => 'competition', 'before' => 'auth'), function() {

	/*
	  |--------------------------------------------------------------------------
	  | Enrollment
	  |--------------------------------------------------------------------------
	 */
	Route::group(array('prefix' => 'enrollment'), function() {
		/*
		 * Wines
		 */
		Route::group(array('prefix' => 'wines/{wine}'), function() {
			Route::get('', array(
			    'as' => 'enrollment.wines/show',
			    'uses' => 'Competition\Wine\WineController@show'
			));
			Route::get('enrollment-pdf', array(
			    'as' => 'enrollment.wines/enrollment-pdf',
			    'uses' => 'Competition\Wine\WineController@enrollmentPdf'
			));
			Route::get('edit', array(
			    'as' => 'enrollment.wines/edit',
			    'uses' => 'Competition\Wine\WineController@edit'
			));
			Route::post('edit', array(
			    'uses' => 'Competition\Wine\WineController@update'
			));
			Route::get('delete', array(
			    'as' => 'enrollment.wines/delete',
			    'uses' => 'Competition\Wine\WineController@delete'
			));
			Route::post('delete', array(
			    'uses' => 'Competition\Wine\WineController@destroy'
			));
			Route::post('update-kdb', array(
			    'as' => 'enrollment.wines/update-kdb',
			    'uses' => 'Competition\Wine\WineController@updateKdb',
			));
			Route::post('update-excluded', array(
			    'as' => 'enrollment.wines/update-excluded',
			    'uses' => 'Competition\Wine\WineController@updateExcluded',
			));
			Route::post('update-sosi', array(
			    'as' => 'enrollment.wines/update-sosi',
			    'uses' => 'Competition\Wine\WineController@updateSosi',
			));
			Route::post('update-chosen', array(
			    'as' => 'enrollment.wines/update-chosen',
			    'uses' => 'Competition\Wine\WineController@updateChosen',
			));
		});
	});

	/*
	  |--------------------------------------------------------------------------
	  | Tasting
	  |--------------------------------------------------------------------------
	 */
	Route::group(array('prefix' => 'tasting'), function() {
		/*
		 * Tasting Numbers
		 */
		Route::group(array('prefix' => 'number/{tastingnumber}'), function() {
			Route::get('deallocate', array(
			    'as' => 'tasting.numbers/deallocate',
			    'uses' => 'Competition\TastingNumber\TastingNumberController@deallocate'
			));
			Route::post('deallocate', array(
			    'uses' => 'Competition\TastingNumber\TastingNumberController@delete'
			));
		});
		/*
		 * Tasting sessions
		 */
		Route::group(array('prefix' => 'session/{tastingsession}'), function() {
			Route::get('', array(
			    'as' => 'tasting.session/show',
			    'uses' => 'Competition\TastingSession\TastingSessionController@show'
			));
			Route::get('edit', array(
			    'as' => 'tasting.sessions/edit',
			    'uses' => 'Competition\TastingSession\TastingSessionController@edit'
			));
			Route::post('edit', array(
			    'uses' => 'Competition\TastingSession\TastingSessionController@update'
			));
			Route::get('commission/{commission}/tasters', array(
			    'uses' => 'Competition\TastingSession\TastingSessionController@tasters',
			    'before' => 'ajax',
			    'as' => 'tasting.session/tasters',
			));
			Route::post('addtaster', array(
			    'uses' => 'Competition\TastingSession\TastingSessionController@addTaster',
			    'before' => 'ajax',
			    'as' => 'tasting.session/addtaster',
			));
			Route::get('complete', array(
			    'as' => 'tasting.sessions/complete',
			    'uses' => 'Competition\TastingSession\TastingSessionController@complete'
			));
			Route::post('complete', array(
			    'uses' => 'Competition\TastingSession\TastingSessionController@lock'
			));
			Route::get('delete', array(
			    'as' => 'tasting.sessions/delete',
			    'uses' => 'Competition\TastingSession\TastingSessionController@delete'
			));
			Route::post('delete', array(
			    'uses' => 'Competition\TastingSession\TastingSessionController@destroy'
			));
			Route::get('export-result/{commission}', array(
			    'as' => 'tasting.sessions/export-result',
			    'uses' => 'Competition\TastingSession\TastingSessionController@exportResult'
			));
			Route::get('export-protocol', array(
			    'as' => 'tasting.sessions/protocol',
			    'uses' => 'Competition\TastingSession\TastingSessionController@exportProtocol'
			));
			Route::get('statistics', array(
			    'as' => 'tasting.sessions/statistics',
			    'uses' => 'Competition\TastingSession\TastingSessionController@statistics',
			));
			Route::group(array('prefix' => 'taste'), function() {
				Route::get('', array(
				    'as' => 'tasting.session/taste',
				    'uses' => 'Competition\TastingSession\Tasting\TastingController@add'
				));
				Route::post('', array(
				    'uses' => 'Competition\TastingSession\Tasting\TastingController@store'
				));
			});
			Route::group(array('prefix' => 'retaste/{tastingnumber}/commission/{commission}'), function() {
				Route::get('', array(
				    'as' => 'tasting.session/retaste',
				    'uses' => 'Competition\TastingSession\Tasting\TastingController@edit'
				));
				Route::post('', array(
				    'uses' => 'Competition\TastingSession\Tasting\TastingController@update'
				));
			});
		});
	});
});

/*
  |--------------------------------------------------------------------------
  | Settings
  |--------------------------------------------------------------------------
 */

Route::group(array('prefix' => 'settings', 'before' => 'auth'), function() {
	Route::get('', array(
	    'as' => 'settings',
	    'uses' => 'SettingsController@index')
	);

	/*
	 * Activity log
	 */
	Route::get('activitylog', array(
	    'as' => 'settings.activitylog',
	    'uses' => 'ActivityLog\ActivityLogController@index',
	));

	/*
	 * Applicant
	 */
	Route::group(array('prefix' => 'applicants'), function() {
		Route::get('', array(
		    'as' => 'settings.applicants',
		    'uses' => 'Applicant\ApplicantController@index'
		));
		Route::get('import', array(
		    'as' => 'settings.applicants/import',
		    'uses' => 'Applicant\ApplicantController@getImport',
		));
		Route::post('import', array(
		    'uses' => 'Applicant\ApplicantController@postImport'
		));
		Route::get('create', array(
		    'as' => 'settings.applicants/create',
		    'uses' => 'Applicant\ApplicantController@create',
		));
		Route::post('create', array(
		    'uses' => 'Applicant\ApplicantController@store',
		));
	});

	Route::group(array('prefix' => 'applicant/{applicant}'), function() {
		Route::get('', array(
		    'as' => 'settings.applicant/show',
		    'uses' => 'Applicant\ApplicantController@show'
		));
		Route::get('edit', array(
		    'as' => 'settings.applicants/edit',
		    'uses' => 'Applicant\ApplicantController@edit',
		));
		Route::post('edit', array(
		    'uses' => 'Applicant\ApplicantController@update',
		));
	});

	/*
	 * Associations
	 */
	Route::group(array('prefix' => 'associations'), function() {
		Route::get('', array(
		    'as' => 'settings.associations',
		    'uses' => 'Association\AssociationController@index',
		));
		Route::get('create', array(
		    'as' => 'settings.associations/create',
		    'uses' => 'Association\AssociationController@create'
		));
		Route::post('create', array(
		    'uses' => 'Association\AssociationController@store'
		));
	});
	Route::group(array('prefix' => 'association/{association}'), function() {
		Route::get('', array(
		    'as' => 'settings.association/show',
		    'uses' => 'Association\AssociationController@show'
		));
		Route::get('edit', array(
		    'as' => 'settings.associations/edit',
		    'uses' => 'Association\AssociationController@edit'
		));
		Route::post('edit', array(
		    'uses' => 'Association\AssociationController@update'
		));
	});

	/*
	 * Competition
	 */
	Route::group(array('prefix' => 'competitions'), function() {
		Route::get('', array(
		    'as' => 'settings.competitions',
		    'uses' => 'Competition\CompetitionController@index'
		));
	});

	/*
	 * Users
	 */
	Route::group(array('prefix' => 'users'), function() {
		Route::get('', array(
		    'as' => 'settings.users',
		    'uses' => 'User\UserController@index'
		));
		Route::get('create', array(
		    'as' => 'settings.users/create',
		    'uses' => 'User\UserController@create'
		));
		Route::post('create', array(
		    'uses' => 'User\UserController@store'
		));
	});
	Route::group(array('prefix' => 'user/{user}'), function() {
		Route::get('', array(
		    'as' => 'settings.user/show',
		    'uses' => 'User\UserController@show'
		));
		Route::get('edit', array(
		    'as' => 'settings.users/edit',
		    'uses' => 'User\UserController@edit'
		));
		Route::post('edit', array(
		    'uses' => 'User\UserController@update'
		));
		Route::get('delete', array(
		    'as' => 'settings.users/delete',
		    'uses' => 'User\UserController@delete'
		));
		Route::post('delete', array(
		    'uses' => 'User\UserController@destroy'
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
