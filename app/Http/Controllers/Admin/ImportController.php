<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportDailyPricesJob;
use App\Models\ImportRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function index(): View
    {
        $latestRun = ImportRun::latest()->first();
        $recentRuns = ImportRun::latest()->paginate(10);

        return view('admin.imports.index', compact('latestRun', 'recentRuns'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ticker' => ['nullable', 'string', 'max:50'],
            'with_indicators' => ['nullable', 'boolean'],
        ]);

        $run = ImportRun::create([
            'type' => 'daily_prices',
            'status' => 'pending',
            'ticker_filter' => $validated['ticker'] ?? null,
            'with_indicators' => $request->boolean('with_indicators'),
            'triggered_by' => $request->user()->id,
        ]);

        ImportDailyPricesJob::dispatch($run->id);

        return redirect()->route('admin.imports.index')
            ->with('success', 'درخواست import ثبت شد و در صف اجرا قرار گرفت.');
    }

    public function status(ImportRun $importRun): JsonResponse
    {
        return response()->json([
            'status' => $importRun->status,
            'total_symbols' => $importRun->total_symbols,
            'processed_symbols' => $importRun->processed_symbols,
            'success_count' => $importRun->success_count,
            'skipped_count' => $importRun->skipped_count,
            'progress_percent' => $importRun->progressPercent(),
            'is_finished' => $importRun->isFinished(),
            'log' => array_slice($importRun->log ?? [], -10),
        ]);
    }
}
