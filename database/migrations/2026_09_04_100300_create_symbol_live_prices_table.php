<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // یک ردیف به ازای هر نماد؛ هر بار fetch لحظه‌ای با upsert بازنویسی می‌شود.
        // عمداً از symbol_id به عنوان کلید اصلی استفاده شده تا upsert سریع و بدون جست‌وجوی اضافه باشد.
        Schema::create('symbol_live_prices', function (Blueprint $table) {
            $table->foreignId('symbol_id')
                ->primary()
                ->constrained('symbols')
                ->cascadeOnDelete();

            $table->decimal('last_price', 15, 2)->nullable();
            $table->decimal('close_price', 15, 2)->nullable();   // قیمت پایانی لحظه‌ای
            $table->decimal('change_percent', 6, 2)->nullable();

            $table->unsignedBigInteger('volume')->nullable();
            $table->decimal('best_buy_price', 15, 2)->nullable();
            $table->decimal('best_sell_price', 15, 2)->nullable();

            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symbol_live_prices');
    }
};
