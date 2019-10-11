<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPypiPackageStatsForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DELETE s FROM stats_pypi s LEFT JOIN packages_pypi p ON s.project = p.project WHERE p.project IS NULL;');

        Schema::table('stats_pypi', function (Blueprint $table) {
            $table->foreign('project')->references('project')->on('packages_pypi')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stats_pypi', function (Blueprint $table) {
            $table->dropForeign('stats_pypi_project_foreign');
        });
    }
}
