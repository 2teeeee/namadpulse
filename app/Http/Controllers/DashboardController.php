<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // TODO: آمار واقعی (تعداد نمادهای واچ‌لیست، آلرت‌های فعال، بیشترین رشد/افت روز) در فاز بعدی جایگزین داده‌ی نمونه شود
        return view('dashboard');
    }
}
