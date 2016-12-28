<?php

use App\MasterData\CompetitionState;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixCompetitionStateIds extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		DB::unprepared('ALTER TABLE `competition`
						DROP FOREIGN KEY `competition_competition_state_id_foreign`;');
		DB::unprepared('ALTER TABLE `competition`
						ADD CONSTRAINT `competition_competition_state_id_foreign`
						FOREIGN KEY (`competition_state_id`) REFERENCES `competition_state` (`id`) ON UPDATE CASCADE;');
		DB::table('competition_state')
			->where('id', CompetitionState::STATE_CHOOSE)
			->update(['id' => 100]);
		DB::table('competition_state')
			->where('id', CompetitionState::STATE_SOSI)
			->update(['id' => CompetitionState::STATE_SOSI]);
		DB::table('competition_state')
			->where('id', CompetitionState::STATE_CHOOSE)
			->update(['id' => CompetitionState::STATE_CHOOSE]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		DB::unprepared('ALTER TABLE `competition`
						DROP FOREIGN KEY `competition_competition_state_id_foreign`;');
		DB::unprepared('ALTER TABLE `competition`
						ADD CONSTRAINT `competition_competition_state_id_foreign`
						FOREIGN KEY (`competition_state_id`) REFERENCES `competition_state` (`id`) ON UPDATE CASCADE;');
		return;
		DB::table('competition_state')
			->where('id', CompetitionState::STATE_CHOOSE)
			->update(['id' => 100]);
		DB::table('competition_state')
			->where('id', CompetitionState::STATE_SOSI)
			->update(['id' => CompetitionState::STATE_CHOOSE]);
		DB::table('competition_state')
			->where('id', CompetitionState::STATE_CHOOSE)
			->update(['id' => CompetitionState::STATE_SOSI]);
	}

}
