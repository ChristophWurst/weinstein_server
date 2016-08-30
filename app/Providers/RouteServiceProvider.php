<?php

namespace App\Providers;

use App\MasterData\Applicant;
use App\MasterData\Association;
use App\MasterData\Competition;
use App\MasterData\User;
use App\MasterData\WineSort;
use App\Tasting\Commission;
use App\TastingNumber;
use App\TastingSession;
use App\Wine;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;
use function app_path;

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
	 * @param  Router  $router
	 * @return void
	 */
	public function boot(Router $router) {
		//

		parent::boot($router);

		$router->model('applicant', Applicant::class);
		$router->model('competition', Competition::class);
		$router->model('commission', Commission::class);
		$router->model('association', Association::class);
		$router->model('competition', Competition::class);
		$router->model('tastingnumber', TastingNumber::class);
		$router->model('tastingsession', TastingSession::class);
		$router->model('user', User::class);
		$router->model('wine', Wine::class);
		$router->model('winesort', WineSort::class);
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
		$router->group([
			'namespace' => $this->namespace, 'middleware' => 'web',
			], function($router) {
			require app_path('Http/routes.php');
		});
	}

}
