<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * -- -----------------------------------------------------
	 * -- Table `weinstein`.`competition`
	 * -- -----------------------------------------------------
	 * DROP TABLE IF EXISTS `weinstein`.`competition` ;
	 *
	 * CREATE TABLE IF NOT EXISTS `weinstein`.`competition` (
	 *   `id` INT NOT NULL AUTO_INCREMENT,
	 *   `label` VARCHAR(50) NULL,
	 *   `wuser_username` VARCHAR(80) NULL,
	 *   `created_at` TIMESTAMP NULL,
	 *   `updated_at` TIMESTAMP NULL,
	 *   `competitionstate_id` INT NOT NULL DEFAULT 1,
	 *   PRIMARY KEY (`id`),
	 *   INDEX `fk_competition_wuser1_idx` (`wuser_username` ASC),
	 *   UNIQUE INDEX `label_UNIQUE` (`label` ASC),
	 *   INDEX `fk_competition_competitionstate1_idx` (`competitionstate_id` ASC),
	 *   CONSTRAINT `fk_competition_wuser1`
	 *     FOREIGN KEY (`wuser_username`)
	 *     REFERENCES `weinstein`.`wuser` (`username`)
	 *     ON DELETE SET NULL
	 *     ON UPDATE CASCADE,
	 *   CONSTRAINT `fk_competition_competitionstate1`
	 *     FOREIGN KEY (`competitionstate_id`)
	 *     REFERENCES `weinstein`.`competitionstate` (`id`)
	 *     ON DELETE NO ACTION
	 *     ON UPDATE NO ACTION)
	 * ENGINE = InnoDB;
	 * 
	 * @return void
	 */
	public function up() {
		Schema::create('competition', function (Blueprint $table) {
			$table->engine = 'InnoDB';

			$table->increments('id');
			$table->string('label', 45)->unique();
			$table->string('wuser_username')->nullable();
			$table->integer('competition_state_id')
				->unsigned()
				->default(1);
			$table->timestamps();

			$table->foreign('wuser_username')
				->references('username')
				->on('wuser')
				->onDelete('set null')
				->onUpdate('cascade');
			$table->foreign('competition_state_id')
				->references('id')
				->on('competition_state')
				->onDelete('no action')
				->onUpdate('no action');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('competition');
	}

}
