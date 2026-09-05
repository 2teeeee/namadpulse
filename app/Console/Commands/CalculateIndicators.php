<?php

namespace App\Console\Commands;

use App\Models\Symbol;
use App\Services\Indicators\IndicatorCalculationService;
use Illuminate\Console\Command;

class CalculateIndicators extends Command
{
    protected $signature = 'indicators:calculate {ticker? : نماد مشخص برای محاسبه (خالی = همه نمادهای فعال)}';

    protected $description = 'محاسبه‌ی میانگین‌های متحرک، پیووت پوینت‌ها و نقاط زیگزاگ برای نمادها';

    public function handle(IndicatorCalculationService $service): int
    {
        $symbols = $this->argument('ticker')
            ? Symbol::where('ticker', $this->argument('ticker'))->get()
            : Symbol::where('is_active', true)->get();

        if ($symbols->isEmpty()) {
            $this->error('نمادی برای محاسبه یافت نشد.');

            return self::FAILURE;
        }

        $bar = $this->output->createProgressBar($symbols->count());
        $bar->start();

        foreach ($symbols as $symbol) {
            $service->calculateFor($symbol);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("اندیکاتورهای {$symbols->count()} نماد محاسبه و ذخیره شد.");

        return self::SUCCESS;
    }
}
