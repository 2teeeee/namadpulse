<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_runs', function (Blueprint $table) {
            $table->id();

            $table->string('type')->default('daily_prices'); // برای گسترش آینده به import های دیگر
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');

            $table->string('ticker_filter')->nullable(); // اگر فقط یک نماد اجرا شده باشد
            $table->boolean('with_indicators')->default(false);

            $table->unsignedInteger('total_symbols')->nullable();
            $table->unsignedInteger('processed_symbols')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);

            $table->json('log')->nullable(); // آخرین پیام‌های رد‌شده/خطا (حداکثر ۵۰ مورد)

            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_runs');
    }
};
