@extends('layouts.app')

@section('title', 'ویرایش نماد')
@section('page-title', 'ویرایش نماد: ' . $symbol->ticker)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">خانه</a> /
    <a href="{{ route('admin.symbols.index') }}">نمادها</a> /
    <span class="active">ویرایش</span>
@endsection

@section('content')

    <div class="card" style="max-width: 720px;">
        <div class="card-header">اطلاعات نماد</div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.symbols.update', $symbol) }}">
                @csrf
                @method('PUT')

                @include('admin.symbols._form', ['symbol' => $symbol, 'symbolGroups' => $symbolGroups])

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-gold">ذخیره تغییرات</button>
                    <a href="{{ route('admin.symbols.index') }}" class="btn btn-outline-secondary">انصراف</a>
                </div>
            </form>
        </div>
    </div>

@endsection
