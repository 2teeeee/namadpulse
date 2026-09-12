<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportDailyPricesJob;
use App\Models\ImportRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function index(): JsonResponse
    {
        $runs = ImportRun::with('triggeredBy')->latest('started_at')->paginate(15);

        $latestRunning = ImportRun::whereIn('status', ['pending', 'running'])->latest('started_at')->first();

        return response()->json([
            'data' => collect($runs->items())->map($this->transform(...)),
            'meta' => [
                'current_page' => $runs->currentPage(),
                'last_page' => $runs->lastPage(),
                'total' => $runs->total(),
            ],
            'latest_run' => $latestRunning ? $this->transform($latestRunning) : null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ticker' => ['nullable', 'string'],
            'with_indicators' => ['nullable', 'boolean'],
        ]);

        $run = ImportRun::create([
            'type' => 'daily_prices',
            'status' => 'pending',
            'ticker_filter' => $validated['ticker'] ?? null,
            'with_indicators' => $validated['with_indicators'] ?? true,
            'triggered_by' => $request->user()->id,
            'started_at' => now(),
        ]);

        ImportDailyPricesJob::dispatch($run->id);

        return response()->json(['data' => $this->transform($run)], 201);
    }

    public function status(ImportRun $importRun): JsonResponse
    {
        return response()->json(['data' => $this->transform($importRun)]);
    }

    private function transform(ImportRun $run): array
    {
        return [
            'id' => $run->id,
            'status' => $run->status,
            'ticker_filter' => $run->ticker_filter,
            'total_symbols' => $run->total_symbols,
            'processed_symbols' => $run->processed_symbols,
            'success_count' => $run->success_count,
            'skipped_count' => $run->skipped_count,
            'progress_percent' => $run->progressPercent(),
            'is_finished' => $run->isFinished(),
            'triggered_by' => $run->triggeredBy?->name,
            'started_at' => $run->started_at,
            'finished_at' => $run->finished_at,
        ];
    }
}
