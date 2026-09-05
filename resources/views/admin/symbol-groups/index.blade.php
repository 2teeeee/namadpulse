@extends('layouts.app')

@section('title', 'گروه نمادها')
@section('page-title', 'گروه نمادها')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">خانه</a> / <span class="active">گروه نمادها</span>
@endsection

@section('page-actions')
    <a href="{{ route('admin.symbol-groups.create') }}" class="btn btn-gold btn-sm">
        <i class="bi bi-plus-lg"></i>
        گروه جدید
    </a>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            فهرست گروه‌ها
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>نام گروه</th>
                        <th>کد</th>
                        <th>گروه والد</th>
                        <th>تعداد نمادها</th>
                        <th>وضعیت</th>
                        <th class="text-end">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($symbolGroups as $group)
                        <tr>
                            <td class="fw-semibold">{{ $group->name }}</td>
                            <td class="tabular-nums">{{ $group->code }}</td>
                            <td class="text-muted">{{ $group->parent?->name ?? '—' }}</td>
                            <td class="tabular-nums">{{ $group->symbols_count }}</td>
                            <td>
                                @if ($group->is_active)
                                    <span class="badge bg-positive-tint text-positive">فعال</span>
                                @else
                                    <span class="badge bg-negative-tint text-negative">غیرفعال</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.symbol-groups.edit', $group) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                    ویرایش
                                </a>
                                <form action="{{ route('admin.symbol-groups.destroy', $group) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('حذف این گروه باعث حذف نشدن نمادهای زیرمجموعه نمی‌شود اما رابطه‌شان قطع می‌شود. ادامه می‌دهید؟');">
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
                                هنوز هیچ گروه نمادی ثبت نشده است.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $symbolGroups->links() }}
    </div>

@endsection
