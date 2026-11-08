<?php

/*
 * Message pre-filled when an admin clicks the WhatsApp button of an order.
 * Placeholders: {customer_name}, {items}, {total}, {delivery_to}.
 * WhatsApp bold text uses single asterisks: *text*.
 */
return [
    'confirmation_message' => <<<'MESSAGE'
مرحبًا {customer_name} ✨

طلبك من sea-hum متحضّر وجاهز للتأكيد 🤍

إحنا بنقدّم لك أجود أنواع الكفيار والسالمون والمأكولات البحرية المختارة بعناية 🐟
جودة عالية، طزاجة مضمونة، وطعم يليق بذوقك 👌

🧾 *تفاصيل الطلب:*
{items}

💰 *الإجمالي:* {total}
📍 *التوصيل إلى:* {delivery_to}


✅ *ضمان الجودة:*
لو المنتج ما وصلش بالحالة اللي ترضيك، حقك الاستبدال أو الاسترجاع بكل سهولة.

🐟😍 كل اللي بتحبوه من خيرات البحر في مكان واحد مع Sea Hum! 💙🌊

🦐 جميع أنواع الأسماك والسلمون والجمبري والكافيار، وكمان بوكسات مميزة بعروض حصرية! 🎁

🎁 ومع أوردراتكم، مستنيينكم هدايا مميزة كمان! 😍

📲 حمّلوا الأبليكيشن واكتشفوا منتجاتنا وعروضنا من هنا 👇
https://play.google.com/store/apps/details?id=com.seahum
https://apps.apple.com/app/id6825504333
MESSAGE,

    'item_line' => "{name}:\nعدد القطع: {qty}\n",

    'currency_label' => 'ج.م',
];
