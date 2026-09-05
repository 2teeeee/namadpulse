<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'جلالی سهام') | پایش بازار بورس</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{asset("fonts/fontstyle.css")}}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">

    @stack('styles')
</head>
<body>

    <div class="app-shell">
        @include('partials.sidebar')

        <div class="app-main">
            @include('partials.topbar')

            <main class="app-content">
                <div class="page-header">
                    <div>
                        <h1 class="page-header__title">@yield('page-title', 'داشبورد')</h1>
                        @hasSection('breadcrumb')
                            <nav class="page-header__breadcrumb">
                                @yield('breadcrumb')
                            </nav>
                        @endif
                    </div>
                    <div>
                        @yield('page-actions')
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-check-circle"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <div class="fw-semibold mb-1">لطفاً خطاهای زیر را بررسی کنید:</div>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggleBtn')?.addEventListener('click', function () {
            document.getElementById('appSidebar')?.classList.toggle('is-open');
        });
    </script>

    @stack('scripts')
</body>
</html>
