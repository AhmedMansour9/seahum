<?php

/*
| Governorates an order can be assigned to by the admin or customer service.
| Greater Cairo (Cairo and Giza) is delivered directly, so it is not listed.
*/
return [
    // Day boundaries of postponed orders follow the business day, whatever app.timezone is.
    'timezone' => 'Africa/Cairo',

    'governorates' => [
        'الإسكندرية', 'القليوبية', 'الشرقية', 'الدقهلية', 'الغربية', 'المنوفية', 'البحيرة', 'كفر الشيخ',
        'دمياط', 'بورسعيد', 'الإسماعيلية', 'السويس', 'شمال سيناء', 'جنوب سيناء', 'الفيوم', 'بني سويف',
        'المنيا', 'أسيوط', 'سوهاج', 'قنا', 'الأقصر', 'أسوان', 'البحر الأحمر', 'الوادي الجديد', 'مطروح',
    ],
];
