<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRun extends Model
{
    protected $fillable = [
        'type',
        'status',
        'ticker_filter',
        'with_indicators',
        'total_symbols',
        'processed_symbols',
        'success_count',
        'skipped_count',
        'log',
        'triggered_by',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'with_indicators' => 'boolean',
        'log' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function progressPercent(): int
    {
        if (! $this->total_symbols) {
            return 0;
        }

        return (int) round(($this->processed_symbols / $this->total_symbols) * 100);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['completed', 'failed'], true);
    }

    /** افزودن پیام به لاگ با محدودیت ۵۰ مورد آخر */
    public function appendLog(string $message): void
    {
        $log = $this->log ?? [];
        $log[] = ['at' => now()->toDateTimeString(), 'message' => $message];

        $this->log = array_slice($log, -50);
    }
}
