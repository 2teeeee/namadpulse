<?php

return [
    // مسیر پوشه‌ی فایل‌های txt تعدیل‌شده؛ هر فایل با نام name_en نماد + .txt
    'daily_prices_path' => env(
        'DAILY_PRICES_PATH',
        public_path('NoavaranAmin/NoavaranAmin/ExportedFile/Adj')
    ),

    // تعداد ردیف در هر batch برای upsert (تعادل بین سرعت و مصرف حافظه)
    'import_chunk_size' => (int) env('DAILY_PRICES_IMPORT_CHUNK', 500),
];
