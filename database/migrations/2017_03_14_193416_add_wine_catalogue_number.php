<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWineCatalogueNumber extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('wine', function (Blueprint $table) {
			$table->unsignedInteger('catalogue_number')->nullable()->default(null);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('wine', function (Blueprint $table) {
			$table->dropColumn('catalogue_number');
		});
	}

}
