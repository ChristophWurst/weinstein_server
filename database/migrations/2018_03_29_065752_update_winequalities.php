<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateWinequalities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('winequality')->insert([
            [
                'id' => 11,
                'label' => 'Perlwein',
                'abbr' => 'PW',
            ],
            [
                'id' => 12,
                'label' => 'Schaumwein',
                'abbr' => 'SW',
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
        DB::table('winequality')->delete(11);
        DB::table('winequality')->delete(12);
    }
}
