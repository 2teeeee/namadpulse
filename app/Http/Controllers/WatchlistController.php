<?php

namespace App\Http\Controllers;

use App\Models\Watchlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WatchlistController extends Controller
{
    public function index(Request $request): View
    {
        $watchlists = $request->user()->watchlists()->withCount('symbols')->get();

        return view('watchlists.index', compact('watchlists'));
    }

    public function create(): View
    {
        return view('watchlists.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->watchlists()->create($validated);

        return redirect()->route('watchlists.index')->with('success', 'واچ‌لیست جدید ایجاد شد.');
    }

    public function show(Watchlist $watchlist): View
    {
        $this->authorizeOwnership($watchlist);

        $watchlist->load('symbols.livePrice');

        return view('watchlists.show', compact('watchlist'));
    }

    public function edit(Watchlist $watchlist): View
    {
        $this->authorizeOwnership($watchlist);

        return view('watchlists.edit', compact('watchlist'));
    }

    public function update(Request $request, Watchlist $watchlist): RedirectResponse
    {
        $this->authorizeOwnership($watchlist);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $watchlist->update($validated);

        return redirect()->route('watchlists.index')->with('success', 'واچ‌لیست به‌روزرسانی شد.');
    }

    public function destroy(Watchlist $watchlist): RedirectResponse
    {
        $this->authorizeOwnership($watchlist);

        $watchlist->delete();

        return redirect()->route('watchlists.index')->with('success', 'واچ‌لیست حذف شد.');
    }

    private function authorizeOwnership(Watchlist $watchlist): void
    {
        abort_unless($watchlist->user_id === auth()->id(), 403);
    }

    public function attachMany(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'watchlist_id' => ['required', 'exists:watchlists,id'],
            'symbol_ids' => ['required', 'array', 'min:1'],
            'symbol_ids.*' => ['exists:symbols,id'],
        ]);

        $watchlist = Watchlist::findOrFail($validated['watchlist_id']);
        $this->authorizeOwnership($watchlist);

        $watchlist->symbols()->syncWithoutDetaching($validated['symbol_ids']);

        return back()->with('success', count($validated['symbol_ids']) . ' نماد به واچ‌لیست «' . $watchlist->name . '» اضافه شد.');
    }
}
