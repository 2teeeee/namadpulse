<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('symbol_zigzag_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('symbol_id')
                ->constrained('symbols')
                ->cascadeOnDelete();

            $table->date('point_date');
            $table->string('jalali_date', 10);
            $table->decimal('price', 15, 2);
            $table->enum('type', ['peak', 'trough']); // سقف / کف

            $table->timestamps();

            // در هر بازمحاسبه، نقاط قبلی این نماد حذف و دوباره درج می‌شوند
            $table->unique(['symbol_id', 'point_date']);
            $table->index(['symbol_id', 'point_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symbol_zigzag_points');
    }
};
