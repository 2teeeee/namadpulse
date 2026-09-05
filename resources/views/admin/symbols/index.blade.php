@extends('layouts.app')

@section('title', 'نمادها')
@section('page-title', 'نمادها')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">خانه</a> / <span class="active">نمادها</span>
@endsection

@section('page-actions')
    <a href="{{ route('admin.symbols.create') }}" class="btn btn-gold btn-sm">
        <i class="bi bi-plus-lg"></i>
        نماد جدید
    </a>
@endsection

@section('content')

    <div class="card mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.symbols.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <input
                        type="text"
                        name="q"
                        class="form-control form-control-sm"
                        placeholder="جست‌وجوی نماد یا نام شرکت..."
                        value="{{ request('q') }}"
                    >
                </div>
                <div class="col-8 col-md-4">
                    <select name="symbol_group_id" class="form-select form-select-sm">
                        <option value="">همه گروه‌ها</option>
                        @foreach ($symbolGroups as $group)
                            <option value="{{ $group->id }}" @selected(request('symbol_group_id') == $group->id)>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4 col-md-3">
                    <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-search"></i>
                        اعمال فیلتر
                    </button>
                </div>
            </form>
        </div>
    </div>

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
                                <a href="{{ route('admin.symbols.edit', $symbol) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                    ویرایش
                                </a>
                                <form action="{{ route('admin.symbols.destroy', $symbol) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('با حذف این نماد، تاریخچه قیمت و آلرت‌های مرتبط نیز حذف می‌شود. ادامه می‌دهید؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
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
