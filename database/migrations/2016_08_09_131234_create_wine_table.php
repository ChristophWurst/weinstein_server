<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWineTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * -- -----------------------------------------------------
	 * -- Table `weinstein`.`wine`
	 * -- -----------------------------------------------------
	 * DROP TABLE IF EXISTS `weinstein`.`wine` ;
	 *
	 * CREATE TABLE IF NOT EXISTS `weinstein`.`wine` (
	 *   `id` INT NOT NULL AUTO_INCREMENT,
	 *   `nr` INT NULL,
	 *   `competition_id` INT NOT NULL,
	 *   `applicant_id` BIGINT NOT NULL,
	 *   `winesort_id` INT NOT NULL,
	 *   `winequality_id` INT NULL DEFAULT NULL,
	 *   `label` VARCHAR(50) NULL,
	 *   `vintage` YEAR NOT NULL,
	 *   `alcohol` DECIMAL(3,1) NOT NULL,
	 *   `alcoholtot` DECIMAL(3,1) NULL,
	 *   `sugar` DECIMAL(4,1) NOT NULL,
	 *   `approvalnr` VARCHAR(20) NULL,
	 *   `created_at` TIMESTAMP NULL,
	 *   `updated_at` TIMESTAMP NULL,
	 *   `kdb` TINYINT(1) NOT NULL DEFAULT 0,
	 *   `sosi` TINYINT(1) NOT NULL DEFAULT 0,
	 *   `chosen` TINYINT(1) NOT NULL DEFAULT 1,
	 *   `excluded` TINYINT(1) NOT NULL DEFAULT 0,
	 *   `comment` VARCHAR(100) NULL,
	 *   PRIMARY KEY (`id`),
	 *   INDEX `fk_table1_winesort1_idx` (`winesort_id` ASC),
	 *   INDEX `fk_table1_applicant1_idx` (`applicant_id` ASC),
	 *   INDEX `fk_wine_competition1_idx` (`competition_id` ASC),
	 *   INDEX `fk_wine_winequality1_idx` (`winequality_id` ASC),
	 *   CONSTRAINT `fk_wine_winesort`
	 *     FOREIGN KEY (`winesort_id`)
	 *     REFERENCES `weinstein`.`winesort` (`id`)
	 *     ON DELETE NO ACTION
	 *     ON UPDATE CASCADE,
	 *   CONSTRAINT `fk_wine_applicant`
	 *     FOREIGN KEY (`applicant_id`)
	 *     REFERENCES `weinstein`.`applicant` (`id`)
	 *     ON DELETE NO ACTION
	 *     ON UPDATE CASCADE,
	 *   CONSTRAINT `fk_wine_competition`
	 *     FOREIGN KEY (`competition_id`)
	 *     REFERENCES `weinstein`.`competition` (`id`)
	 *     ON DELETE NO ACTION
	 *     ON UPDATE CASCADE,
	 *   CONSTRAINT `fk_wine_winequality`
	 *     FOREIGN KEY (`winequality_id`)
	 *     REFERENCES `weinstein`.`winequality` (`id`)
	 *     ON DELETE NO ACTION
	 *     ON UPDATE NO ACTION)
	 * ENGINE = InnoDB;
	 *
	 * @return void
	 */
	public function up() {
		Schema::dropIfExists('wine');

		Schema::create('wine', function(Blueprint $table) {
			$table->engine = 'InnoDB';

			$table->increments('id');
			$table->integer('nr')->nullable;
			$table->integer('competition_id')->unsigned();
			$table->integer('applicant_id')->unsigned();
			$table->integer('winesort_id')->unsigned();
			$table->integer('winequality_id')
				->unsigned()
				->nullable()
				->default(null);
			$table->string('label', 50)->nullable();
			$table->integer('vintage')->unsigned();
			$table->decimal('alcohol', 3, 1);
			$table->decimal('alcoholtot', 3, 1);
			$table->decimal('sugar', 4, 1);
			$table->string('approvalnr', 20)->nullable();
			$table->boolean('kdb')->default(false);
			$table->boolean('sosi')->default(false);
			$table->boolean('chosen')->default(true);
			$table->boolean('excluded')->default(false);
			$table->string('comment', 100)->nullable();
			$table->timestamps();

			$table->foreign('competition_id')
				->references('id')
				->on('competition')
				->onDelete('no action')
				->onUpdate('cascade');
			$table->foreign('applicant_id')
				->references('id')
				->on('applicant')
				->onDelete('no action')
				->onUpdate('cascade');
			$table->foreign('winesort_id')
				->references('id')
				->on('winesort')
				->onDelete('no action')
				->onUpdate('cascade');
			$table->foreign('winequality_id')
				->references('id')
				->on('winequality')
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
		Schema::drop('wine');
	}

}
