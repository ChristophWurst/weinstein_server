<?php

namespace App\Providers;

use App\Auth\Abilities\ActivityLogAbilities;
use App\Auth\Abilities\ApplicantAbilities;
use App\Auth\Abilities\AssociationAbilities;
use App\Auth\Abilities\CatalogueAbilities;
use App\Auth\Abilities\CompetitionAbilities;
use App\Auth\Abilities\TastingAbilities;
use App\Auth\Abilities\TastingNumberAbilities;
use App\Auth\Abilities\TastingSessionAbilities;
use App\Auth\Abilities\UserAbilities;
use App\Auth\Abilities\WineAbilities;
use App\MasterData\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
	 * @return void
	 */
	public function boot() {
		$this->registerPolicies();

		Gate::before(function (User $user) {
			return $user->isAdmin() ? true : null;
		});

		/**
		 * ActivityLog
		 */
		Gate::define('view-activitylog', ActivityLogAbilities::class . '@view');

		/**
		 * Applicant
		 */
		Gate::define('show-applicant', ApplicantAbilities::class . '@show');
		Gate::define('create-applicant', ApplicantAbilities::class . '@create');
		Gate::define('import-applicant', ApplicantAbilities::class . '@import');
		Gate::define('edit-applicant', ApplicantAbilities::class . '@edit');

		/**
		 * Association
		 */
		Gate::define('show-association', AssociationAbilities::class . '@show');
		Gate::define('create-association', AssociationAbilities::class . '@create');
		Gate::define('edit-association', AssociationAbilities::class . '@edit');

		/**
		 * Catalogue
		 */
		Gate::define('create-catalogue', CatalogueAbilities::class . '@create');

		/**
		 * Competition
		 */
		Gate::define('show-competition', CompetitionAbilities::class . '@show');
		Gate::define('reset-competition', CompetitionAbilities::class . '@reset');
		Gate::define('complete-competition-tasting-numbers', CompetitionAbilities::class . '@completeTastingNumbers');
		Gate::define('complete-competition-tasting', CompetitionAbilities::class . '@completeTasting');
		Gate::define('complete-competition-tasting-kdb', CompetitionAbilities::class . '@completeTastingKdb');
		Gate::define('complete-competition-tasting-excluded', CompetitionAbilities::class . '@completeTastingExcluded');
		Gate::define('complete-competition-tasting-sosi', CompetitionAbilities::class . '@completeTastingSosi');
		Gate::define('complete-competition-tasting-choosing', CompetitionAbilities::class . '@completeTastingChoosing');
		Gate::define('complete-competition-catalogue-numbers', CompetitionAbilities::class . '@completeCatalogueNumbers');

		/**
		 * Catalogue Numbers
		 */
		Gate::define('import-catalogue-numbers', CatalogueNumberAbilities::class . '@import');
		/**
		 * Evaluation
		 */
		Gate::define('show-evaluations', CatalogueAbilities::class . '@importNumbers');

		/**
		 * Tasting
		 */
		Gate::define('create-tasting', TastingAbilities::class . '@create');
		Gate::define('edit-tasting', TastingAbilities::class . '@edit');

		/**
		 * TastingNumber
		 */
		Gate::define('show-tastingnumbers', TastingNumberAbilities::class . '@show');
		Gate::define('assign-tastingnumber', TastingNumberAbilities::class . '@assign');
		Gate::define('unassign-tastingnumber', TastingNumberAbilities::class . '@unassign');
		Gate::define('import-tastingnumbers', TastingNumberAbilities::class . '@unsign');
		Gate::define('translate-tastingnumber', TastingNumberAbilities::class . '@assign');

		/**
		 * TastingSession
		 */
		Gate::define('show-tastingsessions', TastingSessionAbilities::class . '@showAll');
		Gate::define('create-tastingsession', TastingSessionAbilities::class . '@create');
		Gate::define('show-tastingsession', TastingSessionAbilities::class . '@show');
		Gate::define('export-tastingsession-result', TastingSessionAbilities::class . '@exportResult');
		Gate::define('edit-tastingsession', TastingSessionAbilities::class . '@edit');
		Gate::define('list-tastingsession-tasters', TastingSessionAbilities::class . '@tasters');
		Gate::define('add-tastingsession-taster', TastingSessionAbilities::class . '@addTaster');
		Gate::define('edit-tastingsession-taster', TastingSessionAbilities::class . '@editTaster');
		Gate::define('show-tastingsession-statistics', TastingSessionAbilities::class . '@showStatistics');
		Gate::define('lock-tastingsession', TastingSessionAbilities::class . '@lock');
		Gate::define('delete-tastingsession', TastingSessionAbilities::class . '@delete');

		/**
		 * User
		 */
		Gate::define('create-user', UserAbilities::class . '@create');
		Gate::define('show-user', UserAbilities::class . '@show');
		Gate::define('edit-user', UserAbilities::class . '@edit');
		Gate::define('delete-user', UserAbilities::class . '@delete');

		/**
		 * Wine
		 */
		Gate::define('show-wine', WineAbilities::class . '@show');
		Gate::define('create-wine', WineAbilities::class . '@create');
		Gate::define('update-wine', WineAbilities::class . '@update');
		Gate::define('delete-wine', WineAbilities::class . '@delete');
		Gate::define('redirect-wine', WineAbilities::class . '@redirect');
		Gate::define('export-all-wines', WineAbilities::class . '@exportAll');
		Gate::define('export-wines-flaws', WineAbilities::class . '@exportFlaws');
		Gate::define('import-kdb-wines', WineAbilities::class . '@importKdb');
		Gate::define('import-sosi-wines', WineAbilities::class . '@importSosi');
		Gate::define('import-excluded-wines', WineAbilities::class . '@importExcluded');
		Gate::define('print-wine-enrollment-pdf', WineAbilities::class . '@enrollmentPdf');
	}

}
