@extends('layouts.app')

@php
    $periodOptions = ['year' => 'سالانه', 'quarter' => 'فصلی', 'month' => 'ماهانه'];
    $pivotLabels = ['pp' => 'PP', 'r1' => 'R1', 'r2' => 'R2', 'r3' => 'R3', 's1' => 'S1', 's2' => 'S2', 's3' => 'S3'];
    $averageLabels = ['ma_20' => 'میانگین ۲۰ روزه', 'ema_100' => 'میانگین نمایی ۱۰۰ روزه', 'ema_200' => 'میانگین نمایی ۲۰۰ روزه'];
    $crossLabels = ['5_20' => '۵ به ۲۰', '5_60' => '۵ به ۶۰', '20_60' => '۲۰ به ۶۰'];

    $threeStateOptions = ['any' => 'بی‌تفاوت', 'above' => 'بالا', 'below' => 'پایین'];
    $crossStateOptions = ['any' => 'بی‌تفاوت', 'bullish' => 'صعودی (سریع بالای کند)', 'bearish' => 'نزولی (سریع زیر کند)'];
@endphp

@section('title', 'اسکرینر واچ‌لیست')
@section('page-title', 'اسکرینر واچ‌لیست')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">خانه</a> / <span class="active">اسکرینر واچ‌لیست</span>
@endsection

@section('content')

    <form method="GET" action="{{ route('symbols.screener') }}" id="screenerForm">

        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span>فیلترها</span>
            </div>
            <div class="card-body p-4">

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label">گروه نماد</label>
                        <select name="symbol_group_id" class="form-select form-select-sm">
                            <option value="">همه گروه‌ها</option>
                            @foreach ($symbolGroups as $group)
                                <option value="{{ $group->id }}" @selected(request('symbol_group_id') == $group->id)>
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @foreach ($periodOptions as $periodKey => $periodLabel)
                    <h2 class="h6 mb-3">نسبت به سطوح پیووت پوینت {{ $periodLabel }}</h2>
                    <div class="row g-3 mb-4">
                        @foreach ($pivotLabels as $key => $label)
                            <div class="col-3 col-md">
                                <label class="form-label" style="font-size: 0.82rem;">{{ $label }}</label>
                                <select name="pivot[{{ $periodKey }}][{{ $key }}]" class="form-select form-select-sm">
                                    @foreach ($threeStateOptions as $value => $optionLabel)
                                        <option value="{{ $value }}" @selected(($filters['pivot'][$periodKey][$key] ?? 'any') === $value)>
                                            {{ $optionLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                @endforeach

                <h2 class="h6 mb-3">نسبت به میانگین‌ها</h2>
                <div class="row g-3 mb-4">
                    @foreach ($averageLabels as $key => $label)
                        <div class="col-12 col-md-4">
                            <label class="form-label" style="font-size: 0.82rem;">{{ $label }}</label>
                            <select name="average[{{ $key }}]" class="form-select form-select-sm">
                                @foreach ($threeStateOptions as $value => $optionLabel)
                                    <option value="{{ $value }}" @selected(($filters['average'][$key] ?? 'any') === $value)>
                                        {{ $optionLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>

                <h2 class="h6 mb-3">کراس میانگین قیمت</h2>
                <div class="row g-3 mb-4">
                    @foreach ($crossLabels as $key => $label)
                        <div class="col-12 col-md-4">
                            <label class="form-label" style="font-size: 0.82rem;">{{ $label }}</label>
                            <select name="price_cross[{{ $key }}]" class="form-select form-select-sm">
                                @foreach ($crossStateOptions as $value => $optionLabel)
                                    <option value="{{ $value }}" @selected(($filters['price_cross'][$key] ?? 'any') === $value)>
                                        {{ $optionLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>

                <h2 class="h6 mb-3">کراس میانگین حجم</h2>
                <div class="row g-3 mb-4">
                    @foreach ($crossLabels as $key => $label)
                        <div class="col-12 col-md-4">
                            <label class="form-label" style="font-size: 0.82rem;">{{ $label }}</label>
                            <select name="volume_cross[{{ $key }}]" class="form-select form-select-sm">
                                @foreach ($crossStateOptions as $value => $optionLabel)
                                    <option value="{{ $value }}" @selected(($filters['volume_cross'][$key] ?? 'any') === $value)>
                                        {{ $optionLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-gold">
                    <i class="bi bi-funnel"></i>
                    اعمال فیلتر
                </button>
                <a href="{{ route('symbols.screener') }}" class="btn btn-outline-secondary">پاک کردن همه</a>
            </div>
        </div>
    </form>

    <div class="card mb-3">
        <div class="card-body p-3 d-flex flex-wrap align-items-center gap-2">
            <span class="text-muted" style="font-size: 0.85rem;">
                {{ $symbols->total() }} نماد مطابق فیلتر یافت شد.
            </span>

            @auth
                @php $userWatchlists = auth()->user()->watchlists; @endphp
                @if ($userWatchlists->isNotEmpty())
                    <form method="POST" action="{{ route('symbols.screener.add-to-watchlist') }}" class="d-flex gap-2 ms-auto">
                        @csrf
                        @foreach (request()->query() as $key => $value)
                            @if (is_array($value))
                                @foreach ($value as $subKey => $subValue)
                                    <input type="hidden" name="{{ $key }}[{{ $subKey }}]" value="{{ $subValue }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach

                        <select name="watchlist_id" class="form-select form-select-sm" style="max-width: 220px;" required>
                            <option value="">افزودن نتایج به واچ‌لیست...</option>
                            @foreach ($userWatchlists as $watchlist)
                                <option value="{{ $watchlist->id }}">{{ $watchlist->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-gold">افزودن</button>
                    </form>
                @endif
            @endauth
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 tabular-nums">
                <thead class="table-light">
                    <tr>
                        <th>نماد</th>
                        <th>نام کامل</th>
                        <th>گروه</th>
                        <th>آخرین قیمت</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($symbols as $symbol)
                        @php
                            $lastPrice = (float) ($symbol->livePrice?->last_price ?? $symbol->latestDailyPrice?->final ?? 0);
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('symbols.show', $symbol) }}" class="fw-semibold text-decoration-none">
                                    {{ $symbol->ticker }}
                                </a>
                            </td>
                            <td class="text-muted">{{ $symbol->name }}</td>
                            <td class="text-muted">{{ $symbol->group->name }}</td>
                            <td>{{ $lastPrice > 0 ? number_format($lastPrice) : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                هیچ نمادی با این ترکیب فیلتر پیدا نشد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $symbols->links() }}
    </div>

@endsection
