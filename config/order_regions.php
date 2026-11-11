<?php

/*
| An order is a "governorate order" when its city/state is filled and contains none of these
| keywords (case-insensitive). Add any Greater Cairo area name that wrongly shows up as a governorate.
*/
return [
    'greater_cairo_keywords' => [
        'cairo', 'giza', 'القاهرة', 'القاهره', 'الجيزة', 'الجيزه',
        'nasr', 'نصر', 'maadi', 'المعادي', 'heliopolis', 'مصر الجديدة', 'tagamo', 'التجمع',
        'shorouk', 'الشروق', 'obour', 'العبور', 'october', 'أكتوبر', 'اكتوبر', 'zayed', 'زايد',
        'helwan', 'حلوان', 'ain shams', 'عين شمس', 'mohandess', 'المهندسين', 'dokki', 'الدقي',
        'haram', 'الهرم', 'faisal', 'فيصل', '15 may', '15 مايو', 'badr', 'بدر', 'rehab', 'الرحاب',
        'madinaty', 'مدينتي', 'capital', 'العاصمة', 'shubra', 'شبرا', 'zamalek', 'الزمالك',
        'mokattam', 'المقطم', 'matareya', 'المطرية', 'marg', 'المرج', 'sheraton', 'الشيراتون',
    ],
];
