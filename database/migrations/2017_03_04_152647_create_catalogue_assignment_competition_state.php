<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateCatalogueAssignmentCompetitionState extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('competition_state')
            ->where('id', 10)
            ->update(['id' => 11]);
        DB::table('competition_state')->insert([
            'id' => 10,
            'description' => 'CATALOGUE_NUMBERS',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('competition_state')
            ->where('id', 10)
            ->delete();
        DB::table('competition_state')
            ->where('id', 11)
            ->update(['id' => 10]);
    }
}
