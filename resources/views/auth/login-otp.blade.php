@extends('layouts.guest')

@section('title', 'تایید کد ورود')

@section('content')

    <h1 class="h5 mb-1">کد ورود را وارد کنید</h1>
    <p class="text-muted mb-4" style="font-size: 0.85rem;">
        کد ۶ رقمی برای شماره
        <span class="tabular-nums fw-semibold">{{ $mobile }}</span>
        پیامک شد.
    </p>

    <form method="POST" action="{{ route('login.verify') }}">
        @csrf

        <div class="mb-3">
            <label for="code" class="form-label">کد تایید</label>
            <input
                type="text"
                inputmode="numeric"
                pattern="[0-9]{6}"
                maxlength="6"
                name="code"
                id="code"
                class="form-control tabular-nums text-center @error('code') is-invalid @enderror"
                style="letter-spacing: 0.5rem; font-size: 1.25rem;"
                autofocus
                required
            >
        </div>

        <button type="submit" class="btn btn-gold w-100 mb-2">ورود</button>

        <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100">
            ویرایش شماره موبایل
        </a>
    </form>

@endsection
