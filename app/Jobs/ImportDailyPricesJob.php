<?php

namespace App\Jobs;

use App\Models\ImportRun;
use App\Models\Symbol;
use App\Services\Import\DailyPriceImporter;
use App\Services\Indicators\IndicatorCalculationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ImportDailyPricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // فایل‌های حجیم روی همه‌ی نمادها ممکنه چند دقیقه طول بکشه
    public int $timeout = 1800;

    public function __construct(
        private readonly int $importRunId,
    ) {
    }

    public function handle(DailyPriceImporter $importer, IndicatorCalculationService $indicatorService): void
    {
        $run = ImportRun::findOrFail($this->importRunId);

        $symbols = $run->ticker_filter
            ? Symbol::where('ticker', $run->ticker_filter)->get()
            : Symbol::where('is_active', true)->get();

        $run->update([
            'status' => 'running',
            'total_symbols' => $symbols->count(),
            'started_at' => now(),
        ]);

        if ($symbols->isEmpty()) {
            $run->appendLog('هیچ نمادی مطابق فیلتر یافت نشد.');
            $run->update(['status' => 'failed', 'finished_at' => now(), 'log' => $run->log]);

            return;
        }

        try {
            foreach ($symbols as $symbol) {
                $this->importOne($run, $symbol, $importer, $indicatorService);
            }

            $run->update(['status' => 'completed', 'finished_at' => now()]);
        } catch (Throwable $exception) {
            $run->appendLog('خطای غیرمنتظره: ' . $exception->getMessage());
            $run->update(['status' => 'failed', 'finished_at' => now(), 'log' => $run->log]);

            throw $exception;
        }
    }

    private function importOne(
        ImportRun $run,
        Symbol $symbol,
        DailyPriceImporter $importer,
        IndicatorCalculationService $indicatorService,
    ): void {
        $result = $importer->importForSymbol($symbol);

        if ($result['status'] === 'ok') {
            $run->increment('success_count');

            if ($run->with_indicators) {
                $indicatorService->calculateFor($symbol);
            }
        } else {
            $run->increment('skipped_count');
            $run->appendLog("[{$symbol->ticker}] {$result['message']}");
        }

        $run->increment('processed_symbols');
        $run->save(); // برای ذخیره‌ی log که با appendLog فقط در حافظه تغییر کرده
    }
}
