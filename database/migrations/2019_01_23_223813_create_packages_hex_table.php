<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackagesHexTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages_hex', function (Blueprint $table) {
            $table->string('name')->primary();
            $table->text('description')->nullable();

            $table->timestamps();
        });

        DB::statement('ALTER TABLE packages_hex ADD FULLTEXT name (name);');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('packages_hex');
    }
}
