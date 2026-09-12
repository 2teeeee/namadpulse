<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SymbolResource;
use App\Http\Resources\WatchlistResource;
use App\Models\Watchlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $watchlists = $request->user()->watchlists()->withCount('symbols')->get();

        return response()->json(['data' => WatchlistResource::collection($watchlists)]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => ['required', 'string', 'max:255']]);

        $watchlist = $request->user()->watchlists()->create($validated);

        return response()->json(['data' => new WatchlistResource($watchlist)], 201);
    }

    public function show(Request $request, Watchlist $watchlist): JsonResponse
    {
        $this->authorizeOwnership($request, $watchlist);

        $watchlist->load('symbols.livePrice', 'symbols.group');

        return response()->json([
            'data' => [
                'id' => $watchlist->id,
                'name' => $watchlist->name,
                'symbols' => SymbolResource::collection($watchlist->symbols),
            ],
        ]);
    }

    public function update(Request $request, Watchlist $watchlist): JsonResponse
    {
        $this->authorizeOwnership($request, $watchlist);

        $validated = $request->validate(['name' => ['required', 'string', 'max:255']]);

        $watchlist->update($validated);

        return response()->json(['data' => new WatchlistResource($watchlist)]);
    }

    public function destroy(Request $request, Watchlist $watchlist): JsonResponse
    {
        $this->authorizeOwnership($request, $watchlist);

        $watchlist->delete();

        return response()->json(['message' => 'واچ‌لیست حذف شد.']);
    }

    public function attachSymbol(Request $request, Watchlist $watchlist): JsonResponse
    {
        $this->authorizeOwnership($request, $watchlist);

        $validated = $request->validate(['symbol_id' => ['required', 'exists:symbols,id']]);

        $watchlist->symbols()->syncWithoutDetaching([$validated['symbol_id']]);

        return response()->json(['message' => 'نماد اضافه شد.']);
    }

    public function detachSymbol(Request $request, Watchlist $watchlist, int $symbolId): JsonResponse
    {
        $this->authorizeOwnership($request, $watchlist);

        $watchlist->symbols()->detach($symbolId);

        return response()->json(['message' => 'نماد حذف شد.']);
    }

    private function authorizeOwnership(Request $request, Watchlist $watchlist): void
    {
        abort_unless($watchlist->user_id === $request->user()->id, 403);
    }
}
