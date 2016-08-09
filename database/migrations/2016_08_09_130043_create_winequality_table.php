<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWinequalityTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * -- -----------------------------------------------------
	 * -- Table `weinstein`.`winequality`
	 * -- -----------------------------------------------------
	 * DROP TABLE IF EXISTS `weinstein`.`winequality` ;
	 *
	 * CREATE TABLE IF NOT EXISTS `weinstein`.`winequality` (
	 *   `id` INT NOT NULL,
	 *   `label` VARCHAR(45) NOT NULL,
	 *   `abbr` VARCHAR(3) NOT NULL,
	 *   PRIMARY KEY (`id`))
	 * ENGINE = InnoDB;
	 *
	 * @return void
	 */
	public function up() {
		Schema::dropIfExists('winequality');

		Schema::create('winequality', function (Blueprint $table) {
			$table->engine = 'InnoDB';

			$table->integer('id')->unsigned();
			$table->primary('id');
			$table->string('label', 45);
			$table->string('abbr', 3);
		});

		$this->insertData();
	}

	/**
	 * -- -----------------------------------------------------
	 * -- Data for table `weinstein`.`winequality`
	 * -- -----------------------------------------------------
	 * START TRANSACTION;
	 * USE `weinstein`;
	 * INSERT INTO `weinstein`.`winequality` (`id`, `label`, `abbr`) VALUES (1, 'DAC', 'DAC');
	 * INSERT INTO `weinstein`.`winequality` (`id`, `label`, `abbr`) VALUES (2, 'Qualitätswein', 'QW');
	 * INSERT INTO `weinstein`.`winequality` (`id`, `label`, `abbr`) VALUES (3, 'Kabinett', 'KAB');
	 * INSERT INTO `weinstein`.`winequality` (`id`, `label`, `abbr`) VALUES (4, 'Spätlese', 'SPL');
	 * INSERT INTO `weinstein`.`winequality` (`id`, `label`, `abbr`) VALUES (5, 'Auslese', 'ALW');
	 * INSERT INTO `weinstein`.`winequality` (`id`, `label`, `abbr`) VALUES (6, 'Eiswein', 'EIW');
	 * INSERT INTO `weinstein`.`winequality` (`id`, `label`, `abbr`) VALUES (7, 'Beerenauslese', 'BAL');
	 * INSERT INTO `weinstein`.`winequality` (`id`, `label`, `abbr`) VALUES (8, 'Ausbruch', 'AUB');
	 * INSERT INTO `weinstein`.`winequality` (`id`, `label`, `abbr`) VALUES (9, 'Trockenbeerenauslese', 'TBA');
	 * INSERT INTO `weinstein`.`winequality` (`id`, `label`, `abbr`) VALUES (10, 'Strohwein', 'STW');
	 *
	 * COMMIT;
	 */
	private function insertData() {
		DB::table('winequality')->insert([
			[
				'id' => 1,
				'label' => 'DAC',
				'abbr' => 'DAC',
			],
			[
				'id' => 2,
				'label' => 'Qualitätswein',
				'abbr' => 'QW',
			],
			[
				'id' => 3,
				'label' => 'Kabinett',
				'abbr' => 'KAB',
			],
			[
				'id' => 4,
				'label' => 'Spätlese',
				'abbr' => 'SPL',
			],
			[
				'id' => 5,
				'label' => 'Auslese',
				'abbr' => 'ALW',
			],
			[
				'id' => 6,
				'label' => 'Eiswein',
				'abbr' => 'EIW',
			],
			[
				'id' => 7,
				'label' => 'Beerenauslese',
				'abbr' => 'BAL',
			],
			[
				'id' => 8,
				'label' => 'Ausbruch',
				'abbr' => 'AUB',
			],
			[
				'id' => 9,
				'label' => 'Trockenbeerenauslese',
				'abbr' => 'TBA',
			],
			[
				'id' => 10,
				'label' => 'Strohwein',
				'abbr' => 'STW',
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('winequality');
	}

}
