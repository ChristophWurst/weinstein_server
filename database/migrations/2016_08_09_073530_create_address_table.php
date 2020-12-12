<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * -- -----------------------------------------------------
     * -- Table `weinstein`.`address`
     * -- -----------------------------------------------------
     * DROP TABLE IF EXISTS `weinstein`.`address` ;
     *
     * CREATE TABLE IF NOT EXISTS `weinstein`.`address` (
     *   `id` INT NOT NULL AUTO_INCREMENT,
     *   `zipcode` INT(11) NULL,
     *   `city` VARCHAR(70) NULL,
     *   `street` VARCHAR(100) NULL,
     *   `nr` VARCHAR(20) NULL,
     *   `created_at` TIMESTAMP NULL,
     *   `updated_at` TIMESTAMP NULL,
     *   PRIMARY KEY (`id`))
     * ENGINE = InnoDB;
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('zipcode', false, true);
            $table->string('city', 100);
            $table->string('street', 100);
            $table->string('nr', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('address');
    }
}
