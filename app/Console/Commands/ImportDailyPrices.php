<?php

namespace App\Console\Commands;

use App\Models\Symbol;
use App\Services\Import\DailyPriceImporter;
use App\Services\Indicators\IndicatorCalculationService;
use Illuminate\Console\Command;

class ImportDailyPrices extends Command
{
    protected $signature = 'prices:import-daily
        {ticker? : نماد مشخص برای import (خالی = همه نمادهای فعال)}
        {--with-indicators : بعد از import، اندیکاتورهای همان نماد هم بازمحاسبه شود}';

    protected $description = 'Import تاریخچه قیمت روزانه تعدیل‌شده از فایل‌های txt';

    public function handle(DailyPriceImporter $importer, IndicatorCalculationService $indicatorService): int
    {
        $symbols = $this->argument('ticker')
            ? Symbol::where('ticker', $this->argument('ticker'))->get()
            : Symbol::where('is_active', true)->get();

        if ($symbols->isEmpty()) {
            $this->error('نمادی برای import یافت نشد.');

            return self::FAILURE;
        }

        $withIndicators = (bool) $this->option('with-indicators');

        $successCount = 0;
        $skippedCount = 0;

        $bar = $this->output->createProgressBar($symbols->count());
        $bar->start();

        foreach ($symbols as $symbol) {
            $result = $importer->importForSymbol($symbol);

            if ($result['status'] === 'ok') {
                $successCount++;

                if ($withIndicators) {
                    $indicatorService->calculateFor($symbol);
                }
            } else {
                $skippedCount++;
                $this->newLine();
                $this->warn("[{$symbol->ticker}] رد شد: {$result['message']}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("import کامل شد — موفق: {$successCount}، رد‌شده: {$skippedCount}");

        return self::SUCCESS;
    }
}
