<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTastingTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * -- -----------------------------------------------------
	 * -- Table `weinstein`.`tasting`
	 * -- -----------------------------------------------------
	 * DROP TABLE IF EXISTS `weinstein`.`tasting` ;
	 *
	 * CREATE TABLE IF NOT EXISTS `weinstein`.`tasting` (
	 *   `id` INT NOT NULL AUTO_INCREMENT,
	 *   `taster_id` INT NOT NULL,
	 *   `tastingnumber_id` INT NOT NULL,
	 *   `rating` DECIMAL(2,1) NOT NULL,
	 *   `created_at` TIMESTAMP NULL,
	 *   `updated_at` TIMESTAMP NULL,
	 *   INDEX `fk_tasting_taster1_idx` (`taster_id` ASC),
	 *   INDEX `fk_tasting_tastingnumber1_idx` (`tastingnumber_id` ASC),
	 *   PRIMARY KEY (`id`),
	 *   CONSTRAINT `fk_tasting_taster1`
	 *     FOREIGN KEY (`taster_id`)
	 *     REFERENCES `weinstein`.`taster` (`id`)
	 *     ON DELETE NO ACTION
	 *     ON UPDATE NO ACTION,
	 *   CONSTRAINT `fk_tasting_tastingnumber1`
	 *     FOREIGN KEY (`tastingnumber_id`)
	 *     REFERENCES `weinstein`.`tastingnumber` (`id`)
	 *     ON DELETE NO ACTION
	 *     ON UPDATE NO ACTION)
	 * ENGINE = InnoDB;
	 * 
	 * @return void
	 */
	public function up() {
		Schema::dropIfExists('tasting');

		Schema::create('tasting', function (Blueprint $table) {
			$table->engine = 'InnoDB';

			$table->increments('id');
			$table->integer('taster_id')->unsigned();
			$table->integer('tastingnumber_id')->unsigned();
			$table->decimal('rating', 2, 1);
			$table->timestamps();

			$table->foreign('taster_id')
				->references('id')
				->on('taster')
				->onDelete('no action')
				->onUpdate('no action');
			$table->foreign('tastingnumber_id')
				->references('id')
				->on('tastingnumber')
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
		Schema::drop('tasting');
	}

}
