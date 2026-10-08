<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->date('record_date');

            $table->text('actions');
            $table->text('good_points');
            $table->text('improvement_points');
            $table->text('improvement_strategy');

            $table->text('improvement_result')->nullable();
            $table->unsignedTinyInteger('improvement_rate')->nullable();

            $table->boolean('is_public')->default(false);

            $table->string('image_path')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_records');
    }
};
