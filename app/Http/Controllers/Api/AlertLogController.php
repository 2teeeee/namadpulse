<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertLogResource;
use App\Models\AlertLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $alertLogs = AlertLog::whereHas('alertRule', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with('alertRule.symbol')
            ->latest('sent_at')
            ->paginate(30);

        return response()->json([
            'data' => AlertLogResource::collection($alertLogs->items()),
            'meta' => [
                'current_page' => $alertLogs->currentPage(),
                'last_page' => $alertLogs->lastPage(),
                'total' => $alertLogs->total(),
            ],
        ]);
    }
}
