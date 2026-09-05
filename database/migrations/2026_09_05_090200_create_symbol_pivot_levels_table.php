<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('symbol_pivot_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('symbol_id')
                ->constrained('symbols')
                ->cascadeOnDelete();

            $table->enum('period_type', ['month', 'quarter', 'year']); // ماه/فصل/سال قبل
            $table->string('period_label', 20)->nullable(); // برچسب نمایشی، مثلاً "1404/05"

            $table->decimal('high', 15, 2);
            $table->decimal('low', 15, 2);
            $table->decimal('close', 15, 2); // آخرین قیمت پایانی همان دوره

            $table->decimal('pp', 15, 2);
            $table->decimal('r1', 15, 2);
            $table->decimal('r2', 15, 2);
            $table->decimal('r3', 15, 2);
            $table->decimal('s1', 15, 2);
            $table->decimal('s2', 15, 2);
            $table->decimal('s3', 15, 2);

            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            // فقط آخرین دوره‌ی ماه/فصل/سال قبل برای هر نماد نگه داشته می‌شود -> upsert امن
            $table->unique(['symbol_id', 'period_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symbol_pivot_levels');
    }
};
