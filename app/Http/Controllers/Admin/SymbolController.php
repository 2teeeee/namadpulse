<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Symbol;
use App\Models\SymbolGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SymbolController extends Controller
{
    public function index(Request $request): View
    {
        $symbols = Symbol::with('group')
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('ticker', 'like', "%{$request->q}%")
                        ->orWhere('name', 'like', "%{$request->q}%");
                });
            })
            ->when($request->filled('symbol_group_id'), function ($query) use ($request) {
                $query->where('symbol_group_id', $request->symbol_group_id);
            })
            ->orderBy('ticker')
            ->paginate(20)
            ->withQueryString();

        $symbolGroups = SymbolGroup::orderBy('name')->get();

        return view('admin.symbols.index', compact('symbols', 'symbolGroups'));
    }

    public function create(): View
    {
        $symbolGroups = SymbolGroup::where('is_active', true)->orderBy('name')->get();

        return view('admin.symbols.create', compact('symbolGroups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        Symbol::create($validated);

        return redirect()->route('admin.symbols.index')->with('success', 'نماد جدید ایجاد شد.');
    }

    public function edit(Symbol $symbol): View
    {
        $symbolGroups = SymbolGroup::where('is_active', true)->orderBy('name')->get();

        return view('admin.symbols.edit', compact('symbol', 'symbolGroups'));
    }

    public function update(Request $request, Symbol $symbol): RedirectResponse
    {
        $validated = $this->validated($request, $symbol);

        $symbol->update($validated);

        return redirect()->route('admin.symbols.index')->with('success', 'اطلاعات نماد به‌روزرسانی شد.');
    }

    public function destroy(Symbol $symbol): RedirectResponse
    {
        $symbol->delete();

        return redirect()->route('admin.symbols.index')->with('success', 'نماد حذف شد.');
    }

    private function validated(Request $request, ?Symbol $symbol = null): array
    {
        return $request->validate([
            'symbol_group_id' => ['required', 'exists:symbol_groups,id'],
            'ticker' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'isin_code' => ['nullable', 'string', 'max:20', 'unique:symbols,isin_code' . ($symbol ? ",{$symbol->id}" : '')],
            'tsetmc_id' => ['nullable', 'string', 'max:30', 'unique:symbols,tsetmc_id' . ($symbol ? ",{$symbol->id}" : '')],
            'board' => ['required', 'in:bourse,farabourse,other'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
