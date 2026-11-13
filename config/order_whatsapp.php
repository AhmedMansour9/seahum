<?php

/*
 * Message pre-filled when an admin clicks the WhatsApp button of an order.
 * Placeholders: {customer_name}, {items}, {total}, {delivery_to}.
 * WhatsApp bold text uses single asterisks: *text*.
 */
return [
    'confirmation_message' => <<<'MESSAGE'
مرحبًا {customer_name} ✨

طلبك من SEA HUM متحضّر وجاهز للتأكيد 🤍

إحنا بنقدّم لك أجود أنواع الكافيار والسالمون والمأكولات البحرية المختارة بعناية 🐟
جودة عالية، طزاجة مضمونة، وطعم يليق بذوقك 👌

🧾 *تفاصيل الطلب:*
{items}

💰 *الإجمالي:* {total}
📍 *التوصيل إلى:* {delivery_to}

📦 *تابع طلبك بسهولة!*
تقدر تتابع حالة الأوردر وتعرف آخر تحديثاته من خلال تطبيق SEA HUM، باستخدام نفس رقم الموبايل المسجل بيه طلبك 📲

✅ *ضمان الجودة:*
لو المنتج ما وصلش بالحالة اللي ترضيك، حقك الاستبدال أو الاسترجاع بكل سهولة.

🐟😍 كل اللي بتحبه من خيرات البحر في مكان واحد مع SEA HUM! 💙🌊

🦐 أسماك، سالمون، جمبري، كافيار، بوكسات مميزة وعروض حصرية 🎁

🎟️ *عرض الكافيار:* اطلب أي كافيار + أي منتج تاني من التطبيق، وخد *خصم 200 ج* بكود *CAVIAR200*

📲 حمّل التطبيق وتابع طلبك واكتشف منتجاتنا وعروضنا:
🤖 *Android:*
https://play.google.com/store/apps/details?id=com.seahum
🍎 *iPhone:*
https://apps.apple.com/app/id6825504333

SEA HUM – Taste The Best Of The Sea 💙🌊
MESSAGE,

    'item_line' => "{name}:\nعدد القطع: {qty}\n",

    'currency_label' => 'ج.م',
];
