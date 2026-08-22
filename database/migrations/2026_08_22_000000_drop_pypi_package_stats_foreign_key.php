<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stats_pypi', function (Blueprint $table) {
            $table->dropForeign('stats_pypi_project_foreign');
        });
    }
};
