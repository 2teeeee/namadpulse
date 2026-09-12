<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertRuleResource;
use App\Models\AlertRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertRuleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $alertRules = $request->user()->alertRules()->with('symbol')->latest()->paginate(20);

        return response()->json([
            'data' => AlertRuleResource::collection($alertRules->items()),
            'meta' => [
                'current_page' => $alertRules->currentPage(),
                'last_page' => $alertRules->lastPage(),
                'total' => $alertRules->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'symbol_id' => ['required', 'exists:symbols,id'],
            'type' => ['required', 'in:price_above,price_below,percent_change_up,percent_change_down,volume_spike'],
            'condition_value' => ['required', 'numeric'],
            'notify_via' => ['required', 'array', 'min:1'],
            'notify_via.*' => ['in:telegram,sms,web'],
            'cooldown_minutes' => ['nullable', 'integer', 'min:5'],
        ]);

        $alertRule = $request->user()->alertRules()->create($validated);

        return response()->json(['data' => new AlertRuleResource($alertRule->load('symbol'))], 201);
    }

    public function update(Request $request, AlertRule $alertRule): JsonResponse
    {
        abort_unless($alertRule->user_id === $request->user()->id, 403);

        $validated = $request->validate(['status' => ['required', 'in:active,disabled']]);

        $alertRule->update($validated);

        return response()->json(['data' => new AlertRuleResource($alertRule->load('symbol'))]);
    }

    public function destroy(Request $request, AlertRule $alertRule): JsonResponse
    {
        abort_unless($alertRule->user_id === $request->user()->id, 403);

        $alertRule->delete();

        return response()->json(['message' => 'قانون آلرت حذف شد.']);
    }
}
