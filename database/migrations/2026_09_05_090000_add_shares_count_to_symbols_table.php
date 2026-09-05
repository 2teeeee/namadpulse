<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('symbols', function (Blueprint $table) {
            // تعداد کل سهام منتشرشده؛ مبنای محاسبه‌ی سرمایه (× ۱۰۰۰ ریال ارزش اسمی) و ارزش بازار (× آخرین قیمت)
            $table->unsignedBigInteger('shares_count')->nullable()->after('board');
        });
    }

    public function down(): void
    {
        Schema::table('symbols', function (Blueprint $table) {
            $table->dropColumn('shares_count');
        });
    }
};
