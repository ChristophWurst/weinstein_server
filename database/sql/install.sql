SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema weinstein
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `weinstein` ;
CREATE SCHEMA IF NOT EXISTS `weinstein` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `weinstein` ;


-- -----------------------------------------------------
-- Table `weinstein`.`rating`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `weinstein`.`rating` ;

CREATE TABLE IF NOT EXISTS `weinstein`.`rating` (
  `tasting_id` INT NOT NULL,
  `taster_id` INT NOT NULL,
  `nr` INT NOT NULL,
  `created_at` VARCHAR(45) NULL,
  `updated_at` VARCHAR(45) NULL,
  PRIMARY KEY (`tasting_id`, `taster_id`, `nr`),
  CONSTRAINT `fk_rating_competition1`
    FOREIGN KEY (`tasting_id`)
    REFERENCES `weinstein`.`competition` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `weinstein`.`commission`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `weinstein`.`commission` ;

CREATE TABLE IF NOT EXISTS `weinstein`.`commission` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `side` CHAR(1) NOT NULL,
  `tastingsession_id` INT NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_Commission_tastingsession1_idx` (`tastingsession_id` ASC),
  CONSTRAINT `fk_Commission_tastingsession1`
    FOREIGN KEY (`tastingsession_id`)
    REFERENCES `weinstein`.`tastingsession` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `weinstein`.`taster`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `weinstein`.`taster` ;

CREATE TABLE IF NOT EXISTS `weinstein`.`taster` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `commission_id` INT NOT NULL,
  `nr` INT NOT NULL,
  `name` VARCHAR(70) NULL,
  `active` TINYINT(1) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_taster_commission1_idx` (`commission_id` ASC),
  CONSTRAINT `fk_taster_Commission1`
    FOREIGN KEY (`commission_id`)
    REFERENCES `weinstein`.`commission` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `weinstein`.`tasting`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `weinstein`.`tasting` ;

CREATE TABLE IF NOT EXISTS `weinstein`.`tasting` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `taster_id` INT NOT NULL,
  `tastingnumber_id` INT NOT NULL,
  `rating` DECIMAL(2,1) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  INDEX `fk_tasting_taster1_idx` (`taster_id` ASC),
  INDEX `fk_tasting_tastingnumber1_idx` (`tastingnumber_id` ASC),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_tasting_taster1`
    FOREIGN KEY (`taster_id`)
    REFERENCES `weinstein`.`taster` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_tasting_tastingnumber1`
    FOREIGN KEY (`tastingnumber_id`)
    REFERENCES `weinstein`.`tastingnumber` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `weinstein`.`activitylog`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `weinstein`.`activitylog` ;

CREATE TABLE IF NOT EXISTS `weinstein`.`activitylog` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `message` VARCHAR(255) NOT NULL,
  `wuser_username` VARCHAR(80) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_activitylog_wuser1_idx` (`wuser_username` ASC),
  CONSTRAINT `fk_activitylog_wuser1`
    FOREIGN KEY (`wuser_username`)
    REFERENCES `weinstein`.`wuser` (`username`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;

USE `weinstein` ;

-- -----------------------------------------------------
-- Placeholder table for view `weinstein`.`TastedWine`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `weinstein`.`TastedWine` (`wine_id` INT, `wine_nr` INT, `tastingnumber_id` INT, `tastingnumber_nr` INT, `tastingstage_id` INT, `taster_id` INT, `commission_id` INT, `tastingsession_id` INT, `result` INT);

-- -----------------------------------------------------
-- Placeholder table for view `weinstein`.`stat_taster`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `weinstein`.`stat_taster` (`taster_id` INT, `variance` INT, `deviation` INT, `avg` INT);

-- -----------------------------------------------------
-- Placeholder table for view `weinstein`.`stat_commission`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `weinstein`.`stat_commission` (`commission_id` INT, `variance` INT, `deviation` INT, `avg` INT);

-- -----------------------------------------------------
-- Placeholder table for view `weinstein`.`cat_address`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `weinstein`.`cat_address` (`competition_id` INT, `association_id` INT, `data` INT);

-- -----------------------------------------------------
-- Placeholder table for view `weinstein`.`wine_details`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `weinstein`.`wine_details` (`id` INT, `nr` INT, `competition_id` INT, `applicant_id` INT, `winesort_id` INT, `winequality_id` INT, `label` INT, `vintage` INT, `alcohol` INT, `alcoholtot` INT, `sugar` INT, `approvalnr` INT, `created_at` INT, `updated_at` INT, `kdb` INT, `sosi` INT, `chosen` INT, `excluded` INT, `comment` INT, `winesort_order` INT, `winesort_name` INT, `quality_id` INT, `quality_label` INT, `applicant_username` INT, `association_username` INT, `rating1` INT, `rating2` INT);

-- -----------------------------------------------------
-- function harm_mean
-- -----------------------------------------------------

USE `weinstein`;
DROP function IF EXISTS `weinstein`.`harm_mean`;

DELIMITER $$
USE `weinstein`$$
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
END$$

DELIMITER ;

-- -----------------------------------------------------
-- function taster_variance
-- -----------------------------------------------------

USE `weinstein`;
DROP function IF EXISTS `weinstein`.`taster_variance`;

DELIMITER $$
USE `weinstein`$$
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
END$$

DELIMITER ;

-- -----------------------------------------------------
-- function commission_variance
-- -----------------------------------------------------

USE `weinstein`;
DROP function IF EXISTS `weinstein`.`commission_variance`;

DELIMITER $$
USE `weinstein`$$
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
END$$

DELIMITER ;

-- -----------------------------------------------------
-- View `weinstein`.`TastedWine`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `weinstein`.`TastedWine` ;
DROP TABLE IF EXISTS `weinstein`.`TastedWine`;
USE `weinstein`;
CREATE  OR REPLACE VIEW `TastedWine` AS
    SELECT 
        wine.id AS wine_id,
        wine.nr AS wine_nr,
        tastingnumber.id AS tastingnumber_id,
        tastingnumber.nr AS tastingnumber_nr,
        tastingnumber.tastingstage_id AS tastingstage_id,
        taster.id AS taster_id,
        commission.id AS commission_id,
        tastingsession.id AS tastingsession_id,
        harm_mean(tastingnumber.id) AS result
    FROM
        wine
            INNER JOIN
        tastingnumber ON tastingnumber.wine_id = wine.id
            INNER JOIN
        tasting ON tasting.tastingnumber_id = tastingnumber.id
            INNER JOIN
        taster ON tasting.taster_id = taster.id
            INNER JOIN
        commission ON taster.commission_id = commission.id
            INNER JOIN
        tastingsession ON commission.tastingsession_id = tastingsession.id
    GROUP BY tastingnumber_id, wine.id, wine.nr, commission.id;

-- -----------------------------------------------------
-- View `weinstein`.`stat_taster`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `weinstein`.`stat_taster` ;
DROP TABLE IF EXISTS `weinstein`.`stat_taster`;
USE `weinstein`;
CREATE  OR REPLACE VIEW `stat_taster` AS
SELECT t.id AS taster_id, taster_variance(t.id) AS variance, sqrt(taster_variance(t.id)) AS deviation, AVG(rating) AS avg
FROM taster t
LEFT OUTER JOIN tasting ta
ON ta.taster_id = t.id
GROUP BY t.id;

-- -----------------------------------------------------
-- View `weinstein`.`stat_commission`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `weinstein`.`stat_commission` ;
DROP TABLE IF EXISTS `weinstein`.`stat_commission`;
USE `weinstein`;
CREATE  OR REPLACE VIEW `stat_commission` AS
SELECT c.id AS commission_id, commission_variance(c.id) AS variance, sqrt(commission_variance(c.id)) AS deviation, AVG(rating) AS avg
FROM tasting ta
RIGHT OUTER JOIN taster t
ON t.id = ta.taster_id
RIGHT OUTER JOIN commission c
ON c.id = t.commission_id
GROUP BY c.id;


-- -----------------------------------------------------
-- View `weinstein`.`cat_address`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `weinstein`.`cat_address` ;
DROP TABLE IF EXISTS `weinstein`.`cat_address`;
USE `weinstein`;
CREATE  OR REPLACE VIEW `cat_address` AS
SELECT DISTINCT c.id AS competition_id, ass.id AS association_id, CONCAT(IF(app.label IS NULL OR app.label = "", "", CONCAT(app.label, " ")),
		IF(app.title IS NULL, "", CONCAT(app.title, " ")), app.lastname, " ", app.firstname, ", ", ad.street, " ",
		IF(ad.nr IS NULL, "", ad.nr), ", ", ad.zipcode, " ", ad.city, "$", IF(app.phone IS NULL, "", CONCAT("Tel.:", app.mobile)),
		IF(app.mobile IS NULL, "", CONCAT(", Mobil:", app.mobile)), IF(app.web IS NULL, "", CONCAT(", ", app.web))) AS `data`
FROM competition c
LEFT OUTER JOIN wine w
ON w.competition_id = c.id
LEFT OUTER JOIN applicant app
ON app.id = w.applicant_id
LEFT OUTER JOIN address ad
ON ad.id = app.address_id
LEFT OUTER JOIN association ass
ON ass.id = app.association_id
WHERE app.id IN (SELECT DISTINCT applicant_id
				 FROM wine
				 WHERE chosen=1
				 AND competition_id = c.id)
ORDER BY ass.id;


-- -----------------------------------------------------
-- View `weinstein`.`wine_details`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `weinstein`.`wine_details` ;
DROP TABLE IF EXISTS `weinstein`.`wine_details`;
USE `weinstein`;
CREATE  OR REPLACE VIEW `wine_details` AS
SELECT w.*, ws.`order` AS winesort_order, ws.name AS winesort_name, wq.id AS quality_id, wq.label AS quality_label, uapp.username AS applicant_username, uass.username AS association_username, harm_mean(tn1.id) AS rating1, harm_mean(tn2.id) AS rating2
FROM wine w
JOIN applicant app
	ON app.id = w.applicant_id
JOIN association ass
	ON ass.id = app.association_id
JOIN winesort ws
	ON ws.id = w.winesort_id
LEFT OUTER JOIN winequality wq
	ON wq.id = w.winequality_id
LEFT OUTER JOIN wuser uapp
	ON uapp.username = app.wuser_username
LEFT OUTER JOIN wuser uass
	ON uass.username = ass.wuser_username
LEFT OUTER JOIN weinstein.tastingnumber AS tn1
	ON w.id = tn1.wine_id
AND tn1.tastingstage_id = 1
LEFT OUTER JOIN weinstein.tastingnumber AS tn2
ON w.id = tn2.wine_id
AND tn2.tastingstage_id IN (2, NULL);
SET SQL_MODE = '';
GRANT USAGE ON *.* TO weinstein;
 DROP USER weinstein;
SET SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';
CREATE USER 'weinstein' IDENTIFIED BY 'Toh5phooDi9ahdohchu1cabu8mej5iut';

GRANT SELECT, INSERT, UPDATE, DELETE ON TABLE `weinstein`.* TO 'weinstein';

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;


USE `weinstein`;

DELIMITER $$

USE `weinstein`$$
DROP TRIGGER IF EXISTS `weinstein`.`tastingnumber_AFTER_INSERT` $$
USE `weinstein`$$
CREATE DEFINER = CURRENT_USER TRIGGER `weinstein`.`tastingnumber_AFTER_INSERT`
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
END$$


DELIMITER ;
