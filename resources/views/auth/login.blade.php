@extends('layouts.guest')

@section('title', 'ورود')

@section('content')

    <h1 class="h5 mb-1">ورود به حساب کاربری</h1>
    <p class="text-muted mb-4" style="font-size: 0.85rem;">
        شماره موبایل خود را وارد کنید تا کد ورود برایتان پیامک شود.
    </p>

    <form method="POST" action="{{ route('login.send-otp') }}">
        @csrf

        <div class="mb-3">
            <label for="mobile" class="form-label">شماره موبایل</label>
            <input
                type="tel"
                inputmode="numeric"
                pattern="09[0-9]{9}"
                maxlength="11"
                name="mobile"
                id="mobile"
                class="form-control tabular-nums @error('mobile') is-invalid @enderror"
                placeholder="09xxxxxxxxx"
                value="{{ old('mobile') }}"
                autofocus
                required
            >
        </div>

        <button type="submit" class="btn btn-gold w-100">دریافت کد ورود</button>
    </form>

    <div class="text-center mt-3" style="font-size: 0.82rem;">
        حساب کاربری ندارید؟
        <a href="{{ route('register') }}">ثبت‌نام</a>
    </div>

@endsection
