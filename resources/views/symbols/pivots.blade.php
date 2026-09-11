@extends('layouts.app')

@php
    $periodOptions = ['year' => 'سالانه', 'quarter' => 'فصلی', 'month' => 'ماهانه'];
    $levelLabels = ['pp' => 'PP', 'r1' => 'R1', 'r2' => 'R2', 'r3' => 'R3', 's1' => 'S1', 's2' => 'S2', 's3' => 'S3'];
@endphp

@section('title', 'پیووت پوینت نمادها')
@section('page-title', 'پیووت پوینت نمادها')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">خانه</a> / <span class="active">پیووت پوینت نمادها</span>
@endsection

@section('content')

    <div class="card mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('symbols.pivots') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <input
                            type="text"
                            name="q"
                            class="form-control form-control-sm"
                            placeholder="جست‌وجوی نماد یا نام شرکت..."
                            value="{{ request('q') }}"
                    >
                </div>

                <div class="col-12 col-md-3">
                    <select name="symbol_group_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">همه گروه‌ها</option>
                        @foreach ($symbolGroups as $group)
                            <option value="{{ $group->id }}" @selected(request('symbol_group_id') == $group->id)>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-1">
                    <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>

                <div class="col-12 col-md-4">
                    <div class="btn-group w-100" role="group">
                        @foreach ($periodOptions as $value => $label)

                            <a href="{{ route('symbols.pivots', array_merge(request()->query(), ['period' => $value])) }}"
                            class="btn btn-sm {{ $period === $value ? 'btn-gold' : 'btn-outline-secondary' }}"
                            >
                            {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            سطوح پیووت پوینت {{ $periodOptions[$period] }}
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 tabular-nums">
                <thead class="table-light">
                    <tr>
                        <th>نماد</th>
                        <th>نام کامل</th>
                        @foreach ($levelLabels as $key => $label)
                            <th>{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($symbols as $symbol)
                        @php
                            $level = $symbol->pivotLevels->first();
                            $lastPrice = (float) ($symbol->livePrice?->last_price ?? $symbol->latestDailyPrice?->final ?? 0);
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('symbols.show', $symbol) }}" class="fw-semibold text-decoration-none">
                                    {{ $symbol->ticker }}
                                </a>
                            </td>
                            <td class="text-muted">{{ $symbol->name }}</td>

                            @if ($level && $lastPrice > 0)
                                @foreach ($levelLabels as $key => $label)
                                    @php
                                        $percent = $level->distancePercentFrom($lastPrice, $key);
                                        $above = $level->isAboveLevel($lastPrice, $key);
                                    @endphp
                                    <td>
                                        {{ number_format($level->{$key}) }}
                                        <span class="{{ $above ? 'text-positive' : 'text-negative' }}" style="font-size: 0.78rem;">
                                            {{ $percent }}٪
                                        </span>
                                    </td>
                                @endforeach
                            @else
                                @foreach ($levelLabels as $key => $label)
                                    <td class="text-muted">—</td>
                                @endforeach
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($levelLabels) + 2 }}" class="text-center text-muted py-4">
                                نمادی یافت نشد.
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
