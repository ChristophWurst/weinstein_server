<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTastingstageTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * -- -----------------------------------------------------
	 * -- Table `weinstein`.`tastingstage`
	 * -- -----------------------------------------------------
	 * DROP TABLE IF EXISTS `weinstein`.`tastingstage` ;
	 *
	 * CREATE TABLE IF NOT EXISTS `weinstein`.`tastingstage` (
	 *   `id` INT NOT NULL,
	 *   PRIMARY KEY (`id`))
	 * ENGINE = InnoDB;
	 *
	 * @return void
	 */
	public function up() {
		Schema::dropIfExists('tastingstage');

		Schema::create('tastingstage', function (Blueprint $table) {
			$table->engine = 'InnoDB';

			$table->integer('id')->unsigned();
			$table->primary('id');
		});

		$this->insertData();
	}

	/**
	 * -- -----------------------------------------------------
	 * -- Data for table `weinstein`.`tastingstage`
	 * -- -----------------------------------------------------
	 * START TRANSACTION;
	 * USE `weinstein`;
	 * INSERT INTO `weinstein`.`tastingstage` (`id`) VALUES (1);
	 * INSERT INTO `weinstein`.`tastingstage` (`id`) VALUES (2);
	 *
	 * COMMIT;
	 */
	private function insertData() {
		DB::table('tastingstage')->insert([
			[

				'id' => 1,
			],
			[
				'id' => 2,
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('tastingstage');
	}

}
