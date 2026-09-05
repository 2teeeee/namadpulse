@extends('layouts.app')

@section('title', 'گروه نماد جدید')
@section('page-title', 'گروه نماد جدید')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">خانه</a> /
    <a href="{{ route('admin.symbol-groups.index') }}">گروه نمادها</a> /
    <span class="active">جدید</span>
@endsection

@section('content')

    <div class="card" style="max-width: 560px;">
        <div class="card-header">اطلاعات گروه</div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.symbol-groups.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">نام گروه</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control @error('name') is-invalid @enderror"
                        placeholder="مثلاً فلزات اساسی"
                        value="{{ old('name') }}"
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="code" class="form-label">کد گروه</label>
                    <input
                        type="text"
                        name="code"
                        id="code"
                        class="form-control tabular-nums @error('code') is-invalid @enderror"
                        placeholder="مثلاً METAL01"
                        value="{{ old('code') }}"
                        required
                    >
                    <div class="form-text">کد باید یکتا باشد و بعداً قابل تغییر نیست.</div>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="parent_id" class="form-label">گروه والد (اختیاری)</label>
                    <select name="parent_id" id="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                        <option value="">— بدون گروه والد —</option>
                        @foreach ($parentGroups as $parentGroup)
                            <option value="{{ $parentGroup->id }}" @selected(old('parent_id') == $parentGroup->id)>
                                {{ $parentGroup->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-gold">ذخیره گروه</button>
                    <a href="{{ route('admin.symbol-groups.index') }}" class="btn btn-outline-secondary">انصراف</a>
                </div>
            </form>
        </div>
    </div>

@endsection
