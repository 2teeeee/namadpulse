<?php

namespace Database\Seeders;

use App\Models\SymbolGroup;
use Illuminate\Database\Seeder;

class SymbolGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            ['name' => 'فلزات اساسی', 'code' => 'BASIC_METALS'],
            ['name' => 'فراورده های نفتی، کک و سوخت هسته ای', 'code' => 'PETROLEUM_COKE_NUCLEAR'],
            ['name' => 'محصولات غذایی', 'code' => 'FOOD_PRODUCTS'],
            ['name' => 'ساخت محصولات فلزی', 'code' => 'FABRICATED_METALS'],
            ['name' => 'استخراج زغال سنگ', 'code' => 'COAL_MINING'],
            ['name' => 'حمل ونقل، انبارداری و ارتباطات', 'code' => 'TRANSPORT_WAREHOUSING_COMM'],
            ['name' => 'استخراج نفت گاز و خدمات جنبی جز اکتشاف', 'code' => 'OIL_GAS_EXTRACTION_SERVICES'],
            ['name' => 'استخراج کانه های فلزی', 'code' => 'METAL_ORE_MINING'],
            ['name' => 'اطلاعات و ارتباطات', 'code' => 'INFO_COMMUNICATIONS'],
            ['name' => 'انبوه سازی', 'code' => 'REAL_ESTATE_CONSTRUCTION'],
            ['name' => 'انتشار، چاپ و تکثیر', 'code' => 'PUBLISHING_PRINTING'],
            ['name' => 'بانکها و موسسات اعتباری', 'code' => 'BANKS_CREDIT_INSTITUTIONS'],
            ['name' => 'بیمه وصندوق بازنشستگی به جزتامین اجتماعی', 'code' => 'INSURANCE_PENSION_FUNDS'],
            ['name' => 'فعالیتهای کمکی به نهادهای مالی واسط', 'code' => 'FINANCIAL_AUXILIARY_SERVICES'],
            ['name' => 'حمل و نقل آبی', 'code' => 'WATER_TRANSPORT'],
            ['name' => 'خدمات فنی و مهندسی', 'code' => 'TECHNICAL_ENGINEERING_SERVICES'],
            ['name' => 'خرده فروشی،باستثنای وسایل نقلیه موتوری', 'code' => 'RETAIL_TRADE'],
            ['name' => 'خودرو و ساخت قطعات', 'code' => 'AUTOMOTIVE_PARTS'],
            ['name' => 'رایانه و فعالیت‌های وابسته به آن', 'code' => 'COMPUTER_RELATED_ACTIVITIES'],
            ['name' => 'زراعت و خدمات وابسته', 'code' => 'AGRICULTURE_SERVICES'],
            ['name' => 'ساخت دستگاه‌ها و وسایل ارتباطی', 'code' => 'COMMUNICATION_EQUIPMENT'],
            ['name' => 'ماشین آلات و دستگاه‌های برقی', 'code' => 'ELECTRICAL_MACHINERY'],
            ['name' => 'استخراج سایر معادن', 'code' => 'OTHER_MINING'],
            ['name' => 'سرمایه گذاریها', 'code' => 'INVESTMENT_COMPANIES'],
            ['name' => 'سیمان، آهک و گچ', 'code' => 'CEMENT_LIME_GYPSUM'],
            ['name' => 'پیمانکاری صنعتی', 'code' => 'INDUSTRIAL_CONTRACTING'],
            ['name' => 'چند رشته ای صنعتی', 'code' => 'DIVERSIFIED_INDUSTRIALS'],
            ['name' => 'صنایع چرم', 'code' => 'LEATHER_INDUSTRIES'],
            ['name' => 'صنایع دارویی', 'code' => 'PHARMACEUTICALS'],
            ['name' => 'صنایع نیروگاهی', 'code' => 'POWER_PLANT_INDUSTRIES'],
            ['name' => 'صندو ق های مختلط و فراصندوق ها', 'code' => 'MIXED_FUNDS'],
            ['name' => 'صندوق های درآمد ثابت', 'code' => 'FIXED_INCOME_FUNDS'],
            ['name' => 'صندوق های سهامی', 'code' => 'EQUITY_FUNDS'],
            ['name' => 'صندوق های کالایی', 'code' => 'COMMODITY_FUNDS'],
            ['name' => 'عمده فروشی', 'code' => 'WHOLESALE_TRADE'],
            ['name' => 'فرهنگی و ورزشی', 'code' => 'CULTURAL_SPORTS'],
            ['name' => 'قند و شکر', 'code' => 'SUGAR'],
            ['name' => 'کاشی و سرامیک', 'code' => 'TILE_CERAMICS'],
            ['name' => 'کانی های غیرفلزی', 'code' => 'NONMETALLIC_MINERALS'],
            ['name' => 'لاستیک و پلاستیک', 'code' => 'RUBBER_PLASTIC'],
            ['name' => 'لیزینگ', 'code' => 'LEASING'],
            ['name' => 'ماشین آلات و تجهیزات', 'code' => 'MACHINERY_EQUIPMENT'],
            ['name' => 'محصولات چوبی', 'code' => 'WOOD_PRODUCTS'],
            ['name' => 'محصولات شیمیایی', 'code' => 'CHEMICAL_PRODUCTS'],
            ['name' => 'محصولات کاغذی', 'code' => 'PAPER_PRODUCTS'],
            ['name' => 'محصولات کامپیوتری', 'code' => 'COMPUTER_PRODUCTS'],
            ['name' => 'مخابرات', 'code' => 'TELECOMMUNICATIONS'],
            ['name' => 'نساجی', 'code' => 'TEXTILES'],
            ['name' => 'هتل و رستوران', 'code' => 'HOTEL_RESTAURANT'],
            ['name' => 'هنری و سرگرمی', 'code' => 'ARTS_ENTERTAINMENT'],
            ['name' => 'واسطه‌گری‌های مالی', 'code' => 'FINANCIAL_INTERMEDIATION'],
            ['name' => 'بدون گروه', 'code' => 'UNGROUPED'],
            ['name' => 'قیمت های کالا', 'code' => 'COMMODITY_PRICES'],
            ['name' => 'شاخص ها', 'code' => 'INDICES'],
        ];

        foreach ($groups as $group) {
            SymbolGroup::updateOrCreate(['code' => $group['code']], $group);
        }
    }
}
