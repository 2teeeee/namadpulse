<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function sendLoginOtp(Request $request, OtpService $otpService): JsonResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'regex:/^09[0-9]{9}$/'],
        ]);

        $user = User::where('mobile', $validated['mobile'])->first();

        if (! $user || ! $user->is_active) {
            return response()->json([
                'message' => 'کاربری با این شماره موبایل یافت نشد.',
            ], 422);
        }

        $otpService->sendCode($validated['mobile']);

        return response()->json([
            'message' => 'کد ورود پیامک شد.',
        ]);
    }

    public function verifyLoginOtp(Request $request, OtpService $otpService): JsonResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'regex:/^09[0-9]{9}$/'],
            'code' => ['required', 'digits:6'],
        ]);

        $result = $otpService->verifyCode($validated['mobile'], $validated['code']);

        if (! $result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        $user = User::where('mobile', $validated['mobile'])->firstOrFail();

        // یک توکن جدید برای این دستگاه/سشن نویسنده
        $token = $user->createToken('nuxt-web')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user->load('roles')),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()->load('roles')),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        // فقط توکنی که همین درخواست باهاش احراز هویت شده باطل می‌شه، نه همه‌ی دستگاه‌های کاربر
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'خارج شدید.']);
    }
}
