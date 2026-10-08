<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_records', function (Blueprint $table): void {
            $table->text('expected_result')->nullable()->after('improvement_strategy');
        });
    }

    public function down(): void
    {
        Schema::table('daily_records', function (Blueprint $table): void {
            $table->dropColumn('expected_result');
        });
    }
};
