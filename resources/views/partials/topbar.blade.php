<header class="app-topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-outline-secondary sidebar-toggle" type="button" id="sidebarToggleBtn" aria-label="باز کردن منو">
            <i class="bi bi-list"></i>
        </button>

        <form class="app-topbar__search d-none d-md-block" role="search">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="search" class="form-control border-start-0" placeholder="جست‌وجوی نماد یا شرکت...">
            </div>
        </form>
    </div>

    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('alert-logs.index') }}" class="btn btn-sm btn-outline-secondary position-relative" aria-label="اعلان‌ها">
            <i class="bi bi-bell"></i>
        </a>

        @auth
            <div class="app-topbar__user">
                <div class="app-topbar__user-avatar">
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="d-none d-sm-block">
                    <div class="fw-semibold" style="font-size: 0.85rem; line-height: 1.3;">
                        {{ auth()->user()->name }}
                    </div>
                    <div class="app-topbar__user-role">
                        {{ auth()->user()->isAdmin() ? 'مدیر سیستم' : 'معامله‌گر' }}
                    </div>
                </div>
            </div>
        @endauth
    </div>
</header>
