<?php

namespace App\Http\Controllers;

use App\Models\Symbol;
use Illuminate\View\View;

class SymbolController extends Controller
{
    public function index(): View
    {
        $symbols = Symbol::with('group', 'livePrice')
            ->where('is_active', true)
            ->orderBy('ticker')
            ->paginate(20);

        return view('symbols.index', compact('symbols'));
    }

    public function show(Symbol $symbol): View
    {
        $symbol->load('group', 'livePrice', 'latestDailyPrice');

        return view('symbols.show', compact('symbol'));
    }
}
