<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsIrService
{
    private const VERIFY_ENDPOINT = 'https://api.sms.ir/v1/send/verify';

    public function __construct(
        private readonly ?string $apiKey = null,
        private readonly ?int $loginTemplateId = null,
    ) {
    }

    /**
     * ارسال کد یکبار‌مصرف از طریق قالب Verify پنل sms.ir
     */
    public function sendVerificationCode(string $mobile, string $code): bool
    {
        $apiKey = $this->apiKey ?? (string) config('smsir.api_key');
        $templateId = $this->loginTemplateId ?? (int) config('smsir.login_template_id');

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'Accept' => 'application/json',
        ])->post(self::VERIFY_ENDPOINT, [
            'mobile' => $mobile,
            'templateId' => $templateId,
            'parameters' => [
                ['name' => 'Code', 'value' => $code],
            ],
        ]);

        if (! $response->successful() || (int) ($response->json('status')) !== 1) {
            Log::warning('sms.ir: ارسال کد تایید ناموفق بود', [
                'mobile' => $mobile,
                'response' => $response->json(),
            ]);

            return false;
        }

        return true;
    }
}
