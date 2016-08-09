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
-- View `weinstein`.`wine_details`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `weinstein`.`wine_details` ;
DROP TABLE IF EXISTS `weinstein`.`wine_details`;
USE `weinstein`;
;
SET SQL_MODE = '';
GRANT USAGE ON *.* TO weinstein;
 DROP USER weinstein;
SET SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';
CREATE USER 'weinstein' IDENTIFIED BY 'Toh5phooDi9ahdohchu1cabu8mej5iut';

GRANT SELECT, INSERT, UPDATE, DELETE ON TABLE `weinstein`.* TO 'weinstein';

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
