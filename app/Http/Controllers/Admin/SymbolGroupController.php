<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SymbolGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SymbolGroupController extends Controller
{
    public function index(): View
    {
        $symbolGroups = SymbolGroup::withCount('symbols')->orderBy('name')->paginate(20);

        return view('admin.symbol-groups.index', compact('symbolGroups'));
    }

    public function create(): View
    {
        return view('admin.symbol-groups.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:symbol_groups,code'],
            'parent_id' => ['nullable', 'exists:symbol_groups,id'],
        ]);

        SymbolGroup::create($validated);

        return redirect()->route('admin.symbol-groups.index')->with('success', 'گروه نماد جدید ایجاد شد.');
    }

    public function update(Request $request, SymbolGroup $symbolGroup): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        $symbolGroup->update($validated);

        return redirect()->route('admin.symbol-groups.index')->with('success', 'گروه نماد به‌روزرسانی شد.');
    }

    public function destroy(SymbolGroup $symbolGroup): RedirectResponse
    {
        $symbolGroup->delete();

        return redirect()->route('admin.symbol-groups.index')->with('success', 'گروه نماد حذف شد.');
    }
}
