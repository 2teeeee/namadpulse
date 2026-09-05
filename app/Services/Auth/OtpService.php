<?php

namespace App\Services\Auth;

use App\Models\MobileVerification;
use App\Services\Sms\SmsIrService;
use Illuminate\Support\Carbon;

class OtpService
{
    private const CODE_LENGTH = 6;
    private const EXPIRES_AFTER_MINUTES = 2;
    private const MAX_ATTEMPTS = 5;

    public function __construct(
        private readonly SmsIrService $smsIrService,
    ) {
    }

    /**
     * تولید کد جدید، ذخیره در دیتابیس و ارسال از طریق پیامک.
     * کدهای قبلیِ تایید‌نشده‌ی همان شماره غیرفعال (منقضی) می‌شوند تا فقط آخرین کد معتبر باشد.
     */
    public function sendCode(string $mobile): bool
    {
        MobileVerification::where('mobile', $mobile)
            ->whereNull('verified_at')
            ->update(['expires_at' => now()]);

        $code = (string) random_int(100000, 999999);

        MobileVerification::create([
            'mobile' => $mobile,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(self::EXPIRES_AFTER_MINUTES),
            'attempts' => 0,
        ]);

    //    return $this->smsIrService->sendVerificationCode($mobile, $code);
        logs('code: '.$code);
        return $code;
    }

    /**
     * بررسی کد وارد‌شده توسط کاربر.
     *
     * @return array{success: bool, message: string}
     */
    public function verifyCode(string $mobile, string $code): array
    {
        $verification = MobileVerification::where('mobile', $mobile)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $verification) {
            return ['success' => false, 'message' => 'کدی برای این شماره یافت نشد. دوباره درخواست کد دهید.'];
        }

        if ($verification->attempts >= self::MAX_ATTEMPTS) {
            return ['success' => false, 'message' => 'تعداد تلاش‌های مجاز به پایان رسیده است. دوباره درخواست کد دهید.'];
        }

        if ($verification->isExpired()) {
            return ['success' => false, 'message' => 'کد وارد‌شده منقضی شده است. دوباره درخواست کد دهید.'];
        }

        if (! hash_equals($verification->code, $code)) {
            $verification->increment('attempts');

            return ['success' => false, 'message' => 'کد وارد‌شده صحیح نیست.'];
        }

        $verification->update(['verified_at' => now()]);

        return ['success' => true, 'message' => 'کد با موفقیت تایید شد.'];
    }
}
