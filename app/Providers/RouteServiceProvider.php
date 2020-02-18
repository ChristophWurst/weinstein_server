<?php

namespace App\Providers;

use App\MasterData\Applicant;
use App\MasterData\Association;
use App\MasterData\Competition;
use App\MasterData\Download;
use App\MasterData\User;
use App\MasterData\WineSort;
use App\Tasting\Commission;
use App\Tasting\Taster;
use App\Tasting\TastingNumber;
use App\Tasting\TastingSession;
use App\Wine;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;
use function app_path;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider {

	/**
	 * This namespace is applied to your controller routes.
	 *
	 * In addition, it is set as the URL generator's root namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'App\Http\Controllers';

	/**
	 * Define your route model bindings, pattern filters, etc.
	 *
	 * @return void
	 */
	public function boot() {
		parent::boot();

		Route::model('applicant', Applicant::class);
		Route::model('competition', Competition::class);
		Route::model('commission', Commission::class);
		Route::model('download', Download::class);
		Route::model('association', Association::class);
		Route::model('competition', Competition::class);
		Route::model('tasters', Taster::class);
		Route::model('tastingnumber', TastingNumber::class);
		Route::model('tastingsession', TastingSession::class);
		Route::model('user', User::class);
		Route::model('wine', Wine::class);
		Route::model('winesort', WineSort::class);
	}

	/**
	 * Define the routes for the application.
	 *
	 * @param  Router  $router
	 * @return void
	 */
	public function map(Router $router) {
		$this->mapWebRoutes($router);

		//
	}

	/**
	 * Define the "web" routes for the application.
	 *
	 * These routes all receive session state, CSRF protection, etc.
	 *
	 * @param  Router  $router
	 * @return void
	 */
	protected function mapWebRoutes(Router $router) {
		Route::group([
			'namespace' => $this->namespace, 'middleware' => 'web',
			], function ($router) {
			require app_path('Http/routes.php');
		});
	}

}
