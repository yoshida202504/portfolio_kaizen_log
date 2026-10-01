<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_records', function (Blueprint $table): void {
            $table->index(
                ['user_id', 'deleted_at', 'record_date', 'created_at'],
                'daily_records_owner_listing_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('daily_records', function (Blueprint $table): void {
            $table->dropIndex('daily_records_owner_listing_index');
        });
    }
};
