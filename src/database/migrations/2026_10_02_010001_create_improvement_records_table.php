<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('improvement_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('daily_record_id')->constrained()->cascadeOnDelete();
            $table->string('execution_status', 20);
            $table->string('result_evaluation', 1)->nullable();
            $table->date('executed_at')->nullable();
            $table->text('actual_result')->nullable();
            $table->string('not_executed_reason', 20)->nullable();
            $table->text('not_executed_note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('daily_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('improvement_records');
    }
};
