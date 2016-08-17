<?php

namespace App\Providers;

use App\Auth\Abilities\ActivityLogAbilities;
use App\Auth\Abilities\ApplicantAbilities;
use App\Auth\Abilities\CatalogueAbilities;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider {

	/**
	 * The policy mappings for the application.
	 *
	 * @var array
	 */
	protected $policies = [];

	/**
	 * Register any application authentication / authorization services.
	 *
	 * @param  Gate  $gate
	 * @return void
	 */
	public function boot(Gate $gate) {
		$this->registerPolicies($gate);

		/**
		 * ActivityLog
		 */
		$gate->define('view-activitylog', ActivityLogAbilities::class . '@view');

		/**
		 * Applicant
		 */
		$gate->define('show-applicant', ApplicantAbilities::class . '@show');
		$gate->define('create-applicant', ApplicantAbilities::class . '@create');
		$gate->define('import-applicant', ApplicantAbilities::class . '@import');
		$gate->define('edit-applicant', ApplicantAbilities::class . '@edit');

		/**
		 * Association
		 */
		$gate->define('show-association', ApplicantAbilities::class . '@show');
		$gate->define('create-association', ApplicantAbilities::class . '@create');
		$gate->define('edit-association', ApplicantAbilities::class . '@edit');

		/**
		 * Catalogue
		 */
		$gate->define('create-catalogue', CatalogueAbilities::class . '@create');
	}

}
