<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('symbol_id')
                ->constrained('symbols')
                ->cascadeOnDelete();

            $table->enum('type', [
                'price_above',        // قیمت بالاتر از مقدار مشخص
                'price_below',        // قیمت پایین‌تر از مقدار مشخص
                'percent_change_up',  // درصد رشد نسبت به قیمت پایانی دیروز
                'percent_change_down',// درصد افت نسبت به قیمت پایانی دیروز
                'volume_spike',       // جهش حجم معاملات
            ]);
            $table->decimal('condition_value', 15, 2); // مقدار قیمت یا درصد یا ضریب حجم

            $table->enum('status', ['active', 'triggered', 'disabled'])->default('active');

            // کانال‌های انتخابی برای این قانون خاص، مثلاً ["telegram","sms"]
            $table->json('notify_via');

            // جلوگیری از اسپم آلرت تکراری برای یک شرط برقرارشده
            $table->unsignedSmallInteger('cooldown_minutes')->default(60);

            $table->timestamp('triggered_at')->nullable();

            $table->timestamps();

            $table->index(['symbol_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_rules');
    }
};
