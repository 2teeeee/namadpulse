@extends('layouts.app')

@section('title', 'ویرایش گروه نماد')
@section('page-title', 'ویرایش گروه نماد')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">خانه</a> /
    <a href="{{ route('admin.symbol-groups.index') }}">گروه نمادها</a> /
    <span class="active">ویرایش</span>
@endsection

@section('content')

    <div class="card" style="max-width: 560px;">
        <div class="card-header">اطلاعات گروه</div>
        <div class="card-body p-4">

            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="stat-card__label">کد گروه</div>
                    <div class="fw-semibold tabular-nums">{{ $symbolGroup->code }}</div>
                </div>
                <div class="col-6">
                    <div class="stat-card__label">گروه والد</div>
                    <div class="fw-semibold">{{ $symbolGroup->parent?->name ?? '—' }}</div>
                </div>
            </div>
            <p class="text-muted mb-4" style="font-size: 0.8rem;">
                کد گروه و گروه والد پس از ساخت قابل تغییر نیستند.
            </p>

            <form method="POST" action="{{ route('admin.symbol-groups.update', $symbolGroup) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">نام گروه</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $symbolGroup->name) }}"
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 form-check form-switch">
                    <input
                        type="hidden"
                        name="is_active"
                        value="0"
                    >
                    <input
                        type="checkbox"
                        name="is_active"
                        id="is_active"
                        class="form-check-input"
                        value="1"
                        @checked(old('is_active', $symbolGroup->is_active))
                    >
                    <label for="is_active" class="form-check-label">گروه فعال است</label>
                    <div class="form-text">گروه‌های غیرفعال در فرم‌های انتخاب نماد نمایش داده نمی‌شوند.</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-gold">ذخیره تغییرات</button>
                    <a href="{{ route('admin.symbol-groups.index') }}" class="btn btn-outline-secondary">انصراف</a>
                </div>
            </form>
        </div>
    </div>

@endsection
