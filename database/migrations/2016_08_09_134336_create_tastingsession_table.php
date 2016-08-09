<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTastingsessionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * 
	 * -- -----------------------------------------------------
	 * -- Table `weinstein`.`tastingsession`
	 * -- -----------------------------------------------------
	 * DROP TABLE IF EXISTS `weinstein`.`tastingsession` ;
	 *
	 * CREATE TABLE IF NOT EXISTS `weinstein`.`tastingsession` (
	 *   `id` INT NOT NULL AUTO_INCREMENT,
	 *   `competition_id` INT NOT NULL,
	 *   `tastingstage_id` INT NOT NULL,
	 *   `nr` INT NOT NULL,
	 *   `wuser_username` VARCHAR(80) NULL,
	 *   `locked` TINYINT(1) NOT NULL DEFAULT 0,
	 *   `created_at` TIMESTAMP NULL,
	 *   `updated_at` TIMESTAMP NULL,
	 *   PRIMARY KEY (`id`),
	 *   INDEX `fk_tastingsession_wuser1_idx` (`wuser_username` ASC),
	 *   INDEX `fk_tastingsession_tastingstage1_idx` (`tastingstage_id` ASC),
	 *   INDEX `fk_tastingsession_competition1_idx` (`competition_id` ASC),
	 *   CONSTRAINT `fk_tastingsession_wuser1`
	 *     FOREIGN KEY (`wuser_username`)
	 *     REFERENCES `weinstein`.`wuser` (`username`)
	 *     ON DELETE NO ACTION
	 *     ON UPDATE CASCADE,
	 *   CONSTRAINT `fk_tastingsession_tastingstage1`
	 *     FOREIGN KEY (`tastingstage_id`)
	 *     REFERENCES `weinstein`.`tastingstage` (`id`)
	 *     ON DELETE NO ACTION
	 *     ON UPDATE NO ACTION,
	 *   CONSTRAINT `fk_tastingsession_competition1`
	 *     FOREIGN KEY (`competition_id`)
	 *     REFERENCES `weinstein`.`competition` (`id`)
	 *     ON DELETE NO ACTION
	 *     ON UPDATE NO ACTION)
	 * ENGINE = InnoDB;
	 *
	 * @return void
	 */
	public function up() {
		Schema::dropIfExists('tastingsession');

		Schema::create('tastingsession', function (Blueprint $table) {
			$table->engine = 'InnoDB';

			$table->increments('id');
			$table->integer('competition_id')->unsigned();
			$table->integer('tastingstage_id')->unsigned();
			$table->string('wuser_username')->nullable();
			$table->integer('nr')->unsigned();
			$table->boolean('locked')->default(false);
			$table->timestamps();

			$table->foreign('competition_id')
				->references('id')
				->on('competition')
				->onDelete('no action')
				->onUpdate('no action');
			$table->foreign('tastingstage_id')
				->references('id')
				->on('tastingstage')
				->onDelete('no action')
				->onUpdate('no action');
			$table->foreign('wuser_username')
				->references('username')
				->on('wuser')
				->onDelete('set null')
				->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('tastingsession');
	}

}
