<aside class="app-sidebar" id="appSidebar">
    <div class="app-sidebar__brand">
        <div class="app-sidebar__brand-mark">جس</div>
        <div class="app-sidebar__brand-text">
            جلالی سهام
            <span class="app-sidebar__brand-sub">پایش و آلرت بازار بورس</span>
        </div>
    </div>

    <nav class="app-nav">
        <div class="app-nav__label">پایش بازار</div>
        <a href="{{ route('dashboard') }}" class="app-nav__item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
            <i class="bi bi-grid-1x2"></i>
            داشبورد
        </a>
        <a href="{{ route('watchlists.index') }}" class="app-nav__item {{ request()->routeIs('watchlists.*') ? 'is-active' : '' }}">
            <i class="bi bi-star"></i>
            واچ‌لیست‌های من
        </a>
        <a href="{{ route('symbols.index') }}" class="app-nav__item {{ request()->routeIs('symbols.*') ? 'is-active' : '' }}">
            <i class="bi bi-graph-up-arrow"></i>
            نمادها
        </a>

        <div class="app-nav__label">معاملات</div>
        <a href="{{ route('alert-rules.index') }}" class="app-nav__item {{ request()->routeIs('alert-rules.*') ? 'is-active' : '' }}">
            <i class="bi bi-bell"></i>
            قوانین آلرت
        </a>
        <a href="{{ route('alert-logs.index') }}" class="app-nav__item {{ request()->routeIs('alert-logs.*') ? 'is-active' : '' }}">
            <i class="bi bi-clock-history"></i>
            تاریخچه آلرت‌ها
        </a>

        @auth
            @if (auth()->user()->isAdmin())
                <div class="app-nav__label">مدیریت سیستم</div>
                <a href="{{ route('admin.users.index') }}" class="app-nav__item {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">
                    <i class="bi bi-people"></i>
                    کاربران
                </a>
                <a href="{{ route('admin.symbol-groups.index') }}" class="app-nav__item {{ request()->routeIs('admin.symbol-groups.*') ? 'is-active' : '' }}">
                    <i class="bi bi-diagram-3"></i>
                    گروه نمادها
                </a>
                <a href="{{ route('admin.symbols.index') }}" class="app-nav__item {{ request()->routeIs('admin.symbols.*') ? 'is-active' : '' }}">
                    <i class="bi bi-diagram-3"></i>
                    مدیریت نمادها
                </a>
            @endif
        @endauth
    </nav>

    @auth
        <div class="app-sidebar__footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="app-nav__item w-100 border-0 bg-transparent text-start">
                    <i class="bi bi-box-arrow-left"></i>
                    خروج از حساب
                </button>
            </form>
        </div>
    @endauth
</aside>
