<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * -- -----------------------------------------------------
     * -- Table `weinstein`.`taster`
     * -- -----------------------------------------------------
     * DROP TABLE IF EXISTS `weinstein`.`taster` ;
     *
     * CREATE TABLE IF NOT EXISTS `weinstein`.`taster` (
     *   `id` INT NOT NULL AUTO_INCREMENT,
     *   `commission_id` INT NOT NULL,
     *   `nr` INT NOT NULL,
     *   `name` VARCHAR(70) NULL,
     *   `active` TINYINT(1) NOT NULL,
     *   `created_at` TIMESTAMP NULL,
     *   `updated_at` TIMESTAMP NULL,
     *   PRIMARY KEY (`id`),
     *   INDEX `fk_taster_commission1_idx` (`commission_id` ASC),
     *   CONSTRAINT `fk_taster_Commission1`
     *     FOREIGN KEY (`commission_id`)
     *     REFERENCES `weinstein`.`commission` (`id`)
     *     ON DELETE NO ACTION
     *     ON UPDATE NO ACTION)
     * ENGINE = InnoDB;
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('taster');

        Schema::create('taster', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('commission_id')->unsigned();
            $table->integer('nr')->unsigned();
            $table->string('name', 70)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('commission_id')
                ->references('id')
                ->on('commission')
                ->onDelete('no action')
                ->onUpdate('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('taster');
    }
}
