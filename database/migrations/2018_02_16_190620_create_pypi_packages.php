<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePypiPackages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages_pypi', function (Blueprint $table) {
            $table->string('project')->primary();
            $table->text('description')->nullable();

            $table->timestamps();
        });

        DB::statement('ALTER TABLE packages_pypi ADD FULLTEXT project (project);');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('packages_pypi');
    }
}
