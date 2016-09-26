<?php

use Illuminate\Database\Migrations\Migration;

class RenameTastedwineView extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::rename('TastedWine', 'tasted_wine');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::rename('tasted_wine', 'TastedWine');
	}

}
