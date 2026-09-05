<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertLogController extends Controller
{
    public function index(Request $request): View
    {
        $alertLogs = \App\Models\AlertLog::whereHas('alertRule', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->with('alertRule.symbol')
            ->latest('sent_at')
            ->paginate(30);

        return view('alert-logs.index', compact('alertLogs'));
    }
}
