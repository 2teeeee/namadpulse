<?php

return [
    // حداقل درصد بازگشت قیمت برای تایید یک سقف/کف در اندیکاتور زیگزاگ ماژور
    'zigzag_threshold_percent' => (float) env('ZIGZAG_THRESHOLD_PERCENT', 8.0),

    // ارزش اسمی هر سهم (ریال) برای محاسبه‌ی سرمایه شرکت
    'nominal_share_value' => (int) env('NOMINAL_SHARE_VALUE', 1000),
];
