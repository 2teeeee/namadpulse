<?php

namespace App\Services\Import;

use Illuminate\Support\Carbon;
use Morilog\Jalali\Jalalian;
use RuntimeException;

class DailyPriceFileParser
{
    /**
     * ساختار هر سطر فایل:
     * <DTYYYYMMDD>,<TIME>,<OPEN>,<HIGH>,<LOW>,<CLOSE>,<VOL>,<MarketValue>,<ShareCount>
     *
     * @return array{rows: array<int, array<string, mixed>>, last_share_count: ?int}
     */
    public function parse(string $filePath): array
    {
        if (! is_readable($filePath)) {
            throw new RuntimeException("فایل قابل خواندن نیست: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        $rows = [];
        $lastShareCount = null;

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);

            if ($line === '' || ! str_starts_with($line, '2')) {
                // خط خالی یا هدر احتمالی (که با <DTYYYYMMDD> شروع می‌شه) رد می‌شود
                continue;
            }

            $columns = explode(',', $line);

            if (count($columns) < 9) {
                continue; // سطر ناقص/خراب
            }

            [$dateRaw, , $open, $high, $low, $close, $volume, , $shareCount] = $columns;

            $tradeDate = Carbon::createFromFormat('Ymd', $dateRaw)->startOfDay();
            $jalaliDate = Jalalian::fromCarbon($tradeDate)->format('Y/m/d');
            $closePrice = (float) $close;
            $volumeInt = (int) $volume;

            $rows[] = [
                'trade_date' => $tradeDate->toDateString(),
                'jalali_date' => $jalaliDate,
                'open' => (float) $open,
                'high' => (float) $high,
                'low' => (float) $low,
                'close' => $closePrice,
                'final' => $closePrice, // فایل فقط یک قیمت دارد؛ به‌عنوان پایانی هم استفاده می‌شود
                'volume' => $volumeInt,
                'value' => (int) round($closePrice * $volumeInt), // تقریبی: ارزش معاملات واقعی در فایل نیست
                'trades_count' => 0, // در فایل موجود نیست
                'is_adjusted' => true,
            ];

            $lastShareCount = (int) $shareCount;
        }

        fclose($handle);

        return ['rows' => $rows, 'last_share_count' => $lastShareCount];
    }
}
