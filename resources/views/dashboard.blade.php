@extends('layouts.app')

@section('title', 'داشبورد')
@section('page-title', 'داشبورد')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">خانه</a> / <span class="active">داشبورد</span>
@endsection

@section('page-actions')
    <a href="{{ route('watchlists.index') }}" class="btn btn-gold btn-sm">
        <i class="bi bi-plus-lg"></i>
        واچ‌لیست جدید
    </a>
@endsection

@section('content')

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card__label">نمادهای زیر نظر</div>
                <div class="stat-card__value tabular-nums">۱۲</div>
                <div class="stat-card__delta text-muted">در ۲ واچ‌لیست</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card__label">آلرت‌های فعال</div>
                <div class="stat-card__value tabular-nums">۵</div>
                <div class="stat-card__delta text-muted">۱ مورد امروز فعال شد</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card__label">بیشترین رشد امروز</div>
                <div class="stat-card__value tabular-nums">فولاد</div>
                <div class="stat-card__delta is-positive">
                    <i class="bi bi-arrow-up"></i> ۴.۲٪
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card__label">بیشترین افت امروز</div>
                <div class="stat-card__value tabular-nums">شپنا</div>
                <div class="stat-card__delta is-negative">
                    <i class="bi bi-arrow-down"></i> ۲.۱٪
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span>نمادهای واچ‌لیست «سبد اصلی»</span>
            <a href="#" class="text-decoration-none" style="font-size: 0.82rem;">مشاهده همه</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>نماد</th>
                        <th>قیمت پایانی دیروز</th>
                        <th>آخرین قیمت</th>
                        <th>تغییر</th>
                        <th>حجم معاملات</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-semibold">فولاد</td>
                        <td class="tabular-nums">۶٬۸۵۰</td>
                        <td class="tabular-nums">۷٬۱۴۰</td>
                        <td class="text-positive tabular-nums"><i class="bi bi-arrow-up"></i> ۴.۲٪</td>
                        <td class="tabular-nums">۱۲٬۳۴۰٬۰۰۰</td>
                        <td class="text-end"><i class="bi bi-three-dots"></i></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">شپنا</td>
                        <td class="tabular-nums">۹٬۴۲۰</td>
                        <td class="tabular-nums">۹٬۲۲۰</td>
                        <td class="text-negative tabular-nums"><i class="bi bi-arrow-down"></i> ۲.۱٪</td>
                        <td class="tabular-nums">۸٬۹۰۰٬۰۰۰</td>
                        <td class="text-end"><i class="bi bi-three-dots"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

@endsection
