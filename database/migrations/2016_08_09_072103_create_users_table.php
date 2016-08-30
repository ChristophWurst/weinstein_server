<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License,version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * -- -----------------------------------------------------
	 * -- Table `weinstein`.`wuser`
	 * -- -----------------------------------------------------
	 * DROP TABLE IF EXISTS `weinstein`.`wuser` ;
	 * CREATE TABLE IF NOT EXISTS `weinstein`.`wuser` (
	 *   `username` VARCHAR(80) NOT NULL,
	 *   `password` VARCHAR(80) NULL,
	 *   `admin` TINYINT(1) NULL DEFAULT 0,
	 *   `remember_token` VARCHAR(100) NULL,
	 *   `created_at` TIMESTAMP NULL,
	 *   `updated_at` TIMESTAMP NULL,
	 *   PRIMARY KEY (`username`))
	 * ENGINE = InnoDB;
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('wuser', function (Blueprint $table) {
			$table->engine = 'InnoDB';

			$table->string('username');
			$table->string('password');
			$table->boolean('admin');
			$table->string('remember_token');
			$table->timestamps();

			$table->primary('username');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('wuser');
	}

}
