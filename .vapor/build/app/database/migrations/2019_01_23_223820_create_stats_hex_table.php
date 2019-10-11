<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatsHexTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stats_hex', function (Blueprint $table) {
            $table->date('date');
            $table->string('package');
            $table->foreign('package')->references('name')->on('packages_hex')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedInteger('downloads');

            $table->unique(['date', 'package']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stats_hex');
    }
}
