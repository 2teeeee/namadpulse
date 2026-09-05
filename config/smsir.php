<?php

return [
    'api_key' => env('SMSIR_API_KEY'),

    // شناسه قالب Verify که در پنل sms.ir برای «ورود با کد یکبار‌مصرف» ساخته‌اید
    // قالب باید یک پارامتر به نام Code داشته باشد.
    'login_template_id' => env('SMSIR_LOGIN_TEMPLATE_ID'),
];
