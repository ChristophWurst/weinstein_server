<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTastingnumberTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * -- -----------------------------------------------------
	 * -- Table `weinstein`.`tastingnumber`
	 * -- -----------------------------------------------------
	 * DROP TABLE IF EXISTS `weinstein`.`tastingnumber` ;
	 *
	 * CREATE TABLE IF NOT EXISTS `weinstein`.`tastingnumber` (
	 *   `id` INT NOT NULL AUTO_INCREMENT,
	 *   `tastingstage_id` INT NOT NULL,
	 *   `wine_id` INT NOT NULL,
	 *   `nr` INT NOT NULL,
	 *   `updated_at` TIMESTAMP NULL,
	 *   `created_at` TIMESTAMP NULL,
	 *   INDEX `fk_tastingnumber_tastingstage1_idx` (`tastingstage_id` ASC),
	 *   INDEX `fk_tastingnumber_wine1_idx` (`wine_id` ASC),
	 *   PRIMARY KEY (`id`),
	 *   CONSTRAINT `fk_tastingnumber_tastingstage1`
	 *     FOREIGN KEY (`tastingstage_id`)
	 *     REFERENCES `weinstein`.`tastingstage` (`id`)
	 *     ON DELETE NO ACTION
	 *     ON UPDATE NO ACTION,
	 *   CONSTRAINT `fk_tastingnumber_wine1`
	 *     FOREIGN KEY (`wine_id`)
	 *     REFERENCES `weinstein`.`wine` (`id`)
	 *     ON DELETE NO ACTION
	 *     ON UPDATE NO ACTION)
	 * ENGINE = InnoDB;
	 *
	 * @return void
	 */
	public function up() {
		Schema::dropIfExists('tastingnumber');

		Schema::create('tastingnumber', function(Blueprint $table) {
			$table->engine = 'InnoDB';

			$table->increments('id');
			$table->integer('tastingstage_id')->unsigned();
			$table->integer('wine_id')->unsigned();
			$table->integer('nr');
			$table->timestamps();

			$table->foreign('tastingstage_id')
				->references('id')
				->on('tastingstage')
				->onDelete('no action')
				->onUpdate('no action');
			$table->foreign('wine_id')
				->references('id')
				->on('wine')
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
		Schema::drop('tastingnumber');
	}

}
