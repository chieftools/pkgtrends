<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscribersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscribers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email');
            $table->uuid('report_id');
            $table->foreign('report_id')->references('id')->on('reports');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();

            $table->unique(['email', 'report_id']);
            $table->index(['confirmed_at', 'created_at']);
            $table->index(['report_id', 'confirmed_at', 'last_notified_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscribers');
    }
}
