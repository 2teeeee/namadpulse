<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\Auth\OtpService;

class AuthController extends Controller
{

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function sendLoginOtp(Request $request, OtpService $otpService): RedirectResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'regex:/^09[0-9]{9}$/'],
        ]);

        $user = User::where('mobile', $validated['mobile'])->first();

        if (! $user || ! $user->is_active) {
            return back()->withErrors(['mobile' => 'کاربری با این شماره موبایل یافت نشد.'])->withInput();
        }

        $otpService->sendCode($validated['mobile']);

        $request->session()->put('login_mobile', $validated['mobile']);

        return redirect()->route('login.verify.show');
    }

    public function showLoginVerify(Request $request): View
    {
        abort_unless($request->session()->has('login_mobile'), 404);

        return view('auth.login-otp', ['mobile' => $request->session()->get('login_mobile')]);
    }

    public function verifyLoginOtp(Request $request, OtpService $otpService): RedirectResponse
    {
        $mobile = $request->session()->get('login_mobile');
        abort_unless($mobile, 404);

        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $result = $otpService->verifyCode($mobile, $validated['code']);

        if (! $result['success']) {
            return back()->withErrors(['code' => $result['message']]);
        }

        $user = User::where('mobile', $mobile)->firstOrFail();
        Auth::login($user);
        $request->session()->forget('login_mobile');
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }


    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        // TODO: ثبت‌نام واقعی + ارسال کد تایید به موبایل در فاز بعدی پیاده‌سازی می‌شود
        return redirect()->route('auth.verify.show');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
