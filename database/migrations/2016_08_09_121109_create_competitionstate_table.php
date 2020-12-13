<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCompetitionstateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * -- -----------------------------------------------------
     * -- Table `weinstein`.`competitionstate`
     * -- -----------------------------------------------------
     * DROP TABLE IF EXISTS `weinstein`.`competitionstate` ;
     *
     * CREATE TABLE IF NOT EXISTS `weinstein`.`competitionstate` (
     *   `id` INT NOT NULL,
     *   `description` VARCHAR(45) NOT NULL,
     *   PRIMARY KEY (`id`))
     * ENGINE = InnoDB;
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competition_state', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->integer('id')->unsigned();
            $table->primary('id');
            $table->string('description', 45);
        });

        /*
         * -- -----------------------------------------------------
         * -- Data for table `weinstein`.`competitionstate`
         * -- -----------------------------------------------------
         * START TRANSACTION;
         * USE `weinstein`;
         * INSERT INTO `weinstein`.`competitionstate` (`id`, `description`) VALUES (1, 'ENROLLMENT');
         * INSERT INTO `weinstein`.`competitionstate` (`id`, `description`) VALUES (2, 'TASTINGNUMBERS1');
         * INSERT INTO `weinstein`.`competitionstate` (`id`, `description`) VALUES (3, 'TASTING1');
         * INSERT INTO `weinstein`.`competitionstate` (`id`, `description`) VALUES (4, 'KDB');
         * INSERT INTO `weinstein`.`competitionstate` (`id`, `description`) VALUES (5, 'EXCLUDE');
         * INSERT INTO `weinstein`.`competitionstate` (`id`, `description`) VALUES (6, 'TASTINGNUMBERS2');
         * INSERT INTO `weinstein`.`competitionstate` (`id`, `description`) VALUES (7, 'TASTING2');
         * INSERT INTO `weinstein`.`competitionstate` (`id`, `description`) VALUES (9, 'CHOOSE');
         * INSERT INTO `weinstein`.`competitionstate` (`id`, `description`) VALUES (8, 'SOSI');
         * INSERT INTO `weinstein`.`competitionstate` (`id`, `description`) VALUES (10, 'FINISHED');
         *
         * COMMIT;
         */
        DB::table('competition_state')->insert([
            [
                'id' => 1,
                'description' => 'ENROLLMENT',
            ],
            [
                'id' => 2,
                'description' => 'TASTINGNUMBERS1',
            ],
            [
                'id' => 3,
                'description' => 'TASTING1',
            ],
            [
                'id' => 4,
                'description' => 'KDB',
            ],
            [
                'id' => 5,
                'description' => 'EXCLUDE',
            ],
            [
                'id' => 6,
                'description' => 'TASTINGNUMBERS2',
            ],
            [
                'id' => 7,
                'description' => 'TASTING2',
            ],
            [
                'id' => 8,
                'description' => 'CHOOSE',
            ],
            [
                'id' => 9,
                'description' => 'SOSI',
            ],
            [
                'id' => 10,
                'description' => 'FINISHED',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('competition_state');
    }
}
