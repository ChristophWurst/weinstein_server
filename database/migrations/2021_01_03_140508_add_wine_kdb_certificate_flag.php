<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddWineKdbCertificateFlag extends Migration
{
    public function up(): void
    {
        Schema::table('wine', function (Blueprint $table) {
            $table->boolean('kdb_certificate')->default(true);
        });

        $this->rebuildWineDetailsView();
    }

    public function down(): void
    {
        Schema::table('wine', function (Blueprint $table) {
            $table->dropColumn('kdb_certificate');
        });

        $this->rebuildWineDetailsView();
    }

    private function rebuildWineDetailsView(): void
    {
        $sql = <<<'SQL'
CREATE  OR REPLACE VIEW `wine_details` AS
SELECT 1 as x, w.*, ws.`order` AS winesort_order, ws.name AS winesort_name, wq.id AS quality_id, wq.label AS quality_label, uapp.username AS applicant_username, uass.username AS association_username, harm_mean(tn1.id) AS rating1, harm_mean(tn2.id) AS rating2
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
AND tn2.tastingstage_id IN (2, NULL);
SQL;

        DB::unprepared($sql);
    }
}
