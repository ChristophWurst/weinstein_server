<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWinesortTable extends Migration
{
    /**
     * Run the migrations.
     *
     * -- -----------------------------------------------------
     * -- Table `weinstein`.`winesort`
     * -- -----------------------------------------------------
     * DROP TABLE IF EXISTS `weinstein`.`winesort` ;
     *
     * CREATE TABLE IF NOT EXISTS `weinstein`.`winesort` (
     *   `id` INT NOT NULL AUTO_INCREMENT,
     *   `order` INT NOT NULL,
     *   `name` VARCHAR(45) NOT NULL,
     *   `created_at` TIMESTAMP NULL,
     *   `updated_at` TIMESTAMP NULL,
     *   PRIMARY KEY (`id`),
     *   UNIQUE INDEX `order_UNIQUE` (`order` ASC))
     * ENGINE = InnoDB;
     *
     * @return void
     */
    public function up()
    {
        Schema::create('winesort', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('order')->unique();
            $table->string('name', 45);
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
        Schema::drop('winesort');
    }
}
