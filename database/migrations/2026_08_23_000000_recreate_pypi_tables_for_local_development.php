<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (!app()->environment('local')) {
            return;
        }

        $this->dropPyPITables();
        $this->createCurrentPyPITables();
    }

    public function down(): void
    {
        if (!app()->environment('local')) {
            return;
        }

        $this->dropPyPITables();
        $this->createLegacyPyPITables();
    }

    private function dropPyPITables(): void
    {
        Schema::dropIfExists('stats_pypi');
        Schema::dropIfExists('packages_pypi');
    }

    private function createCurrentPyPITables(): void
    {
        Schema::create('packages_pypi', function (Blueprint $table) {
            $table->increments('id');
            $table->string('project')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE packages_pypi ADD FULLTEXT project (project)');

        Schema::create('stats_pypi', function (Blueprint $table) {
            $table->unsignedInteger('package_id');
            $table->date('date');
            $table->unsignedInteger('downloads');
            $table->primary(['package_id', 'date']);
        });

        DB::statement(sprintf(
            "ALTER TABLE stats_pypi PARTITION BY RANGE COLUMNS (`date`) (\n%s\n)",
            $this->monthlyPartitions(),
        ));
    }

    private function createLegacyPyPITables(): void
    {
        Schema::create('packages_pypi', function (Blueprint $table) {
            $table->string('project')->primary();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE packages_pypi ADD FULLTEXT project (project)');

        Schema::create('stats_pypi', function (Blueprint $table) {
            $table->date('date');
            $table->string('project');
            $table->unsignedInteger('downloads');
            $table->unique(['date', 'project']);
            $table->index('date');
            $table->index('project');
        });
    }

    private function monthlyPartitions(): string
    {
        $month      = now()->startOfMonth()->subMonthsNoOverflow(13);
        $lastMonth  = now()->startOfMonth()->addMonthsNoOverflow(2);
        $partitions = [];

        while ($month->lessThanOrEqualTo($lastMonth)) {
            $upperBound   = $month->copy()->addMonthNoOverflow();
            $partitions[] = sprintf(
                "    PARTITION p%s VALUES LESS THAN ('%s')",
                $month->format('Ym'),
                $upperBound->format('Y-m-d'),
            );
            $month        = $upperBound;
        }

        $partitions[] = '    PARTITION p_future VALUES LESS THAN (MAXVALUE)';

        return implode(",\n", $partitions);
    }
};
