<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('symbol_daily_prices', function (Blueprint $table) {
            // جدول حجیم (۵ میلیون+ رکورد) -> id عددی ساده برای سبک ماندن ایندکس
            $table->id();
            $table->foreignId('symbol_id')
                ->constrained('symbols')
                ->cascadeOnDelete();

            $table->date('trade_date');          // تاریخ میلادی، برای ایندکس و مرتب‌سازی سریع
            $table->string('jalali_date', 10);   // معادل شمسی، فقط برای نمایش (مثلاً 1403/05/12)

            $table->decimal('open', 15, 2);
            $table->decimal('high', 15, 2);
            $table->decimal('low', 15, 2);
            $table->decimal('close', 15, 2);     // آخرین قیمت معامله
            $table->decimal('final', 15, 2);     // قیمت پایانی

            $table->unsignedBigInteger('volume')->default(0);
            $table->unsignedBigInteger('value')->default(0);      // ارزش معاملات (ریال)
            $table->unsignedInteger('trades_count')->default(0);

            $table->boolean('is_adjusted')->default(true); // تعدیل‌شده بابت افزایش سرمایه/سود نقدی

            $table->timestamps();

            // هر نماد فقط یک رکورد به ازای هر تاریخ -> پایه‌ی upsert امن برای بازخوانی مجدد
            $table->unique(['symbol_id', 'trade_date']);
            $table->index('trade_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symbol_daily_prices');
    }
};
