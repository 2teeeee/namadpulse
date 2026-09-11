@extends('layouts.app')

@php
    use App\Support\PercentDistanceCalculator as Dist;

    $snapshot = $symbol->technicalSnapshot;

    $periodLabels = ['year' => 'سالانه', 'quarter' => 'فصلی', 'month' => 'ماهانه'];
    $levelLabels = ['pp' => 'PP', 'r1' => 'R1', 'r2' => 'R2', 'r3' => 'R3', 's1' => 'S1', 's2' => 'S2', 's3' => 'S3'];

    $yearlyPivot = $pivotLevels['year'] ?? null;
    $isAboveYearlyPivot = $yearlyPivot ? $yearlyPivot->isAboveLevel($lastPrice, 'pp') : null;
    $isAboveEma200 = $snapshot ? $snapshot->isAboveField('ema_200', $lastPrice) : null;
@endphp

@section('title', $symbol->ticker)
@section('page-title', $symbol->ticker . ' — ' . $symbol->name)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">خانه</a> /
    <a href="{{ route('symbols.index') }}">نمادها</a> /
    <span class="active">{{ $symbol->ticker }}</span>
@endsection

@section('content')

    {{-- خلاصه بالای صفحه --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card__label">آخرین قیمت</div>
                <div class="stat-card__value tabular-nums">{{ number_format($lastPrice) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card__label">سرمایه</div>
                <div class="stat-card__value tabular-nums">
                    {{ $symbol->capital() ? number_format($symbol->capital() / 1e13) . ' همت' : '—' }}
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card__label">ارزش بازار</div>
                <div class="stat-card__value tabular-nums">
                    {{ $symbol->marketCap() ? number_format($symbol->marketCap() / 1e13) . ' همت' : '—' }}
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card__label">گروه نماد</div>
                <div class="stat-card__value" style="font-size: 1.05rem;">{{ $symbol->group->name }}</div>
            </div>
        </div>
    </div>

    {{-- میانگین‌ها --}}
    <div class="row g-3 mb-4">
        @php
            $averageFields = [
                'price_ma_20' => 'میانگین ۲۰ روزه',
                'ema_100' => 'میانگین نمایی ۱۰۰ روزه',
                'ema_200' => 'میانگین نمایی ۲۰۰ روزه',
            ];
        @endphp

        @foreach ($averageFields as $field => $label)
            <div class="col-12 col-md-4">
                <div class="stat-card">
                    <div class="stat-card__label">{{ $label }}</div>
                    @if ($snapshot && ! is_null($snapshot->{$field}))
                        @php
                            $percent = $snapshot->percentVsLast($field, $lastPrice);
                            $above = $snapshot->isAboveField($field, $lastPrice);
                        @endphp
                        <div class="stat-card__value tabular-nums">{{ number_format($snapshot->{$field}) }}</div>
                        <div class="stat-card__delta {{ $above ? 'is-positive' : 'is-negative' }}">
                            <i class="bi bi-{{ $above ? 'arrow-up' : 'arrow-down' }}"></i>
                            {{ $percent }}٪
                        </div>
                    @else
                        <div class="stat-card__value text-muted">—</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- نشانه‌های وضعیت --}}
    <div class="mb-4">
        <div class="mb-2">
            @if (! is_null($isAboveYearlyPivot))
                <span class="badge {{ $isAboveYearlyPivot ? 'bg-positive-tint text-positive' : 'bg-negative-tint text-negative' }} px-3 py-2">
                <i class="bi bi-{{ $isAboveYearlyPivot ? 'arrow-up-circle' : 'arrow-down-circle' }}"></i>
                {{ $isAboveYearlyPivot ? 'بالای پیووت پوینت سالانه' : 'زیر پیووت پوینت سالانه' }}
            </span>
            @endif
        </div>

        <div class="mb-2">
            @if (! is_null($isAboveEma200))
                <span class="badge {{ $isAboveEma200 ? 'bg-positive-tint text-positive' : 'bg-negative-tint text-negative' }} px-3 py-2">
                <i class="bi bi-{{ $isAboveEma200 ? 'arrow-up-circle' : 'arrow-down-circle' }}"></i>
                {{ $isAboveEma200 ? 'بالای میانگین نمایی ۲۰۰ روزه' : 'زیر میانگین نمایی ۲۰۰ روزه' }}
            </span>
            @endif
        </div>

        @if ($snapshot)
            <div class="mb-2">
                <span class="me-2">کراس میانگین قیمت: </span>
                @foreach ([[5, 20], [5, 60], [20, 60]] as [$fast, $slow])
                    @php $cross = $snapshot->priceCross($fast, $slow); @endphp
                    @if (! is_null($cross))
                        <span class="badge {{ $cross ? 'bg-positive-tint text-positive' : 'bg-negative-tint text-negative' }} px-3 py-2">
                        {{ $cross ? "میانگین {$fast} بالای {$slow}" : "میانگین {$fast} زیر {$slow}" }}
                    </span>
                    @endif
                @endforeach
            </div>
            <div class="mb-2">
                <span class="me-2">کراس میانگین حجم: </span>
                @foreach ([[5, 20], [5, 60], [20, 60]] as [$fast, $slow])
                    @php $volumeCross = $snapshot->volumeCross($fast, $slow); @endphp
                    @if (! is_null($volumeCross))
                        <span class="badge {{ $volumeCross ? 'bg-positive-tint text-positive' : 'bg-negative-tint text-negative' }} px-3 py-2">
                        {{ $volumeCross ? "میانگین حجم {$fast} بالای {$slow}" : "میانگین حجم {$fast} زیر {$slow}" }}
                    </span>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    {{-- جدول پیووت پوینت --}}
    <div class="card mb-4">
        <div class="card-header">سطوح پیووت پوینت</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 tabular-nums">
                <thead class="table-light">
                    <tr>
                        <th></th>
                        @foreach ($periodLabels as $key => $label)
                            <th>{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-semibold text-muted">بیشترین قیمت</td>
                        @foreach ($periodLabels as $key => $label)
                            <td>{{ isset($pivotLevels[$key]) ? number_format($pivotLevels[$key]->high) : '—' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">کمترین قیمت</td>
                        @foreach ($periodLabels as $key => $label)
                            <td>{{ isset($pivotLevels[$key]) ? number_format($pivotLevels[$key]->low) : '—' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">آخرین قیمت</td>
                        @foreach ($periodLabels as $key => $label)
                            <td>{{ isset($pivotLevels[$key]) ? number_format($pivotLevels[$key]->close) : '—' }}</td>
                        @endforeach
                    </tr>

                    @foreach ($levelLabels as $levelKey => $levelLabel)
                        <tr>
                            <td class="fw-semibold">{{ $levelLabel }}</td>
                            @foreach ($periodLabels as $periodKey => $label)
                                @php $level = $pivotLevels[$periodKey] ?? null; @endphp
                                <td>
                                    @if ($level)
                                        @php
                                            $percent = $level->distancePercentFrom($lastPrice, $levelKey);
                                            $above = $level->isAboveLevel($lastPrice, $levelKey);
                                        @endphp
                                        {{ number_format($level->{$levelKey}) }}
                                        <span class="{{ $above ? 'text-positive' : 'text-negative' }}" style="font-size: 0.78rem;">
                                            {{ $percent }}٪
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- جدول نقاط زیگزاگ --}}
    <div class="card">
        <div class="card-header">نقاط زیگزاگ (بر اساس MACD)</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>تاریخ</th>
                        <th>مبلغ</th>
                        <th>کف / سقف</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($zigzagPoints as $point)
                        @php
                            $percent = Dist::between((float) $point->price, $lastPrice);
                            $above = Dist::isAboveLevel((float) $point->price, $lastPrice);
                        @endphp
                        <tr>
                            <td>{{ $point->jalali_date }}</td>
                            <td class="tabular-nums">
                                {{ number_format($point->price) }}
                                <span class="{{ $above ? 'text-positive' : 'text-negative' }}" style="font-size: 0.78rem;">
                                    {{ $percent }}٪
                                </span>
                            </td>
                            <td>
                                @if ($point->type === 'peak')
                                    <span class="badge bg-negative-tint text-negative">سقف</span>
                                @else
                                    <span class="badge bg-positive-tint text-positive">کف</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                داده‌ی کافی برای محاسبه‌ی زیگزاگ این نماد وجود ندارد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
