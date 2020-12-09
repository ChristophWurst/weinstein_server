<?php

namespace App\Providers;

use App\MasterData\Competition;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use function env;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot() {
		if (env('APP_ENV', 'production') === 'production') {
			URL::forceScheme("https"); //force HTTPS schema in production
		}

		Paginator::useBootstrap();

		Validator::extend('tastingnumber_nr_unique',
			function($attribute, $value, array $parameters) {
			$competition_id = $parameters[0];

			$competition = Competition::find($competition_id);
			return $competition
					->tastingnumbers()
					->where('tastingstage_id', $competition->getTastingStage()->id)
					->where('tastingnumber.nr', $value)
					->count() == 0;
		});

		Validator::extend('tastingnumber_wine_exists',
			function($attribute, $value, array $parameters) {
			$competition_id = $parameters[0];
			$competition = Competition::find($competition_id);

			return $competition
					->wines()
					->where('nr', '=', $value)
					->count() >= 1;
		});

		Validator::extend('tastingnumber_wine_unique',
			function($attribute, $value, array $parameters) {
			$competition_id = $parameters[0];

			$competition = Competition::find($competition_id);
			$wine = $competition
				->wines()
				->where('nr', $value)
				->first();
			if (!$wine) {
				//return true, even if the rule could not been validated
				//hopefully the wineExists rule is used too, so it works as expected
				return true;
			}
			return $competition
					->tastingnumbers()
					->where('tastingstage_id', $competition->getTastingStage()->id)
					->where('wine_id', $wine->id)
					->count() === 0;
		});
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register() {
		//
	}

}
