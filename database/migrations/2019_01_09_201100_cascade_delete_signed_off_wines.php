<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CascadeDeleteSignedOffWines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wines_chosen_signed_off', function (Blueprint $table) {
            $table->dropForeign('wines_chosen_signed_off_competition_id_foreign');
            $table->foreign('competition_id')
                ->references('id')
                ->on('competition')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wines_chosen_signed_off', function (Blueprint $table) {
            $table->dropForeign('wines_chosen_signed_off_competition_id_foreign');
            $table->foreign('competition_id')
                ->references('id')
                ->on('competition')
                ->onDelete('no action')
                ->onUpdate('cascade');
        });
    }
}
