<?php

use App\MasterData\CompetitionState;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertDefaultCompetition extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::table('competition')->where('label', 'Weinwoche')->count() > 0) {
            return;
        }

        DB::table('competition')->insert([
            [
                'label' => 'Weinwoche',
                'competition_state_id' => CompetitionState::STATE_ENROLLMENT,
                'wuser_username' => null,
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
    }
}
