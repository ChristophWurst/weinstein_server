<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateFunctions extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		DB::unprepared('DROP function IF EXISTS `taster_variance`');
		DB::unprepared('DROP function IF EXISTS `harm_mean`');
		DB::unprepared('DROP function IF EXISTS `commission_variance`');

		$this->createTasterVariance();
		$this->createHarmMean();
		$this->createCommissionVariance();
	}

	private function createTasterVariance() {
		$sql = <<<SQL
CREATE FUNCTION `taster_variance` (
	t_id INT
)
RETURNS FLOAT
DETERMINISTIC
BEGIN
	DECLARE dataFound BOOL DEFAULT TRUE;
	DECLARE n INT DEFAULT 0;
	DECLARE sum DOUBLE DEFAULT 0.0;
	DECLARE x DECIMAL(2,1);
	DECLARE ts_id INT;
	DECLARE t_avg DOUBLE DEFAULT 0.0;
	DECLARE ratings CURSOR FOR SELECT rating
		FROM tasting
		WHERE taster_id = t_id;

	DECLARE CONTINUE HANDLER
		FOR NOT FOUND
		SET dataFound = FALSE;

	SELECT AVG(rating) INTO t_avg
	FROM tasting
	WHERE taster_id = t_id;

	OPEN ratings;
	ratings_loop: LOOP
		FETCH ratings INTO x;
		IF NOT dataFound THEN
			LEAVE ratings_loop;
		END IF;
		SET sum = sum + POW(t_avg - x, 2);
		SET n = n + 1;
	END LOOP;
	
	CLOSE ratings;

	IF n = 0 THEN
		RETURN NULL;
	END IF;

	RETURN ROUND(sum / n, 5);
END
SQL;
		DB::unprepared($sql);
	}

	private function createHarmMean() {
		$sql = <<<SQL
CREATE FUNCTION `harm_mean` (
	tn_id INT
)
RETURNS FLOAT
DETERMINISTIC
BEGIN
	DECLARE dataFound BOOL DEFAULT TRUE;
	DECLARE n INT DEFAULT 0;
	DECLARE sum DOUBLE DEFAULT 0.0;
	DECLARE x DECIMAL(2,1);
	DECLARE ratings CURSOR FOR SELECT rating
		FROM tasting
		WHERE tastingnumber_id = tn_id;
	DECLARE CONTINUE HANDLER
		FOR NOT FOUND
		SET dataFound = FALSE;

	OPEN ratings;
	ratings_loop: LOOP
		FETCH ratings INTO x;
		IF NOT dataFound THEN
			LEAVE ratings_loop;
		END IF;
		SET sum = sum + (1 / x);
		SET n = n + 1;
	END LOOP;
	
	CLOSE ratings;

	IF n = 0 THEN
		RETURN NULL;
	END IF;

	RETURN ROUND(n / sum, 5);
END
SQL;
		DB::unprepared($sql);
	}

	private function createCommissionVariance() {
		$sql = <<<SQL
CREATE FUNCTION `commission_variance` (
	c_id INT
)
RETURNS FLOAT
DETERMINISTIC
BEGIN
	DECLARE dataFound BOOL DEFAULT TRUE;
	DECLARE n INT DEFAULT 0;
	DECLARE sum DOUBLE DEFAULT 0.0;
	DECLARE x DECIMAL(2,1);
    DECLARE ts_id INT;
	DECLARE c_avg DOUBLE DEFAULT 0.0;
	DECLARE ratings CURSOR FOR SELECT rating
							   FROM tasting ta
							   JOIN taster t
							   ON t.id = ta.taster_id
                               WHERE t.commission_id = c_id;
	DECLARE CONTINUE HANDLER
		FOR NOT FOUND
		SET dataFound = FALSE;

	SELECT AVG(rating) INTO c_avg
	FROM tasting ta
	JOIN taster t
	ON t.id = ta.taster_id
	WHERE t.commission_id = c_id;

	OPEN ratings;
	ratings_loop: LOOP
		FETCH ratings INTO x;
		IF NOT dataFound THEN
			LEAVE ratings_loop;
		END IF;
		SET sum = sum + POW(c_avg - x, 2);
		SET n = n + 1;
	END LOOP;
	
	CLOSE ratings;

	IF n = 0 THEN
		RETURN NULL;
	END IF;

	RETURN ROUND(sum / n, 5);
END
SQL;
		DB::unprepared($sql);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		DB::unprepared('DROP function IF EXISTS `taster_variance`');
		DB::unprepared('DROP function IF EXISTS `harm_mean`');
		DB::unprepared('DROP function IF EXISTS `commission_variance`');
	}

}
