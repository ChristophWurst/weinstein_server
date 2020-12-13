<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createTastedWinesView();
        $this->createStatTasterView();
        $this->createStatCommissionView();
        $this->createCatAddressView();
        $this->createWineDetailsView();
    }

    private function createTastedWinesView()
    {
        $sql = <<<'SQL'
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
SQL;
        DB::unprepared($sql);
    }

    private function createStatTasterView()
    {
        $sql = <<<'SQL'
CREATE  OR REPLACE VIEW `stat_taster` AS
SELECT t.id AS taster_id, taster_variance(t.id) AS variance, sqrt(taster_variance(t.id)) AS deviation, AVG(rating) AS avg
FROM taster t
LEFT OUTER JOIN tasting ta
ON ta.taster_id = t.id
GROUP BY t.id
SQL;
        DB::unprepared($sql);
    }

    private function createStatCommissionView()
    {
        $sql = <<<'SQL'
CREATE  OR REPLACE VIEW `stat_commission` AS
SELECT c.id AS commission_id, commission_variance(c.id) AS variance, sqrt(commission_variance(c.id)) AS deviation, AVG(rating) AS avg
FROM tasting ta
RIGHT OUTER JOIN taster t
ON t.id = ta.taster_id
RIGHT OUTER JOIN commission c
ON c.id = t.commission_id
GROUP BY c.id
SQL;
        DB::unprepared($sql);
    }

    private function createCatAddressView()
    {
        $sql = <<<'SQL'
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
ORDER BY ass.id
SQL;
        DB::unprepared($sql);
    }

    private function createWineDetailsView()
    {
        $sql = <<<'SQL'
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
LEFT OUTER JOIN tastingnumber AS tn1
	ON w.id = tn1.wine_id
AND tn1.tastingstage_id = 1
LEFT OUTER JOIN tastingnumber AS tn2
ON w.id = tn2.wine_id
AND tn2.tastingstage_id IN (2, NULL)
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
        DB::unprepared('DROP VIEW IF EXISTS `TastedWine`');
        DB::unprepared('DROP VIEW IF EXISTS `stat_taster`');
        DB::unprepared('DROP VIEW IF EXISTS `stat_commission`');
        DB::unprepared('DROP VIEW IF EXISTS `cat_address`');
        DB::unprepared('DROP VIEW IF EXISTS `wine_details`');
    }
}
