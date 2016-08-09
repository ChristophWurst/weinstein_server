<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssociationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * -- -----------------------------------------------------
	 * -- Table `weinstein`.`association`
	 * -- -----------------------------------------------------
	 * DROP TABLE IF EXISTS `weinstein`.`association` ;
	 *
	 * CREATE TABLE IF NOT EXISTS `weinstein`.`association` (
	 *   `id` INT NOT NULL,
	 *   `name` VARCHAR(80) NULL,
	 *   `wuser_username` VARCHAR(80) NULL,
	 *   `updated_at` TIMESTAMP NULL,
	 *   `created_at` TIMESTAMP NULL,
	 *   PRIMARY KEY (`id`),
	 *   INDEX `fk_association_wuser1_idx` (`wuser_username` ASC),
	 *   CONSTRAINT `fk_association_wuser1`
	 *     FOREIGN KEY (`wuser_username`)
	 *     REFERENCES `weinstein`.`wuser` (`username`)
	 *     ON DELETE SET NULL
	 *     ON UPDATE CASCADE)
	 * ENGINE = InnoDB;
	 * 
	 * @return void
	 */
	public function up() {
		Schema::create('association', function(Blueprint $table) {
			$table->engine = 'InnoDB';

			$table->increments('id');
			$table->string('name')->nullable();
			$table->string('wuser_username')->nullable();
			$table->timestamps();

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
		Schema::drop('association');
	}

}
