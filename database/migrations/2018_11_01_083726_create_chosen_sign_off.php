<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChosenSignOff extends Migration
{
    public function up()
    {
		Schema::create('wines_chosen_signed_off', function(Blueprint $table) {
			$table->engine = 'InnoDB';

			$table->increments('id');
			$table->integer('competition_id')->unsigned();
			$table->integer('association_id')->unsigned();
			$table->unique([
				'competition_id',
				'association_id'
			]);
			$table->timestamps();

			$table->foreign('competition_id')
				->references('id')
				->on('competition')
				->onDelete('no action')
				->onUpdate('cascade');
			$table->foreign('association_id')
				->references('id')
				->on('association')
				->onDelete('cascade')
				->onUpdate('cascade');
		});
    }

    public function down()
    {
		Schema::dropIfExists('wines_chosen_signed_off');
    }
}
