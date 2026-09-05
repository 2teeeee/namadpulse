<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('symbols', function (Blueprint $table) {
            $table->id();
            $table->foreignId('symbol_group_id')
                ->constrained('symbol_groups')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // شناسه‌های مرجع بازار
            $table->string('isin_code')->nullable()->unique();   // کد ۱۲ رقمی ISIN
            $table->string('tsetmc_id')->nullable()->unique();   // شناسه عددی سایت tsetmc (برای brsapi/tsetmc)

            $table->string('ticker');           // نماد لاتین/کوتاه، مثلاً فولاد
            $table->string('name');             // نام فارسی کامل
            $table->string('name_en')->nullable();

            $table->enum('board', ['bourse', 'farabourse', 'other'])->default('bourse');
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('ticker');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symbols');
    }
};
