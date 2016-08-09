<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicantTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * -- -----------------------------------------------------
	 * -- Table `weinstein`.`applicant`
	 * -- -----------------------------------------------------
	 * DROP TABLE IF EXISTS `weinstein`.`applicant` ;
	 *
	 * CREATE TABLE IF NOT EXISTS `weinstein`.`applicant` (
	 *   `id` BIGINT NOT NULL,
	 *   `wuser_username` VARCHAR(80) NULL,
	 *   `association_id` INT NOT NULL,
	 *   `address_id` INT NOT NULL,
	 *   `label` VARCHAR(45) NULL,
	 *   `title` VARCHAR(45) NULL,
	 *   `firstname` VARCHAR(80) NULL,
	 *   `lastname` VARCHAR(80) NOT NULL,
	 *   `phone` VARCHAR(25) NULL,
	 *   `fax` VARCHAR(25) NULL,
	 *   `mobile` VARCHAR(25) NULL,
	 *   `email` VARCHAR(100) NULL,
	 *   `web` VARCHAR(100) NULL,
	 *   `created_at` TIMESTAMP NULL,
	 *   `updated_at` TIMESTAMP NULL,
	 *   PRIMARY KEY (`id`),
	 *   INDEX `fk_applicant_adress_idx` (`address_id` ASC),
	 *   INDEX `fk_applicant_wuser1_idx` (`wuser_username` ASC),
	 *   INDEX `fk_applicant_association1_idx` (`association_id` ASC),
	 *   CONSTRAINT `fk_applicant_address`
	 *     FOREIGN KEY (`address_id`)
	 *     REFERENCES `weinstein`.`address` (`id`)
	 *     ON DELETE NO ACTION
	 *     ON UPDATE NO ACTION,
	 *   CONSTRAINT `fk_applicant_wuser`
	 *     FOREIGN KEY (`wuser_username`)
	 *     REFERENCES `weinstein`.`wuser` (`username`)
	 *     ON DELETE SET NULL
	 *     ON UPDATE CASCADE,
	 *   CONSTRAINT `fk_applicant_association`
	 *     FOREIGN KEY (`association_id`)
	 *     REFERENCES `weinstein`.`association` (`id`)
	 *     ON DELETE NO ACTION
	 *     ON UPDATE CASCADE)
	 * ENGINE = InnoDB;
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('applicant', function (Blueprint $table) {
			$table->engine = 'InnoDB';

			$table->increments('id');
			$table->string('wuser_username')->nullable();
			$table->integer('association_id')->unsigned();
			$table->integer('address_id')->unsigned();
			$table->string('label', 45)->nullable();
			$table->string('title', 45)->nullable();
			$table->string('firstname', 80)->nullable();
			$table->string('lastname', 80);
			$table->string('phone', 25)->nullable();
			$table->string('fax', 25)->nullable();
			$table->string('mobile', 25)->nullable();
			$table->string('email', 100)->nullable();
			$table->string('web', 100)->nullable();
			$table->timestamps();

			$table->foreign('wuser_username')
				->references('username')
				->on('wuser')
				->onDelete('set null')
				->onUpdate('cascade');
			$table->foreign('association_id')
				->references('id')
				->on('association')
				->onDelete('no action')
				->onUpdate('cascade');
			$table->foreign('address_id')
				->references('id')
				->on('address')
				->onDelete('no action')
				->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('applicant');
	}

}
