<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SymbolGroup;
use Illuminate\Http\JsonResponse;

class SymbolGroupController extends Controller
{
    public function index(): JsonResponse
    {
        $groups = SymbolGroup::orderBy('name')->get(['id', 'name']);

        return response()->json(['data' => $groups]);
    }
}
