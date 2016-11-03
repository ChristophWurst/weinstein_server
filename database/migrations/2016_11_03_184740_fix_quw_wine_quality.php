<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixQuwWineQuality extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		DB::table('winequality')
			->where('id', 2)
			->update(['abbr' => 'QUW']);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		DB::table('winequality')
			->where('id', 2)
			->update(['abbr' => 'QW']);
	}

}
