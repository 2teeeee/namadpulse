<?php

namespace App\Http\Controllers;

use App\Models\AlertRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertRuleController extends Controller
{
    public function index(Request $request): View
    {
        $alertRules = $request->user()->alertRules()->with('symbol')->latest()->paginate(20);

        return view('alert-rules.index', compact('alertRules'));
    }

    public function create(): View
    {
        return view('alert-rules.create');
    }

    public function store(Request $request): RedirectResponse
    {
        // TODO: قوانین validation دقیق بر اساس type (price_above/percent_change/...) در فاز منطق آلرت تکمیل شود
        $validated = $request->validate([
            'symbol_id' => ['required', 'exists:symbols,id'],
            'type' => ['required', 'in:price_above,price_below,percent_change_up,percent_change_down,volume_spike'],
            'condition_value' => ['required', 'numeric'],
            'notify_via' => ['required', 'array', 'min:1'],
            'notify_via.*' => ['in:telegram,sms,web'],
            'cooldown_minutes' => ['nullable', 'integer', 'min:5'],
        ]);

        $request->user()->alertRules()->create($validated);

        return redirect()->route('alert-rules.index')->with('success', 'قانون آلرت ایجاد شد.');
    }

    public function update(Request $request, AlertRule $alertRule): RedirectResponse
    {
        abort_unless($alertRule->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:active,disabled'],
        ]);

        $alertRule->update($validated);

        return redirect()->route('alert-rules.index')->with('success', 'وضعیت قانون به‌روزرسانی شد.');
    }

    public function destroy(AlertRule $alertRule): RedirectResponse
    {
        abort_unless($alertRule->user_id === auth()->id(), 403);

        $alertRule->delete();

        return redirect()->route('alert-rules.index')->with('success', 'قانون آلرت حذف شد.');
    }
}
