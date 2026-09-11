@extends('layouts.app')

@section('title', 'نمادها')
@section('page-title', 'نمادها')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">خانه</a> / <span class="active">نمادها</span>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            فهرست نمادها
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>نماد</th>
                        <th>نام شرکت</th>
                        <th>گروه</th>
                        <th>بازار</th>
                        <th>وضعیت</th>
                        <th class="text-end">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($symbols as $symbol)
                        <tr>
                            <td class="fw-semibold">{{ $symbol->ticker }}</td>
                            <td>{{ $symbol->name }}</td>
                            <td class="text-muted">{{ $symbol->group->name }}</td>
                            <td>
                                @switch($symbol->board)
                                    @case('bourse')
                                        بازار اول/دوم بورس
                                        @break
                                    @case('farabourse')
                                        فرابورس
                                        @break
                                    @default
                                        سایر
                                @endswitch
                            </td>
                            <td>
                                @if ($symbol->is_active)
                                    <span class="badge bg-positive-tint text-positive">فعال</span>
                                @else
                                    <span class="badge bg-negative-tint text-negative">غیرفعال</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('symbols.show', $symbol) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i>
                                    نمایش
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                نمادی با این مشخصات یافت نشد.
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
