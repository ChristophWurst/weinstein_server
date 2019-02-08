<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWineSortQualityAssoc extends Migration
{

	public function up()
	{
		Schema::table('winesort', function (Blueprint $table) {
			$table->json('quality_allowed')->default(null);
		});
	}

	public function down()
	{
		Schema::table('winesort', function (Blueprint $table) {
			$table->dropColumn('quality_allowed');
		});
	}

}
