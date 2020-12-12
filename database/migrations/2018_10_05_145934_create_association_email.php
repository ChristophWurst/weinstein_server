<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAssociationEmail extends Migration
{
    public function up()
    {
        Schema::table('association', function (Blueprint $table) {
            $table->string('email')->nullable();
        });
    }

    public function down()
    {
        Schema::table('association', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
}
