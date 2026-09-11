<?php

namespace App\Services\Import;

use App\Models\Symbol;
use Illuminate\Support\Facades\DB;

class DailyPriceImporter
{
    public function __construct(
        private readonly DailyPriceFileParser $parser,
    ) {
    }

    /**
     * @return array{status: string, imported_rows: int, message: ?string}
     */
    public function importForSymbol(Symbol $symbol): array
    {
        if (empty($symbol->name_en)) {
            return ['status' => 'skipped', 'imported_rows' => 0, 'message' => 'نماد فیلد name_en ندارد.'];
        }

        $filePath = $this->resolveFilePath($symbol->name_en);

        if (! file_exists($filePath)) {
            return ['status' => 'skipped', 'imported_rows' => 0, 'message' => "فایل یافت نشد: {$filePath}"];
        }

        $parsed = $this->parser->parse($filePath);

        if (empty($parsed['rows'])) {
            return ['status' => 'skipped', 'imported_rows' => 0, 'message' => 'فایل خالی یا بدون داده معتبر بود.'];
        }

        $this->upsertRows($symbol, $parsed['rows']);
        $this->updateSharesCount($symbol, $parsed['last_share_count']);

        return ['status' => 'ok', 'imported_rows' => count($parsed['rows']), 'message' => null];
    }

    private function resolveFilePath(string $nameEn): string
    {
        $basePath = rtrim((string) config('imports.daily_prices_path'), '/\\');

        return $basePath . DIRECTORY_SEPARATOR . $nameEn . '.txt';
    }

    private function upsertRows(Symbol $symbol, array $rows): void
    {
        $chunkSize = (int) config('imports.import_chunk_size', 500);

        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            $chunk = array_map(function (array $row) use ($symbol) {
                $row['symbol_id'] = $symbol->id;
                $row['created_at'] = now();
                $row['updated_at'] = now();

                return $row;
            }, $chunk);

            DB::table('symbol_daily_prices')->upsert(
                $chunk,
                ['symbol_id', 'trade_date'],
                ['jalali_date', 'open', 'high', 'low', 'close', 'final', 'volume', 'value', 'trades_count', 'is_adjusted', 'updated_at']
            );
        }
    }

    private function updateSharesCount(Symbol $symbol, ?int $shareCount): void
    {
        if ($shareCount && $shareCount !== $symbol->shares_count) {
            $symbol->update(['shares_count' => $shareCount]);
        }
    }
}
