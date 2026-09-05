<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // یک ردیف به ازای هر نماد؛ بعد از هر بار import قیمت روزانه بازمحاسبه و overwrite می‌شود
        Schema::create('symbol_technical_snapshots', function (Blueprint $table) {
            $table->foreignId('symbol_id')
                ->primary()
                ->constrained('symbols')
                ->cascadeOnDelete();

            $table->decimal('price_ma_5', 15, 2)->nullable();
            $table->decimal('price_ma_20', 15, 2)->nullable();
            $table->decimal('price_ma_60', 15, 2)->nullable();

            $table->unsignedBigInteger('volume_ma_5')->nullable();
            $table->unsignedBigInteger('volume_ma_20')->nullable();
            $table->unsignedBigInteger('volume_ma_60')->nullable();

            $table->decimal('ema_100', 15, 2)->nullable();
            $table->decimal('ema_200', 15, 2)->nullable();

            $table->timestamp('computed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symbol_technical_snapshots');
    }
};
