@extends('layouts.app')

@section('title', 'Import قیمت‌ها')
@section('page-title', 'Import قیمت روزانه')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">خانه</a> / <span class="active">Import قیمت‌ها</span>
@endsection

@section('content')

    <div class="card mb-4" style="max-width: 640px;">
        <div class="card-header">شروع Import جدید</div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.imports.store') }}" id="importForm">
                @csrf

                <div class="mb-3">
                    <label for="ticker" class="form-label">فقط یک نماد (اختیاری)</label>
                    <input
                        type="text"
                        name="ticker"
                        id="ticker"
                        class="form-control"
                        placeholder="مثلاً فولاد — خالی بگذارید برای همه نمادهای فعال"
                        value="{{ old('ticker') }}"
                    >
                </div>

                <div class="mb-4 form-check form-switch">
                    <input type="hidden" name="with_indicators" value="0">
                    <input
                        type="checkbox"
                        name="with_indicators"
                        id="with_indicators"
                        class="form-check-input"
                        value="1"
                        checked
                    >
                    <label for="with_indicators" class="form-check-label">
                        بعد از import، اندیکاتورها هم بازمحاسبه شوند
                    </label>
                </div>

                <button type="submit" class="btn btn-gold" id="importSubmitBtn">
                    <i class="bi bi-cloud-arrow-down"></i>
                    شروع Import
                </button>
            </form>
        </div>
    </div>

    @if ($latestRun && ! $latestRun->isFinished())
        <div class="card mb-4" style="max-width: 640px;" id="progressCard" data-run-id="{{ $latestRun->id }}">
            <div class="card-header">در حال اجرا...</div>
            <div class="card-body p-4">
                <div class="progress mb-2" style="height: 10px;">
                    <div class="progress-bar bg-warning" id="progressBar" style="width: {{ $latestRun->progressPercent() }}%"></div>
                </div>
                <div class="d-flex justify-content-between text-muted" style="font-size: 0.8rem;">
                    <span id="progressText">
                        {{ $latestRun->processed_symbols }} از {{ $latestRun->total_symbols ?? '?' }} نماد
                    </span>
                    <span id="progressPercentText">{{ $latestRun->progressPercent() }}٪</span>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">تاریخچه‌ی Importها</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>تاریخ شروع</th>
                        <th>فیلتر نماد</th>
                        <th>وضعیت</th>
                        <th>موفق / رد‌شده</th>
                        <th>اجراکننده</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentRuns as $run)
                        <tr>
                            <td class="tabular-nums">{{ $run->started_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td>{{ $run->ticker_filter ?? 'همه نمادها' }}</td>
                            <td>
                                @switch($run->status)
                                    @case('completed')
                                        <span class="badge bg-positive-tint text-positive">تکمیل‌شده</span>
                                        @break
                                    @case('failed')
                                        <span class="badge bg-negative-tint text-negative">ناموفق</span>
                                        @break
                                    @case('running')
                                        <span class="badge bg-gold-tint" style="color: var(--gold-600);">در حال اجرا</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary-subtle text-muted">در صف</span>
                                @endswitch
                            </td>
                            <td class="tabular-nums">{{ $run->success_count }} / {{ $run->skipped_count }}</td>
                            <td class="text-muted">{{ $run->triggeredBy?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">هنوز importی اجرا نشده است.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $recentRuns->links() }}
    </div>

@endsection

@push('scripts')
<script>
    const progressCard = document.getElementById('progressCard');
    const importSubmitBtn = document.getElementById('importSubmitBtn');

    if (progressCard) {
        importSubmitBtn.disabled = true;

        const runId = progressCard.dataset.runId;
        const statusUrlBase = "{{ url('/admin/imports') }}";

        const poll = setInterval(async () => {
            try {
                const response = await fetch(`${statusUrlBase}/${runId}/status`);
                const data = await response.json();

                const percent = data.progress_percent ?? 0;
                document.getElementById('progressBar').style.width = percent + '%';
                document.getElementById('progressPercentText').textContent = percent + '٪';
                document.getElementById('progressText').textContent =
                    `${data.processed_symbols} از ${data.total_symbols ?? '?'} نماد`;

                if (data.is_finished) {
                    clearInterval(poll);
                    location.reload();
                }
            } catch (e) {
                clearInterval(poll);
            }
        }, 2500);
    }
</script>
@endpush
