@extends('layouts.app')

@section('title', 'نماد جدید')
@section('page-title', 'نماد جدید')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">خانه</a> /
    <a href="{{ route('admin.symbols.index') }}">نمادها</a> /
    <span class="active">جدید</span>
@endsection

@section('content')

    <div class="card" style="max-width: 720px;">
        <div class="card-header">اطلاعات نماد</div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.symbols.store') }}">
                @csrf

                @include('admin.symbols._form', ['symbolGroups' => $symbolGroups])

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-gold">ذخیره نماد</button>
                    <a href="{{ route('admin.symbols.index') }}" class="btn btn-outline-secondary">انصراف</a>
                </div>
            </form>
        </div>
    </div>

@endsection
