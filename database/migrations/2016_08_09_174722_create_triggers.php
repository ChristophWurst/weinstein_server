<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTriggers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `tastingnumber_AFTER_INSERT`');

        $sql = <<<'SQL'
CREATE DEFINER = CURRENT_USER TRIGGER `tastingnumber_AFTER_INSERT`
AFTER INSERT
ON `tastingnumber` FOR EACH ROW
BEGIN
	-- trigger to automatically get from 'ENROLLMENT' state to 'TASTINGNUMBERS1'
	DECLARE comp INT;
	
	SET comp = (SELECT w.competition_id
					FROM wine w
					WHERE w.id = NEW.wine_id);			
	
	IF ((SELECT DISTINCT cs.description
			FROM competitionstate cs
			JOIN competition c ON cs.id = c.competitionstate_id
			WHERE c.id = comp) = 'ENROLLMENT') THEN
		UPDATE competition
			SET competitionstate_id = competitionstate_id + 1
			WHERE id = comp;
	END IF;
END
SQL;
        DB::unprepared($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `tastingnumber_AFTER_INSERT`');
    }
}
