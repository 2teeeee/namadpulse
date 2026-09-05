<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ورود') | جلالی سهام</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">

    <style>
        body.guest-body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 30% 20%, #16233c 0%, #0c1526 65%);
            padding: 1.5rem;
        }

        .guest-card {
            width: 100%;
            max-width: 400px;
            background: var(--surface-card);
            border-radius: var(--radius-md);
            padding: 2.25rem 2rem;
            box-shadow: 0 20px 45px rgba(6, 12, 26, 0.35);
        }

        .guest-brand {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 1.75rem;
        }

        .guest-brand-mark {
            width: 38px;
            height: 38px;
            border-radius: var(--radius-sm);
            background: var(--gold-tint);
            border: 1px solid var(--gold-500);
            color: var(--gold-600);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .guest-brand-text {
            font-weight: 700;
            font-size: 1.05rem;
        }

        .guest-footer-note {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.78rem;
            color: var(--text-on-navy-dim);
        }
    </style>

    @stack('styles')
</head>
<body class="guest-body">

    <div>
        <div class="guest-card">
            <div class="guest-brand">
                <div class="guest-brand-mark">جس</div>
                <div class="guest-brand-text">جلالی سهام</div>
            </div>

            @if (session('success'))
                <div class="alert alert-success py-2" role="alert">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger py-2">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>

        <p class="guest-footer-note">پایش و آلرت بازار بورس ایران</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
