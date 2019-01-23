<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePypiStats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stats_pypi', function (Blueprint $table) {
            $table->date('date');
            $table->string('project');
            $table->unsignedInteger('downloads');

            $table->unique(['date', 'project']);
            $table->index('date');
            $table->index('project');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stats_pypi');
    }
}
