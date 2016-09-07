<?php

namespace App\Providers;

use App\Auth\Abilities\ActivityLogAbilities;
use App\Auth\Abilities\ApplicantAbilities;
use App\Auth\Abilities\CatalogueAbilities;
use App\Auth\Abilities\CompetitionAbilities;
use App\Auth\Abilities\EvaluationAbilities;
use App\Auth\Abilities\TastingAbilities;
use App\Auth\Abilities\TastingNumberAbilities;
use App\Auth\Abilities\TastingSessionAbilities;
use App\Auth\Abilities\UserAbilities;
use App\Auth\Abilities\WineAbilities;
use App\MasterData\User;
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

		$gate->before(function (User $user) {
			return $user->isAdmin() ? true : null;
		});

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

		/**
		 * Competition
		 */
		$gate->define('show-competition', CompetitionAbilities::class . '@show');
		$gate->define('reset-competition', CompetitionAbilities::class . '@reset');
		$gate->define('complete-competition-tasting-numbers', CompetitionAbilities::class . '@completeTastingNumbers');
		$gate->define('complete-competition-tasting', CompetitionAbilities::class . '@completeTasting');
		$gate->define('complete-competition-tasting-kdb', CompetitionAbilities::class . '@completeTastingKdb');
		$gate->define('complete-competition-tasting-excluded', CompetitionAbilities::class . '@completeTastingExcluded');
		$gate->define('complete-competition-tasting-sosi', CompetitionAbilities::class . '@completeTastingSosi');
		$gate->define('complete-competition-tasting-choosing', CompetitionAbilities::class . '@completeTastingChoosing');

		/**
		 * Evaluation
		 */
		$gate->define('show-evaluations', EvaluationAbilities::class . '@show');

		/**
		 * Tasting
		 */
		$gate->define('create-tasting', TastingAbilities::class . '@create');
		$gate->define('edit-tasting', TastingAbilities::class . '@edit');

		/**
		 * TastingNumber
		 */
		$gate->define('show-tastingnumbers', TastingNumberAbilities::class . '@show');
		$gate->define('assign-tastingnumber', TastingNumberAbilities::class . '@assign');
		$gate->define('unassign-tastingnumber', TastingNumberAbilities::class . '@unassign');
		$gate->define('import-tastingnumbers', TastingNumberAbilities::class . '@unsign');
		$gate->define('translate-tastingnumber', TastingNumberAbilities::class . '@assign');

		/**
		 * TastingSession
		 */
		$gate->define('show-tastingsessions', TastingSessionAbilities::class . '@showAll');
		$gate->define('create-tastingsession', TastingSessionAbilities::class . '@create');
		$gate->define('show-tastingsession', TastingSessionAbilities::class . '@show');
		$gate->define('export-tastingsession-result', TastingSessionAbilities::class . '@exportResult');
		$gate->define('export-tastingsession-result', TastingSessionAbilities::class . '@exportProtocol');
		$gate->define('edit-tastingsession', TastingSessionAbilities::class . '@edit');
		$gate->define('list-tastingsession-tasters', TastingSessionAbilities::class . '@tasters');
		$gate->define('add-tastingsession-taster', TastingSessionAbilities::class . '@addTaster');
		$gate->define('show-tastingsession-statistics', TastingSessionAbilities::class . '@showStatistics');
		$gate->define('lock-tastingsession', TastingSessionAbilities::class . '@lock');
		$gate->define('delete-tastingsession', TastingSessionAbilities::class . '@delete');

		/**
		 * User
		 */
		$gate->define('create-user', UserAbilities::class . '@create');
		$gate->define('show-user', UserAbilities::class . '@show');
		$gate->define('edit-user', UserAbilities::class . '@edit');
		$gate->define('delete-user', UserAbilities::class . '@delete');

		/**
		 * Wine
		 */
	}

}
