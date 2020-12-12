<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActivitylogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * -- -----------------------------------------------------
     * -- Table `weinstein`.`activitylog`
     * -- -----------------------------------------------------
     * DROP TABLE IF EXISTS `weinstein`.`activitylog` ;
     *
     * CREATE TABLE IF NOT EXISTS `weinstein`.`activitylog` (
     *   `id` INT NOT NULL AUTO_INCREMENT,
     *   `message` VARCHAR(255) NOT NULL,
     *   `wuser_username` VARCHAR(80) NULL,
     *   `created_at` TIMESTAMP NULL,
     *   `updated_at` TIMESTAMP NULL,
     *   PRIMARY KEY (`id`),
     *   INDEX `fk_activitylog_wuser1_idx` (`wuser_username` ASC),
     *   CONSTRAINT `fk_activitylog_wuser1`
     *     FOREIGN KEY (`wuser_username`)
     *     REFERENCES `weinstein`.`wuser` (`username`)
     *     ON DELETE SET NULL
     *     ON UPDATE CASCADE)
     * ENGINE = InnoDB;
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('activitylog');

        Schema::create('activitylog', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('message', 255);
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
    public function down()
    {
        Schema::drop('activitylog');
    }
}
