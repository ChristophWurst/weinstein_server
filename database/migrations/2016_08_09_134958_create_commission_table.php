<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCommissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * -- -----------------------------------------------------
     * -- Table `weinstein`.`commission`
     * -- -----------------------------------------------------
     * DROP TABLE IF EXISTS `weinstein`.`commission` ;
     *
     * CREATE TABLE IF NOT EXISTS `weinstein`.`commission` (
     *   `id` INT NOT NULL AUTO_INCREMENT,
     *   `side` CHAR(1) NOT NULL,
     *   `tastingsession_id` INT NOT NULL,
     *   `created_at` TIMESTAMP NULL,
     *   `updated_at` TIMESTAMP NULL,
     *   PRIMARY KEY (`id`),
     *   INDEX `fk_Commission_tastingsession1_idx` (`tastingsession_id` ASC),
     *   CONSTRAINT `fk_Commission_tastingsession1`
     *     FOREIGN KEY (`tastingsession_id`)
     *     REFERENCES `weinstein`.`tastingsession` (`id`)
     *     ON DELETE NO ACTION
     *     ON UPDATE NO ACTION)
     * ENGINE = InnoDB;
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('commission');

        Schema::create('commission', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->char('side', 1);
            $table->integer('tastingsession_id')->unsigned();
            $table->timestamps();

            $table->foreign('tastingsession_id')
                ->references('id')
                ->on('tastingsession')
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
        Schema::drop('commission');
    }
}
