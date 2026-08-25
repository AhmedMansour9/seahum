<?php

/*
|--------------------------------------------------------------------------
| Seafood demo catalogue
|--------------------------------------------------------------------------
|
| Deterministic demo data consumed by Database\Seeders\SeafoodCatalogSeeder
| and Database\Seeders\SeafoodCatalogSupportSeeder.
|
| Prices are demo catalogue prices in the system default currency (AED) per
| unit. They are illustrative retail figures, not live market quotations.
|
| Every product is keyed by its SKU ('sku'), which is the idempotency key of
| the seeder. Images are seed assets shipped in
| public/assets/front-end/img/seafood/products; a product only carries an
| image when the species it sells is actually visible in that photograph.
|
*/

return [

    'categories' => [
        [
            'slug' => 'fresh-fish',
            'priority' => 3,
            'image' => 'whole-fish.webp',
            'name' => ['en' => 'Fresh Fish', 'ar' => 'أسماك طازجة'],
            'children' => [
                ['slug' => 'whole-fish', 'image' => 'sea-bass.webp', 'name' => ['en' => 'Whole Fish', 'ar' => 'أسماك كاملة']],
                ['slug' => 'fish-fillets', 'image' => 'fillets.webp', 'name' => ['en' => 'Fish Fillets', 'ar' => 'فيليه السمك']],
                ['slug' => 'fish-steaks-and-portions', 'image' => 'fillets.webp', 'name' => ['en' => 'Steaks & Portions', 'ar' => 'شرائح وقطع']],
            ],
        ],
        [
            'slug' => 'shellfish',
            'priority' => 2,
            'image' => 'crab-lobster.webp',
            'name' => ['en' => 'Shellfish', 'ar' => 'قشريات ومحار'],
            'children' => [
                ['slug' => 'shrimp-and-prawns', 'image' => 'shrimp.webp', 'name' => ['en' => 'Shrimp & Prawns', 'ar' => 'روبيان وجمبري']],
                ['slug' => 'crab-and-lobster', 'image' => 'crab-lobster.webp', 'name' => ['en' => 'Crab & Lobster', 'ar' => 'سرطان البحر والاستاكوزا']],
                ['slug' => 'mussels-and-clams', 'image' => 'mussels-octopus.webp', 'name' => ['en' => 'Mussels & Clams', 'ar' => 'بلح البحر والمحار']],
            ],
        ],
        [
            'slug' => 'squid-and-octopus',
            'priority' => 1,
            'image' => 'squid.webp',
            'name' => ['en' => 'Squid & Octopus', 'ar' => 'حبار وأخطبوط'],
            'children' => [
                ['slug' => 'squid-and-calamari', 'image' => 'squid.webp', 'name' => ['en' => 'Squid & Calamari', 'ar' => 'حبار وكاليماري']],
                ['slug' => 'octopus', 'image' => 'mussels-octopus.webp', 'name' => ['en' => 'Octopus', 'ar' => 'أخطبوط']],
            ],
        ],
    ],

    'attributes' => ['Cut', 'Pack Size', 'Preparation'],

    'products' => [

        /* ---------------------------------------------------------------
         | Fresh fish · whole
         --------------------------------------------------------------- */

        [
            'sku' => 'BW-FSH-SBASS-01',
            'slug' => 'fresh-whole-sea-bass',
            'category' => 'fresh-fish',
            'sub_category' => 'whole-fish',
            'image' => 'sea-bass-whole.webp',
            'unit' => 'kg',
            'price' => 45.00,
            'compare_at' => 52.00,
            'stock' => 80,
            'name' => ['en' => 'Fresh Whole Sea Bass', 'ar' => 'سمك القاروص الطازج الكامل'],
            'short' => [
                'en' => 'Silver-skinned whole sea bass with firm white flesh and a clean, mild flavour — our best seller for the oven and the grill.',
                'ar' => 'سمك قاروص كامل بقشرة فضية ولحم أبيض متماسك ونكهة هادئة نظيفة — الخيار الأول لدينا للشوي والفرن.',
            ],
            'intro' => [
                'en' => 'Sea bass arrives whole and unfrozen, scaled and gutted on request, so you can judge its freshness yourself: bright eyes, red gills and firm flesh that springs back to the touch. The meat cooks to large white flakes that hold their shape, which makes it equally reliable stuffed and baked whole, butterflied over charcoal, or filleted for a pan sear.',
                'ar' => 'يصلك القاروص كاملاً غير مجمّد، ويُقشّر ويُنظّف حسب الطلب، لتتحقق بنفسك من طزاجته: عين صافية وخياشيم حمراء ولحم متماسك يعود لوضعه عند لمسه. يتحول لحمه بعد الطهي إلى رقائق بيضاء كبيرة تحافظ على شكلها، ما يجعله مناسباً للحشو والخبز كاملاً، أو للشوي على الفحم، أو للتقطيع فيليه وقليه سريعاً في المقلاة.',
            ],
            'spec' => [
                'species' => ['en' => 'European sea bass', 'ar' => 'القاروص الأوروبي'],
                'scientific' => 'Dicentrarchus labrax',
                'origin' => ['en' => 'Farmed in Türkiye', 'ar' => 'مستزرع في تركيا'],
                'source' => ['en' => 'Farmed', 'ar' => 'استزراع سمكي'],
                'condition' => ['en' => 'Fresh, chilled on ice', 'ar' => 'طازج، محفوظ على الثلج'],
                'cut' => ['en' => 'Whole, cleaned or filleted to order', 'ar' => 'كامل أو منظّف أو فيليه حسب الطلب'],
                'packaging' => ['en' => 'Packed on flake ice in an insulated box', 'ar' => 'يُعبأ على ثلج مجروش داخل صندوق معزول'],
                'weight' => ['en' => '400 g – 800 g per fish', 'ar' => '400 – 800 جم للسمكة الواحدة'],
            ],
            'storage' => [
                'en' => 'Keep on ice at 0–4 °C and cook within 24 hours of delivery. To freeze, pat the fish dry, wrap it tightly and store at −18 °C for up to three months.',
                'ar' => 'يُحفظ على الثلج عند 0–4 مئوية ويُطهى خلال 24 ساعة من الاستلام. للتجميد: جفّف السمكة جيداً ولفّها بإحكام واحفظها عند −18 مئوية لمدة تصل إلى ثلاثة أشهر.',
            ],
            'cooking' => [
                'en' => [
                    'Stuff with lemon, garlic and thyme and bake at 200 °C for 20–25 minutes.',
                    'Score the skin, brush with olive oil and grill over charcoal until the skin blisters.',
                    'Fillet and sear skin-side down for a crisp finish, then serve with a squeeze of lime.',
                ],
                'ar' => [
                    'يُحشى بالليمون والثوم والزعتر ويُخبز على 200 مئوية لمدة 20–25 دقيقة.',
                    'اعمل شقوقاً في الجلد وادهنه بزيت الزيتون واشوه على الفحم حتى يصبح الجلد مقرمشاً.',
                    'قطّعه فيليه واقله على جهة الجلد أولاً للحصول على قوام مقرمش، ثم قدّمه مع عصير الليمون.',
                ],
            ],
            'nutrition' => [
                'en' => 'A lean white fish: naturally high in protein, low in saturated fat and a useful source of omega-3 fatty acids.',
                'ar' => 'سمك أبيض قليل الدهون: غني بالبروتين بطبيعته، منخفض الدهون المشبعة، ومصدر مفيد لأحماض أوميغا-3 الدهنية.',
            ],
            'seo' => [
                'title' => 'Fresh Whole Sea Bass | Cleaned or Filleted to Order',
                'description' => 'Fresh whole sea bass, 400–800 g per fish, chilled on ice and cleaned or filleted to order. Firm white flesh with a mild flavour, ideal for baking and grilling.',
            ],
            'tags' => ['sea bass', 'whole fish', 'grilling fish'],
            'variants' => [
                'axes' => ['Cut' => ['Whole', 'Cleaned', 'Fillet']],
                'combinations' => [
                    'Whole' => ['price' => 45.00, 'qty' => 40, 'sku' => 'BW-FSH-SBASS-01-WHL'],
                    'Cleaned' => ['price' => 49.00, 'qty' => 30, 'sku' => 'BW-FSH-SBASS-01-CLN'],
                    'Fillet' => ['price' => 85.00, 'qty' => 10, 'sku' => 'BW-FSH-SBASS-01-FLT'],
                ],
            ],
        ],

        [
            'sku' => 'BW-FSH-SBREAM-02',
            'slug' => 'fresh-whole-sea-bream',
            'category' => 'fresh-fish',
            'sub_category' => 'whole-fish',
            'image' => 'sea-bream-whole.webp',
            'unit' => 'kg',
            'price' => 42.00,
            'compare_at' => null,
            'stock' => 75,
            'name' => ['en' => 'Fresh Whole Sea Bream', 'ar' => 'سمك الدنيس الطازج الكامل'],
            'short' => [
                'en' => 'Gilt-head bream with sweet, delicate flesh and a golden band between the eyes — a table fish that needs very little help.',
                'ar' => 'سمك دنيس بلحم رقيق وطعم حلو مميز وشريط ذهبي بين العينين — سمكة مائدة لا تحتاج إلى الكثير من التوابل.',
            ],
            'intro' => [
                'en' => 'Gilt-head bream is prized for flesh that is finer and slightly sweeter than sea bass, with a thin skin that crisps beautifully. Because the fillets are delicate, we recommend cooking bream whole: salt-baked, roasted on a bed of potatoes, or grilled with nothing more than olive oil, sea salt and lemon.',
                'ar' => 'يتميز الدنيس بلحم أنعم وأحلى قليلاً من القاروص، وبجلد رقيق يصبح مقرمشاً عند الشوي. ولأن شرائحه رقيقة، ننصح بطهيه كاملاً: مدفوناً في الملح، أو محمّراً فوق طبقة من البطاطس، أو مشوياً بزيت الزيتون وملح البحر والليمون فقط.',
            ],
            'spec' => [
                'species' => ['en' => 'Gilt-head sea bream', 'ar' => 'الدنيس ذهبي الرأس'],
                'scientific' => 'Sparus aurata',
                'origin' => ['en' => 'Farmed in Türkiye', 'ar' => 'مستزرع في تركيا'],
                'source' => ['en' => 'Farmed', 'ar' => 'استزراع سمكي'],
                'condition' => ['en' => 'Fresh, chilled on ice', 'ar' => 'طازج، محفوظ على الثلج'],
                'cut' => ['en' => 'Whole or cleaned to order', 'ar' => 'كامل أو منظّف حسب الطلب'],
                'packaging' => ['en' => 'Packed on flake ice in an insulated box', 'ar' => 'يُعبأ على ثلج مجروش داخل صندوق معزول'],
                'weight' => ['en' => '350 g – 700 g per fish', 'ar' => '350 – 700 جم للسمكة الواحدة'],
            ],
            'storage' => [
                'en' => 'Store on ice at 0–4 °C and cook within 24 hours. Bream freezes well whole at −18 °C for up to three months.',
                'ar' => 'يُحفظ على الثلج عند 0–4 مئوية ويُطهى خلال 24 ساعة. يمكن تجميد الدنيس كاملاً عند −18 مئوية لمدة تصل إلى ثلاثة أشهر.',
            ],
            'cooking' => [
                'en' => [
                    'Bake whole in a coarse salt crust for 25 minutes to keep the flesh moist.',
                    'Roast over sliced potatoes, onion and rosemary at 190 °C.',
                    'Grill whole and finish with olive oil, lemon and flat-leaf parsley.',
                ],
                'ar' => [
                    'اخبزه كاملاً داخل قشرة من الملح الخشن لمدة 25 دقيقة للحفاظ على طراوة اللحم.',
                    'حمّره فوق شرائح البطاطس والبصل وإكليل الجبل على حرارة 190 مئوية.',
                    'اشوه كاملاً وقدّمه مع زيت الزيتون والليمون والبقدونس.',
                ],
            ],
            'nutrition' => [
                'en' => 'Lean and protein-rich, with a moderate fat content that keeps the flesh moist during cooking.',
                'ar' => 'قليل الدهون وغني بالبروتين، ومحتواه الدهني المعتدل يحافظ على طراوة اللحم أثناء الطهي.',
            ],
            'seo' => [
                'title' => 'Fresh Whole Sea Bream (Gilt-head) | Chilled on Ice',
                'description' => 'Fresh gilt-head sea bream, 350–700 g per fish, cleaned to order and delivered chilled on ice. Sweet, delicate flesh that is at its best cooked whole.',
            ],
            'tags' => ['sea bream', 'whole fish', 'grilling fish'],
            'variants' => [
                'axes' => ['Cut' => ['Whole', 'Cleaned']],
                'combinations' => [
                    'Whole' => ['price' => 42.00, 'qty' => 45, 'sku' => 'BW-FSH-SBREAM-02-WHL'],
                    'Cleaned' => ['price' => 46.00, 'qty' => 30, 'sku' => 'BW-FSH-SBREAM-02-CLN'],
                ],
            ],
        ],

        [
            'sku' => 'BW-FSH-MACK-03',
            'slug' => 'fresh-atlantic-mackerel',
            'category' => 'fresh-fish',
            'sub_category' => 'whole-fish',
            'image' => 'whole-fish-on-ice.webp',
            'unit' => 'kg',
            'price' => 28.00,
            'compare_at' => 34.00,
            'stock' => 90,
            'name' => ['en' => 'Fresh Atlantic Mackerel', 'ar' => 'سمك الماكريل الأطلسي الطازج'],
            'short' => [
                'en' => 'Rich, oily mackerel with tiger-striped skin — strong enough in flavour to stand up to charcoal, chilli and vinegar.',
                'ar' => 'ماكريل دسم بجلد مخطط لامع — نكهته القوية تتحمل الفحم والفلفل الحار والخل.',
            ],
            'intro' => [
                'en' => 'Mackerel is a fast-moving oily fish with dark, full-flavoured flesh that pales as it cooks. It is one of the few fish that genuinely improves over live coals, and its natural oil means it rarely dries out. Buy it as fresh as possible: mackerel is at its best on the day it is delivered.',
                'ar' => 'الماكريل سمك دسم سريع الحركة، لحمه داكن قوي النكهة يفتح لونه عند الطهي. وهو من الأسماك القليلة التي تتحسن فعلاً على الفحم، وزيته الطبيعي يمنعه من الجفاف. اشترِه طازجاً قدر الإمكان: فالماكريل في أفضل حالاته يوم استلامه.',
            ],
            'spec' => [
                'species' => ['en' => 'Atlantic mackerel', 'ar' => 'الماكريل الأطلسي'],
                'scientific' => 'Scomber scombrus',
                'origin' => ['en' => 'North-East Atlantic', 'ar' => 'شمال شرق المحيط الأطلسي'],
                'source' => ['en' => 'Wild-caught', 'ar' => 'صيد بري'],
                'condition' => ['en' => 'Fresh, chilled on ice', 'ar' => 'طازج، محفوظ على الثلج'],
                'cut' => ['en' => 'Whole or cleaned to order', 'ar' => 'كامل أو منظّف حسب الطلب'],
                'packaging' => ['en' => 'Layered on ice in an insulated box', 'ar' => 'يُرصّ على الثلج داخل صندوق معزول'],
                'weight' => ['en' => '300 g – 500 g per fish', 'ar' => '300 – 500 جم للسمكة الواحدة'],
            ],
            'storage' => [
                'en' => 'Oily fish spoils faster than white fish: keep at 0–4 °C and cook the same day. Freeze at −18 °C for no longer than two months.',
                'ar' => 'الأسماك الدسمة تفسد أسرع من البيضاء: احفظه عند 0–4 مئوية واطهه في اليوم نفسه. عند التجميد لا تتجاوز شهرين عند −18 مئوية.',
            ],
            'cooking' => [
                'en' => [
                    'Butterfly and grill over charcoal for three minutes a side.',
                    'Roast whole with sliced onion, sumac and lemon.',
                    'Fillet and cure briefly in vinegar and salt for a sharp cold starter.',
                ],
                'ar' => [
                    'افتحه فراشة واشوه على الفحم ثلاث دقائق لكل جهة.',
                    'حمّره كاملاً مع شرائح البصل والسماق والليمون.',
                    'قطّعه فيليه وتبّله بالخل والملح لفترة قصيرة كمقبل بارد حاد النكهة.',
                ],
            ],
            'nutrition' => [
                'en' => 'One of the richest common sources of long-chain omega-3 fatty acids, and a good source of protein and vitamin B12.',
                'ar' => 'من أغنى الأسماك الشائعة بأحماض أوميغا-3 طويلة السلسلة، ومصدر جيد للبروتين وفيتامين ب12.',
            ],
            'seo' => [
                'title' => 'Fresh Atlantic Mackerel | Whole, Chilled on Ice',
                'description' => 'Fresh Atlantic mackerel, 300–500 g per fish, cleaned to order. A rich, oily fish that is at its best grilled over charcoal.',
            ],
            'tags' => ['mackerel', 'oily fish', 'omega 3'],
            'variants' => [
                'axes' => ['Cut' => ['Whole', 'Cleaned']],
                'combinations' => [
                    'Whole' => ['price' => 28.00, 'qty' => 55, 'sku' => 'BW-FSH-MACK-03-WHL'],
                    'Cleaned' => ['price' => 32.00, 'qty' => 35, 'sku' => 'BW-FSH-MACK-03-CLN'],
                ],
            ],
        ],

        [
            'sku' => 'BW-FSH-SNAP-04',
            'slug' => 'fresh-red-snapper',
            'category' => 'fresh-fish',
            'sub_category' => 'whole-fish',
            'image' => 'whole-fish-on-ice.webp',
            'unit' => 'kg',
            'price' => 55.00,
            'compare_at' => null,
            'stock' => 50,
            'name' => ['en' => 'Fresh Red Snapper', 'ar' => 'سمك السنابر الأحمر الطازج'],
            'short' => [
                'en' => 'Rose-pink snapper with firm, slightly sweet flesh that holds together in a curry or a tray bake.',
                'ar' => 'سنابر وردي اللون بلحم متماسك وطعم حلو خفيف يحافظ على تماسكه في الكاري وصواني الفرن.',
            ],
            'intro' => [
                'en' => 'Snapper is the fish to reach for when a recipe has to cook for longer than a few minutes: the flesh is firm enough for curries, tagines and whole-fish tray bakes without breaking up. The skin is thin and edible, and the large frame gives generous fillets with few bones.',
                'ar' => 'السنابر هو الخيار الأمثل للوصفات التي تحتاج وقت طهي أطول: لحمه متماسك بما يكفي للكاري والطواجن وصواني السمك الكامل دون أن يتفتت. جلده رقيق صالح للأكل، وحجمه الكبير يعطي شرائح فيليه وافرة قليلة الشوك.',
            ],
            'spec' => [
                'species' => ['en' => 'Snapper', 'ar' => 'السنابر'],
                'scientific' => 'Lutjanus spp.',
                'origin' => ['en' => 'Indian Ocean', 'ar' => 'المحيط الهندي'],
                'source' => ['en' => 'Wild-caught', 'ar' => 'صيد بري'],
                'condition' => ['en' => 'Fresh, chilled on ice', 'ar' => 'طازج، محفوظ على الثلج'],
                'cut' => ['en' => 'Whole, cleaned or filleted to order', 'ar' => 'كامل أو منظّف أو فيليه حسب الطلب'],
                'packaging' => ['en' => 'Packed on flake ice in an insulated box', 'ar' => 'يُعبأ على ثلج مجروش داخل صندوق معزول'],
                'weight' => ['en' => '800 g – 1.5 kg per fish', 'ar' => '800 جم – 1.5 كجم للسمكة الواحدة'],
            ],
            'storage' => [
                'en' => 'Keep on ice at 0–4 °C and use within 24 hours. Fillets freeze well at −18 °C for up to three months.',
                'ar' => 'يُحفظ على الثلج عند 0–4 مئوية ويُستخدم خلال 24 ساعة. تتحمل شرائح الفيليه التجميد عند −18 مئوية لمدة تصل إلى ثلاثة أشهر.',
            ],
            'cooking' => [
                'en' => [
                    'Score, marinate in tamarind and chilli, then bake whole at 200 °C.',
                    'Cut into chunks for a coconut fish curry — the flesh stays intact.',
                    'Grill fillets skin-side down and finish with garlic butter.',
                ],
                'ar' => [
                    'اعمل شقوقاً في الجلد وتبّله بالتمر الهندي والفلفل الحار ثم اخبزه كاملاً على 200 مئوية.',
                    'قطّعه مكعبات لكاري السمك بجوز الهند — يحافظ لحمه على تماسكه.',
                    'اشوِ شرائح الفيليه على جهة الجلد وقدّمها مع زبدة الثوم.',
                ],
            ],
            'nutrition' => [
                'en' => 'A lean, high-protein white fish with a low fat content and a good level of selenium.',
                'ar' => 'سمك أبيض قليل الدهون وعالي البروتين، ومصدر جيد لعنصر السيلينيوم.',
            ],
            'seo' => [
                'title' => 'Fresh Red Snapper | Whole or Filleted',
                'description' => 'Fresh red snapper, 800 g – 1.5 kg per fish, cleaned or filleted to order. Firm flesh that holds its shape in curries and tray bakes.',
            ],
            'tags' => ['snapper', 'whole fish', 'white fish'],
            'variants' => [
                'axes' => ['Cut' => ['Whole', 'Cleaned', 'Fillet']],
                'combinations' => [
                    'Whole' => ['price' => 55.00, 'qty' => 25, 'sku' => 'BW-FSH-SNAP-04-WHL'],
                    'Cleaned' => ['price' => 59.00, 'qty' => 15, 'sku' => 'BW-FSH-SNAP-04-CLN'],
                    'Fillet' => ['price' => 98.00, 'qty' => 10, 'sku' => 'BW-FSH-SNAP-04-FLT'],
                ],
            ],
        ],

        [
            'sku' => 'BW-FSH-TILA-05',
            'slug' => 'fresh-whole-tilapia',
            'category' => 'fresh-fish',
            'sub_category' => 'whole-fish',
            'image' => null,
            'unit' => 'kg',
            'price' => 24.00,
            'compare_at' => 28.00,
            'stock' => 120,
            'name' => ['en' => 'Fresh Whole Tilapia', 'ar' => 'سمك البلطي الطازج الكامل'],
            'short' => [
                'en' => 'Everyday farmed tilapia with mild white flesh — the dependable choice for frying and family-sized tray bakes.',
                'ar' => 'بلطي مستزرع للاستهلاك اليومي بلحم أبيض هادئ الطعم — الخيار المضمون للقلي وصواني الفرن العائلية.',
            ],
            'intro' => [
                'en' => 'Tilapia is the most economical fish on the counter and the easiest to sell to fussy eaters: the flavour is mild, the bones are large and easy to find, and the flesh takes on whatever spice you put on it. It is best cooked hot and fast — deep-fried in a cumin and coriander crust, or baked in a tray with tomato and cumin.',
                'ar' => 'البلطي هو أكثر الأسماك اقتصاداً وأسهلها قبولاً لدى من لا يحبون طعم السمك القوي: نكهته هادئة، وشوكه كبير سهل التمييز، ولحمه يتشرب أي توابل تضاف إليه. ويُفضّل طهيه على حرارة عالية وسريعة — مقلياً بقشرة من الكمون والكزبرة، أو مخبوزاً في صينية مع الطماطم والكمون.',
            ],
            'spec' => [
                'species' => ['en' => 'Nile tilapia', 'ar' => 'البلطي النيلي'],
                'scientific' => 'Oreochromis niloticus',
                'origin' => ['en' => 'Farmed in Egypt', 'ar' => 'مستزرع في مصر'],
                'source' => ['en' => 'Farmed', 'ar' => 'استزراع سمكي'],
                'condition' => ['en' => 'Fresh, chilled on ice', 'ar' => 'طازج، محفوظ على الثلج'],
                'cut' => ['en' => 'Whole, cleaned or filleted to order', 'ar' => 'كامل أو منظّف أو فيليه حسب الطلب'],
                'packaging' => ['en' => 'Packed on ice in an insulated box', 'ar' => 'يُعبأ على الثلج داخل صندوق معزول'],
                'weight' => ['en' => '400 g – 700 g per fish', 'ar' => '400 – 700 جم للسمكة الواحدة'],
            ],
            'storage' => [
                'en' => 'Keep chilled at 0–4 °C and cook within 24 hours. Clean and freeze at −18 °C for up to three months.',
                'ar' => 'يُحفظ مبرداً عند 0–4 مئوية ويُطهى خلال 24 ساعة. نظّفه قبل التجميد واحفظه عند −18 مئوية لمدة تصل إلى ثلاثة أشهر.',
            ],
            'cooking' => [
                'en' => [
                    'Score, dust with seasoned flour and deep-fry until the skin crackles.',
                    'Bake in a tray with tomato, garlic, cumin and green chilli.',
                    'Grill whole and serve with tahini sauce and warm bread.',
                ],
                'ar' => [
                    'اعمل شقوقاً في الجلد وغلّفه بالدقيق المتبّل واقله بزيت غزير حتى يقرمش الجلد.',
                    'اخبزه في صينية مع الطماطم والثوم والكمون والفلفل الأخضر.',
                    'اشوه كاملاً وقدّمه مع صلصة الطحينة والخبز الساخن.',
                ],
            ],
            'nutrition' => [
                'en' => 'A lean source of protein with a low fat content, which is why it suits light, everyday cooking.',
                'ar' => 'مصدر للبروتين قليل الدهون، لذلك يناسب الطهي اليومي الخفيف.',
            ],
            'seo' => [
                'title' => 'Fresh Whole Tilapia | Cleaned or Filleted to Order',
                'description' => 'Fresh farmed tilapia, 400–700 g per fish, cleaned or filleted to order. Mild white flesh that suits frying, grilling and tray bakes.',
            ],
            'tags' => ['tilapia', 'whole fish', 'white fish'],
            'variants' => [
                'axes' => ['Cut' => ['Whole', 'Cleaned', 'Fillet']],
                'combinations' => [
                    'Whole' => ['price' => 24.00, 'qty' => 70, 'sku' => 'BW-FSH-TILA-05-WHL'],
                    'Cleaned' => ['price' => 27.00, 'qty' => 35, 'sku' => 'BW-FSH-TILA-05-CLN'],
                    'Fillet' => ['price' => 38.00, 'qty' => 15, 'sku' => 'BW-FSH-TILA-05-FLT'],
                ],
            ],
        ],

        [
            'sku' => 'BW-FSH-SARD-06',
            'slug' => 'fresh-sardines',
            'category' => 'fresh-fish',
            'sub_category' => 'whole-fish',
            'image' => null,
            'unit' => 'kg',
            'price' => 20.00,
            'compare_at' => null,
            'stock' => 100,
            'name' => ['en' => 'Fresh Sardines', 'ar' => 'السردين الطازج'],
            'short' => [
                'en' => 'Small silver sardines, sold by the kilo — cheap, intensely flavoured and made for the grill.',
                'ar' => 'سردين فضي صغير يُباع بالكيلو — اقتصادي وقوي النكهة ومثالي للشوي.',
            ],
            'intro' => [
                'en' => 'Sardines are the classic charcoal fish: a handful of salt, a very hot grill and four minutes are all they need. They are also the fish that rewards freshness most obviously, so we sell them the day they land and recommend cooking them the day you buy them.',
                'ar' => 'السردين هو سمك الفحم الكلاسيكي: قليل من الملح وشواية شديدة الحرارة وأربع دقائق فقط. وهو أكثر الأسماك التي تظهر فيها الطزاجة بوضوح، لذلك نبيعه في يوم وصوله وننصح بطهيه في يوم شرائه.',
            ],
            'spec' => [
                'species' => ['en' => 'European pilchard (sardine)', 'ar' => 'السردين الأوروبي'],
                'scientific' => 'Sardina pilchardus',
                'origin' => ['en' => 'Atlantic coast of Morocco', 'ar' => 'الساحل الأطلسي للمغرب'],
                'source' => ['en' => 'Wild-caught', 'ar' => 'صيد بري'],
                'condition' => ['en' => 'Fresh, chilled on ice', 'ar' => 'طازج، محفوظ على الثلج'],
                'cut' => ['en' => 'Whole, ungutted (cleaning available on request)', 'ar' => 'كامل غير منظّف (التنظيف متاح عند الطلب)'],
                'packaging' => ['en' => 'Loose on ice, 500 g or 1 kg packs', 'ar' => 'سائب على الثلج، بعبوات 500 جم أو 1 كجم'],
                'weight' => ['en' => '60 g – 100 g per fish', 'ar' => '60 – 100 جم للسمكة الواحدة'],
            ],
            'storage' => [
                'en' => 'Keep at 0–4 °C and cook the same day. Sardines lose quality quickly in the freezer; use within one month if frozen at −18 °C.',
                'ar' => 'يُحفظ عند 0–4 مئوية ويُطهى في اليوم نفسه. يفقد السردين جودته سريعاً في الفريزر؛ استخدمه خلال شهر واحد عند التجميد على −18 مئوية.',
            ],
            'cooking' => [
                'en' => [
                    'Salt generously and grill whole over very high heat, three to four minutes a side.',
                    'Butterfly, coat in semolina and shallow-fry until golden.',
                    'Bake packed tightly in a dish with lemon, garlic and olive oil.',
                ],
                'ar' => [
                    'ملّحه بسخاء واشوه كاملاً على نار عالية جداً، ثلاث إلى أربع دقائق لكل جهة.',
                    'افتحه فراشة وغلّفه بالسميد واقله في زيت قليل حتى يذهب لونه.',
                    'اخبزه مرصوصاً في طبق مع الليمون والثوم وزيت الزيتون.',
                ],
            ],
            'nutrition' => [
                'en' => 'An oily fish rich in omega-3 fatty acids, protein and vitamin B12; eaten with the soft bones it also provides calcium.',
                'ar' => 'سمك دسم غني بأحماض أوميغا-3 والبروتين وفيتامين ب12، وعند تناوله بعظامه الطرية يوفر أيضاً الكالسيوم.',
            ],
            'seo' => [
                'title' => 'Fresh Sardines | Sold by the Kilo, Chilled on Ice',
                'description' => 'Fresh whole sardines, 60–100 g per fish, landed and sold the same day. An oily, full-flavoured fish that is at its best grilled.',
            ],
            'tags' => ['sardines', 'oily fish', 'grilling fish'],
            'variants' => [
                'axes' => ['Pack Size' => ['500 g', '1 kg']],
                'combinations' => [
                    '500g' => ['price' => 11.00, 'qty' => 60, 'sku' => 'BW-FSH-SARD-06-500'],
                    '1kg' => ['price' => 20.00, 'qty' => 40, 'sku' => 'BW-FSH-SARD-06-1KG'],
                ],
            ],
        ],

        [
            'sku' => 'BW-FSH-MULL-07',
            'slug' => 'fresh-red-mullet',
            'category' => 'fresh-fish',
            'sub_category' => 'whole-fish',
            'image' => null,
            'unit' => 'kg',
            'price' => 52.00,
            'compare_at' => null,
            'stock' => 40,
            'name' => ['en' => 'Fresh Red Mullet', 'ar' => 'سمك السلطان إبراهيم الطازج'],
            'short' => [
                'en' => 'Small red mullet with distinctive, almost shellfish-like flavour — a delicacy sold whole and best fried.',
                'ar' => 'سلطان إبراهيم صغير الحجم بنكهة مميزة تقترب من طعم القشريات — صنف فاخر يُباع كاملاً ويُفضّل مقلياً.',
            ],
            'intro' => [
                'en' => 'Red mullet has a flavour unlike any other small fish — sweet, slightly mineral, closer to prawn than to bass. It is traditionally cooked whole and ungutted by cooks who value the liver, but we clean it on request. Keep the seasoning minimal: flour, salt and hot oil.',
                'ar' => 'يتميز السلطان إبراهيم بنكهة لا تشبه أي سمكة صغيرة أخرى — حلوة وذات لمسة معدنية، أقرب إلى الروبيان منها إلى القاروص. ويُطهى تقليدياً كاملاً دون تنظيف عند من يقدّرون كبده، لكننا ننظّفه عند الطلب. اجعل التتبيلة بسيطة: دقيق وملح وزيت ساخن.',
            ],
            'spec' => [
                'species' => ['en' => 'Red mullet', 'ar' => 'السلطان إبراهيم'],
                'scientific' => 'Mullus surmuletus',
                'origin' => ['en' => 'Mediterranean Sea', 'ar' => 'البحر الأبيض المتوسط'],
                'source' => ['en' => 'Wild-caught', 'ar' => 'صيد بري'],
                'condition' => ['en' => 'Fresh, chilled on ice', 'ar' => 'طازج، محفوظ على الثلج'],
                'cut' => ['en' => 'Whole; scaled and gutted on request', 'ar' => 'كامل؛ يُقشّر ويُنظّف عند الطلب'],
                'packaging' => ['en' => 'Packed on flake ice in an insulated box', 'ar' => 'يُعبأ على ثلج مجروش داخل صندوق معزول'],
                'weight' => ['en' => '120 g – 250 g per fish', 'ar' => '120 – 250 جم للسمكة الواحدة'],
            ],
            'storage' => [
                'en' => 'Store at 0–4 °C and cook within 24 hours. Freezing is not recommended: the delicate flesh softens.',
                'ar' => 'يُحفظ عند 0–4 مئوية ويُطهى خلال 24 ساعة. لا يُنصح بالتجميد لأن لحمه الرقيق يفقد تماسكه.',
            ],
            'cooking' => [
                'en' => [
                    'Dust in flour and shallow-fry in hot oil until crisp on both sides.',
                    'Grill whole over charcoal, brushed with olive oil and lemon.',
                    'Roast briefly with cherry tomatoes, capers and oregano.',
                ],
                'ar' => [
                    'غلّفه بالدقيق واقله في زيت ساخن حتى يقرمش من الجهتين.',
                    'اشوه كاملاً على الفحم مدهوناً بزيت الزيتون والليمون.',
                    'حمّره سريعاً مع الطماطم الكرزية والكبر والأوريغانو.',
                ],
            ],
            'nutrition' => [
                'en' => 'A lean fish, high in protein with a modest fat content and a good source of phosphorus.',
                'ar' => 'سمك قليل الدهون وغني بالبروتين، ومصدر جيد للفوسفور.',
            ],
            'seo' => [
                'title' => 'Fresh Red Mullet | Whole, Cleaned on Request',
                'description' => 'Fresh red mullet, 120–250 g per fish, chilled on ice and cleaned on request. A sweet, distinctive fish that is at its best fried whole.',
            ],
            'tags' => ['red mullet', 'whole fish', 'white fish'],
            'variants' => null,
        ],

        [
            'sku' => 'BW-FSH-HAMR-08',
            'slug' => 'fresh-hammour-grouper',
            'category' => 'fresh-fish',
            'sub_category' => 'whole-fish',
            'image' => null,
            'unit' => 'kg',
            'price' => 78.00,
            'compare_at' => 89.00,
            'stock' => 35,
            'name' => ['en' => 'Fresh Hammour (Grouper)', 'ar' => 'سمك الهامور الطازج'],
            'short' => [
                'en' => 'Gulf hammour with thick, meaty white flesh — the standard fish for machboos and for grilling in large pieces.',
                'ar' => 'هامور خليجي بلحم أبيض سميك ومتماسك — السمك المعتمد للمجبوس وللشوي بقطع كبيرة.',
            ],
            'intro' => [
                'en' => 'Hammour is the benchmark fish of the Gulf table: dense white flesh, very few bones, and a texture closer to chicken breast than to flaky white fish. That structure is what allows it to be cut into thick steaks, layered into machboos, or grilled in large pieces without falling apart.',
                'ar' => 'الهامور هو المعيار على مائدة الخليج: لحم أبيض كثيف قليل الشوك، وقوام أقرب إلى صدور الدجاج منه إلى الأسماك سريعة التفتت. وهذا التماسك هو ما يسمح بتقطيعه شرائح سميكة أو طهيه مع المجبوس أو شويه بقطع كبيرة دون أن يتفكك.',
            ],
            'spec' => [
                'species' => ['en' => 'Orange-spotted grouper (hammour)', 'ar' => 'الهامور المرقّط'],
                'scientific' => 'Epinephelus coioides',
                'origin' => ['en' => 'Arabian Gulf', 'ar' => 'الخليج العربي'],
                'source' => ['en' => 'Wild-caught', 'ar' => 'صيد بري'],
                'condition' => ['en' => 'Fresh, chilled on ice', 'ar' => 'طازج، محفوظ على الثلج'],
                'cut' => ['en' => 'Whole, cleaned or filleted to order', 'ar' => 'كامل أو منظّف أو فيليه حسب الطلب'],
                'packaging' => ['en' => 'Packed on flake ice in an insulated box', 'ar' => 'يُعبأ على ثلج مجروش داخل صندوق معزول'],
                'weight' => ['en' => '1 kg – 2.5 kg per fish', 'ar' => '1 – 2.5 كجم للسمكة الواحدة'],
            ],
            'storage' => [
                'en' => 'Keep on ice at 0–4 °C and cook within 24 hours. Portioned hammour freezes well at −18 °C for up to three months.',
                'ar' => 'يُحفظ على الثلج عند 0–4 مئوية ويُطهى خلال 24 ساعة. يتحمل الهامور المقطّع التجميد عند −18 مئوية لمدة تصل إلى ثلاثة أشهر.',
            ],
            'cooking' => [
                'en' => [
                    'Cut into thick steaks and grill, basting with lemon and bezar spice.',
                    'Layer over spiced rice for machboos and steam until just cooked.',
                    'Roast whole with dried lime, turmeric and onion.',
                ],
                'ar' => [
                    'قطّعه شرائح سميكة واشوه مع الدهن بالليمون والبزار.',
                    'ضعه فوق الأرز المتبّل لتحضير المجبوس واتركه على البخار حتى ينضج.',
                    'حمّره كاملاً مع اللومي والكركم والبصل.',
                ],
            ],
            'nutrition' => [
                'en' => 'A lean, high-protein white fish with very little fat and a good source of selenium.',
                'ar' => 'سمك أبيض غني بالبروتين وقليل الدهون جداً، ومصدر جيد للسيلينيوم.',
            ],
            'seo' => [
                'title' => 'Fresh Hammour (Grouper) | Whole or Filleted',
                'description' => 'Fresh Gulf hammour, 1–2.5 kg per fish, cleaned or filleted to order. Thick, meaty white flesh for machboos, steaks and grilling.',
            ],
            'tags' => ['hammour', 'grouper', 'machboos'],
            'variants' => [
                'axes' => ['Cut' => ['Whole', 'Cleaned', 'Steaks']],
                'combinations' => [
                    'Whole' => ['price' => 78.00, 'qty' => 15, 'sku' => 'BW-FSH-HAMR-08-WHL'],
                    'Cleaned' => ['price' => 84.00, 'qty' => 12, 'sku' => 'BW-FSH-HAMR-08-CLN'],
                    'Steaks' => ['price' => 105.00, 'qty' => 8, 'sku' => 'BW-FSH-HAMR-08-STK'],
                ],
            ],
        ],

        [
            'sku' => 'BW-FSH-SHER-09',
            'slug' => 'fresh-sheri-emperor',
            'category' => 'fresh-fish',
            'sub_category' => 'whole-fish',
            'image' => null,
            'unit' => 'kg',
            'price' => 58.00,
            'compare_at' => null,
            'stock' => 45,
            'name' => ['en' => 'Fresh Sheri (Spangled Emperor)', 'ar' => 'سمك الشعري الطازج'],
            'short' => [
                'en' => 'Gulf sheri with sweet white flesh and a firm bite — the local favourite for whole-fish grilling.',
                'ar' => 'شعري خليجي بلحم أبيض حلو المذاق وقوام متماسك — المفضل محلياً لشوي السمك كاملاً.',
            ],
            'intro' => [
                'en' => 'Sheri is caught in Gulf waters year round and is the fish most local cooks choose when the whole fish is going on the grill. The flesh is white, sweet and firmer than bream, and the thick skin protects it from the heat, so it can take a long, slow charcoal cook without drying.',
                'ar' => 'يُصطاد الشعري في مياه الخليج على مدار العام، وهو السمك الذي يختاره معظم الطهاة المحليين عند شوي السمكة كاملة. لحمه أبيض حلو وأكثر تماسكاً من الدنيس، وجلده السميك يحميه من الحرارة فيتحمل الشوي البطيء على الفحم دون أن يجف.',
            ],
            'spec' => [
                'species' => ['en' => 'Spangled emperor (sheri)', 'ar' => 'الشعري المرقّط'],
                'scientific' => 'Lethrinus nebulosus',
                'origin' => ['en' => 'Arabian Gulf', 'ar' => 'الخليج العربي'],
                'source' => ['en' => 'Wild-caught', 'ar' => 'صيد بري'],
                'condition' => ['en' => 'Fresh, chilled on ice', 'ar' => 'طازج، محفوظ على الثلج'],
                'cut' => ['en' => 'Whole or cleaned to order', 'ar' => 'كامل أو منظّف حسب الطلب'],
                'packaging' => ['en' => 'Packed on flake ice in an insulated box', 'ar' => 'يُعبأ على ثلج مجروش داخل صندوق معزول'],
                'weight' => ['en' => '700 g – 1.4 kg per fish', 'ar' => '700 جم – 1.4 كجم للسمكة الواحدة'],
            ],
            'storage' => [
                'en' => 'Keep on ice at 0–4 °C and cook within 24 hours, or freeze cleaned at −18 °C for up to three months.',
                'ar' => 'يُحفظ على الثلج عند 0–4 مئوية ويُطهى خلال 24 ساعة، أو يُجمّد بعد التنظيف عند −18 مئوية لمدة تصل إلى ثلاثة أشهر.',
            ],
            'cooking' => [
                'en' => [
                    'Butterfly, rub with bezar and grill slowly over charcoal.',
                    'Bake whole in a foil parcel with dried lime and onion.',
                    'Fry thick cutlets in ghee and serve with rice and daqoos.',
                ],
                'ar' => [
                    'افتحه فراشة وادهنه بالبزار واشوه ببطء على الفحم.',
                    'اخبزه كاملاً داخل ورق ألمنيوم مع اللومي والبصل.',
                    'اقلِ قطعه السميكة بالسمن وقدّمها مع الأرز والدقوس.',
                ],
            ],
            'nutrition' => [
                'en' => 'Lean and rich in protein, with a low fat content typical of reef white fish.',
                'ar' => 'قليل الدهون وغني بالبروتين، بمحتوى دهني منخفض كما هو معتاد في أسماك الشعاب البيضاء.',
            ],
            'seo' => [
                'title' => 'Fresh Sheri (Spangled Emperor) | Gulf-Caught',
                'description' => 'Fresh sheri from Gulf waters, 700 g – 1.4 kg per fish, cleaned to order. Sweet, firm white flesh made for whole-fish grilling.',
            ],
            'tags' => ['sheri', 'emperor', 'gulf fish'],
            'variants' => [
                'axes' => ['Cut' => ['Whole', 'Cleaned']],
                'combinations' => [
                    'Whole' => ['price' => 58.00, 'qty' => 25, 'sku' => 'BW-FSH-SHER-09-WHL'],
                    'Cleaned' => ['price' => 63.00, 'qty' => 20, 'sku' => 'BW-FSH-SHER-09-CLN'],
                ],
            ],
        ],

        [
            'sku' => 'BW-FSH-SAFI-10',
            'slug' => 'fresh-safi-rabbitfish',
            'category' => 'fresh-fish',
            'sub_category' => 'whole-fish',
            'image' => null,
            'unit' => 'kg',
            'price' => 42.00,
            'compare_at' => null,
            'stock' => 55,
            'name' => ['en' => 'Fresh Safi (Rabbitfish)', 'ar' => 'سمك الصافي الطازج'],
            'short' => [
                'en' => 'Local safi with a distinctive, slightly grassy sweetness — traditionally salted and grilled whole.',
                'ar' => 'صافي محلي بنكهة حلوة مميزة — يُملّح تقليدياً ويُشوى كاملاً.',
            ],
            'intro' => [
                'en' => 'Safi is a Gulf staple with a taste all of its own, a result of its algae-based diet. It has fine bones and thin flesh, so it is rarely filleted; the traditional treatment is a heavy salting, a short rest, and then a hot grill. Handle the dorsal spines with care when cleaning.',
                'ar' => 'الصافي من أساسيات مائدة الخليج وله طعم خاص ناتج عن غذائه من الطحالب. شوكه رفيع ولحمه غير سميك، لذلك نادراً ما يُقطّع فيليه؛ والطريقة التقليدية هي تمليحه جيداً ثم تركه قليلاً وشويه على نار قوية. انتبه للأشواك الظهرية عند التنظيف.',
            ],
            'spec' => [
                'species' => ['en' => 'White-spotted rabbitfish (safi)', 'ar' => 'الصافي المرقّط'],
                'scientific' => 'Siganus canaliculatus',
                'origin' => ['en' => 'Arabian Gulf', 'ar' => 'الخليج العربي'],
                'source' => ['en' => 'Wild-caught', 'ar' => 'صيد بري'],
                'condition' => ['en' => 'Fresh, chilled on ice', 'ar' => 'طازج، محفوظ على الثلج'],
                'cut' => ['en' => 'Whole or cleaned to order', 'ar' => 'كامل أو منظّف حسب الطلب'],
                'packaging' => ['en' => 'Packed on ice in an insulated box', 'ar' => 'يُعبأ على الثلج داخل صندوق معزول'],
                'weight' => ['en' => '250 g – 450 g per fish', 'ar' => '250 – 450 جم للسمكة الواحدة'],
            ],
            'storage' => [
                'en' => 'Keep at 0–4 °C and cook within 24 hours. Best eaten fresh; freezing softens the texture.',
                'ar' => 'يُحفظ عند 0–4 مئوية ويُطهى خلال 24 ساعة. يُفضّل تناوله طازجاً، فالتجميد يليّن قوامه.',
            ],
            'cooking' => [
                'en' => [
                    'Salt heavily, rest for an hour, then grill whole over charcoal.',
                    'Fry in a light flour coating and serve with lemon.',
                    'Bake in a tray with tomato, onion and green chilli.',
                ],
                'ar' => [
                    'ملّحه بكثافة واتركه ساعة ثم اشوه كاملاً على الفحم.',
                    'اقله بطبقة خفيفة من الدقيق وقدّمه مع الليمون.',
                    'اخبزه في صينية مع الطماطم والبصل والفلفل الأخضر.',
                ],
            ],
            'nutrition' => [
                'en' => 'A lean fish, high in protein and low in fat.',
                'ar' => 'سمك قليل الدهون وغني بالبروتين.',
            ],
            'seo' => [
                'title' => 'Fresh Safi (Rabbitfish) | Gulf-Caught, Cleaned to Order',
                'description' => 'Fresh safi from Gulf waters, 250–450 g per fish, cleaned to order. A local favourite, traditionally salted and grilled whole.',
            ],
            'tags' => ['safi', 'rabbitfish', 'gulf fish'],
            'variants' => null,
        ],

        /* ---------------------------------------------------------------
         | Fresh fish · fillets
         --------------------------------------------------------------- */

        [
            'sku' => 'BW-FIL-SALM-11',
            'slug' => 'norwegian-salmon-fillet',
            'category' => 'fresh-fish',
            'sub_category' => 'fish-fillets',
            'image' => 'salmon-and-white-fish-fillets.webp',
            'unit' => 'kg',
            'price' => 95.00,
            'compare_at' => 110.00,
            'stock' => 60,
            'name' => ['en' => 'Norwegian Salmon Fillet, Skin-On', 'ar' => 'فيليه السلمون النرويجي بالجلد'],
            'short' => [
                'en' => 'Deep-orange Atlantic salmon fillet, pin-boned and trimmed, with the skin left on for crisping.',
                'ar' => 'فيليه سلمون أطلسي بلون برتقالي غامق، منزوع الشوك ومشذّب، مع بقاء الجلد ليصبح مقرمشاً عند الطهي.',
            ],
            'intro' => [
                'en' => 'Our salmon is farmed in Norway and cut to order from whole sides, so you get an even fillet rather than a thin tail piece. Pin bones are removed and the skin is left on: it protects the flesh in a hot pan and crisps into the best part of the dish. The fat marbling is what keeps salmon forgiving — a minute too long will not ruin it.',
                'ar' => 'يُستزرع السلمون لدينا في النرويج ويُقطّع عند الطلب من الشرائح الكاملة، لتحصل على فيليه متساوي السماكة بدلاً من قطعة ذيل رفيعة. يُنزع الشوك ويُترك الجلد: فهو يحمي اللحم في المقلاة الساخنة ويتحول إلى أشهى جزء في الطبق. وتوزّع الدهون داخل اللحم هو ما يجعل السلمون متسامحاً — فدقيقة إضافية لن تفسده.',
            ],
            'spec' => [
                'species' => ['en' => 'Atlantic salmon', 'ar' => 'السلمون الأطلسي'],
                'scientific' => 'Salmo salar',
                'origin' => ['en' => 'Farmed in Norway', 'ar' => 'مستزرع في النرويج'],
                'source' => ['en' => 'Farmed', 'ar' => 'استزراع سمكي'],
                'condition' => ['en' => 'Fresh, chilled', 'ar' => 'طازج، مبرّد'],
                'cut' => ['en' => 'Skin-on fillet, pin-boned', 'ar' => 'فيليه بالجلد، منزوع الشوك'],
                'packaging' => ['en' => 'Vacuum-packed in 500 g and 1 kg portions', 'ar' => 'مغلّف بتفريغ الهواء بعبوات 500 جم و1 كجم'],
                'weight' => ['en' => '500 g or 1 kg per pack', 'ar' => '500 جم أو 1 كجم للعبوة'],
            ],
            'storage' => [
                'en' => 'Keep at 0–4 °C and use within two days of delivery. Vacuum-packed fillets freeze well at −18 °C for up to three months.',
                'ar' => 'يُحفظ عند 0–4 مئوية ويُستخدم خلال يومين من الاستلام. تتحمل العبوات المفرّغة من الهواء التجميد عند −18 مئوية لمدة تصل إلى ثلاثة أشهر.',
            ],
            'cooking' => [
                'en' => [
                    'Sear skin-side down in a dry hot pan for four minutes, then one minute on the flesh.',
                    'Roast at 180 °C for 12–15 minutes with lemon and dill.',
                    'Cure with salt, sugar and dill for 24 hours to make gravlax.',
                ],
                'ar' => [
                    'اقله على جهة الجلد في مقلاة ساخنة جافة لأربع دقائق، ثم دقيقة واحدة على جهة اللحم.',
                    'اخبزه على 180 مئوية لمدة 12–15 دقيقة مع الليمون والشبت.',
                    'تبّله بالملح والسكر والشبت لمدة 24 ساعة لتحضير الجرافلاكس.',
                ],
            ],
            'nutrition' => [
                'en' => 'An oily fish rich in long-chain omega-3 fatty acids, and a good source of protein and vitamin D.',
                'ar' => 'سمك دسم غني بأحماض أوميغا-3 طويلة السلسلة، ومصدر جيد للبروتين وفيتامين د.',
            ],
            'seo' => [
                'title' => 'Norwegian Salmon Fillet, Skin-On | Pin-Boned, Cut to Order',
                'description' => 'Fresh Norwegian Atlantic salmon fillet, skin-on and pin-boned, vacuum-packed in 500 g and 1 kg portions. Rich in omega-3 and ready for the pan or oven.',
            ],
            'tags' => ['salmon', 'fillet', 'omega 3'],
            'variants' => [
                'axes' => ['Pack Size' => ['500 g', '1 kg']],
                'combinations' => [
                    '500g' => ['price' => 49.00, 'qty' => 35, 'sku' => 'BW-FIL-SALM-11-500'],
                    '1kg' => ['price' => 95.00, 'qty' => 25, 'sku' => 'BW-FIL-SALM-11-1KG'],
                ],
            ],
        ],

        [
            'sku' => 'BW-FIL-SALMP-12',
            'slug' => 'salmon-portions-skinless',
            'category' => 'fresh-fish',
            'sub_category' => 'fish-fillets',
            'image' => 'salmon-and-white-fish-fillets.webp',
            'unit' => 'kg',
            'price' => 105.00,
            'compare_at' => null,
            'stock' => 45,
            'name' => ['en' => 'Skinless Salmon Portions', 'ar' => 'قطع السلمون بدون جلد'],
            'short' => [
                'en' => 'Evenly cut 150 g salmon portions, skinless and boneless — portion control without the knife work.',
                'ar' => 'قطع سلمون متساوية بوزن 150 جم، بدون جلد أو شوك — حصص جاهزة دون عناء التقطيع.',
            ],
            'intro' => [
                'en' => 'Cut from the thick centre of the side, these portions are the same weight and thickness, so they cook in the same time — useful when you are plating for a table rather than a pan. Skinless and boneless, they suit poaching, en papillote cooking and anything sauced.',
                'ar' => 'تُقطع هذه الحصص من الجزء السميك في وسط الشريحة، وتكون متساوية في الوزن والسماكة فتنضج في الوقت نفسه — وهو أمر مفيد عند التقديم لمائدة كاملة. وبما أنها بلا جلد أو شوك، فهي مناسبة للسلق والطهي داخل ورق الزبدة ولكل الأطباق ذات الصلصات.',
            ],
            'spec' => [
                'species' => ['en' => 'Atlantic salmon', 'ar' => 'السلمون الأطلسي'],
                'scientific' => 'Salmo salar',
                'origin' => ['en' => 'Farmed in Norway', 'ar' => 'مستزرع في النرويج'],
                'source' => ['en' => 'Farmed', 'ar' => 'استزراع سمكي'],
                'condition' => ['en' => 'Fresh, chilled', 'ar' => 'طازج، مبرّد'],
                'cut' => ['en' => 'Skinless, boneless centre-cut portions', 'ar' => 'قطع من الوسط بدون جلد أو شوك'],
                'packaging' => ['en' => 'Four 150 g portions, vacuum-packed', 'ar' => 'أربع قطع بوزن 150 جم، مغلّفة بتفريغ الهواء'],
                'weight' => ['en' => '600 g per pack (4 × 150 g)', 'ar' => '600 جم للعبوة (4 × 150 جم)'],
            ],
            'storage' => [
                'en' => 'Keep at 0–4 °C and use within two days. Freeze in the vacuum pack at −18 °C for up to three months.',
                'ar' => 'تُحفظ عند 0–4 مئوية وتُستخدم خلال يومين. يمكن تجميدها داخل عبوتها المفرّغة عند −18 مئوية لمدة تصل إلى ثلاثة أشهر.',
            ],
            'cooking' => [
                'en' => [
                    'Poach gently in stock or milk for eight minutes.',
                    'Bake en papillote with fennel, lemon and white wine.',
                    'Cube for skewers and grill for three minutes a side.',
                ],
                'ar' => [
                    'اسلقها بلطف في المرق أو الحليب لمدة ثماني دقائق.',
                    'اخبزها داخل ورق الزبدة مع الشمر والليمون.',
                    'قطّعها مكعبات للأسياخ واشوها ثلاث دقائق لكل جهة.',
                ],
            ],
            'nutrition' => [
                'en' => 'Rich in omega-3 fatty acids and high-quality protein, with vitamin D and B12.',
                'ar' => 'غنية بأحماض أوميغا-3 وبالبروتين عالي الجودة، إضافة إلى فيتامين د وب12.',
            ],
            'seo' => [
                'title' => 'Skinless Salmon Portions | 4 × 150 g, Vacuum-Packed',
                'description' => 'Centre-cut Norwegian salmon portions, skinless and boneless, four 150 g pieces per vacuum pack. Even thickness for consistent cooking.',
            ],
            'tags' => ['salmon', 'portions', 'omega 3'],
            'variants' => null,
        ],

        [
            'sku' => 'BW-FIL-SBASS-13',
            'slug' => 'sea-bass-fillet',
            'category' => 'fresh-fish',
            'sub_category' => 'fish-fillets',
            'image' => null,
            'unit' => 'kg',
            'price' => 85.00,
            'compare_at' => null,
            'stock' => 40,
            'name' => ['en' => 'Sea Bass Fillet, Skin-On', 'ar' => 'فيليه القاروص بالجلد'],
            'short' => [
                'en' => 'Hand-cut sea bass fillets, pin-boned with the skin on — a ten-minute dinner with a restaurant finish.',
                'ar' => 'شرائح فيليه قاروص مقطّعة يدوياً ومنزوعة الشوك مع الجلد — عشاء في عشر دقائق بمستوى المطاعم.',
            ],
            'intro' => [
                'en' => 'Sea bass fillets are cut by hand from fish we clean the same morning, pin-boned and left skin-on. The skin is what makes this cut worth buying: dried well and pressed into a hot pan, it turns to glass while the flesh underneath stays barely set.',
                'ar' => 'تُقطّع شرائح القاروص يدوياً من أسماك ننظّفها في الصباح نفسه، وتُنزع أشواكها ويُترك جلدها. والجلد هو ما يجعل هذه القطعة تستحق الشراء: عند تجفيفه جيداً وضغطه في مقلاة ساخنة يصبح مقرمشاً كالزجاج بينما يبقى اللحم أسفله طرياً.',
            ],
            'spec' => [
                'species' => ['en' => 'European sea bass', 'ar' => 'القاروص الأوروبي'],
                'scientific' => 'Dicentrarchus labrax',
                'origin' => ['en' => 'Farmed in Türkiye', 'ar' => 'مستزرع في تركيا'],
                'source' => ['en' => 'Farmed', 'ar' => 'استزراع سمكي'],
                'condition' => ['en' => 'Fresh, chilled', 'ar' => 'طازج، مبرّد'],
                'cut' => ['en' => 'Skin-on fillet, pin-boned', 'ar' => 'فيليه بالجلد، منزوع الشوك'],
                'packaging' => ['en' => 'Two fillets per tray, film-sealed', 'ar' => 'شريحتان في كل عبوة، مغلّفة بإحكام'],
                'weight' => ['en' => '140 g – 180 g per fillet', 'ar' => '140 – 180 جم للشريحة'],
            ],
            'storage' => [
                'en' => 'Keep at 0–4 °C and cook within 24 hours. Freeze at −18 °C for up to two months.',
                'ar' => 'تُحفظ عند 0–4 مئوية وتُطهى خلال 24 ساعة. يمكن تجميدها عند −18 مئوية لمدة تصل إلى شهرين.',
            ],
            'cooking' => [
                'en' => [
                    'Dry the skin, salt it and sear skin-side down for four minutes without moving it.',
                    'Steam over aromatics with ginger, spring onion and soy.',
                    'Grill briefly and dress with olive oil and capers.',
                ],
                'ar' => [
                    'جفّف الجلد وملّحه واقله على جهة الجلد أربع دقائق دون تحريك.',
                    'اطهها على البخار مع الزنجبيل والبصل الأخضر وصلصة الصويا.',
                    'اشوها سريعاً وقدّمها مع زيت الزيتون والكبر.',
                ],
            ],
            'nutrition' => [
                'en' => 'A lean, high-protein white fish, low in saturated fat with a useful amount of omega-3.',
                'ar' => 'سمك أبيض قليل الدهون وغني بالبروتين، منخفض الدهون المشبعة ويحتوي قدراً مفيداً من أوميغا-3.',
            ],
            'seo' => [
                'title' => 'Sea Bass Fillet, Skin-On | Hand-Cut and Pin-Boned',
                'description' => 'Fresh sea bass fillets, 140–180 g each, hand-cut, pin-boned and skin-on. Ready for the pan in under ten minutes.',
            ],
            'tags' => ['sea bass', 'fillet', 'white fish'],
            'variants' => null,
        ],

        [
            'sku' => 'BW-FIL-SBREAM-14',
            'slug' => 'sea-bream-fillet',
            'category' => 'fresh-fish',
            'sub_category' => 'fish-fillets',
            'image' => null,
            'unit' => 'kg',
            'price' => 82.00,
            'compare_at' => null,
            'stock' => 35,
            'name' => ['en' => 'Sea Bream Fillet, Skin-On', 'ar' => 'فيليه الدنيس بالجلد'],
            'short' => [
                'en' => 'Delicate bream fillets with sweet white flesh — quick to cook and easy to overcook, so watch the pan.',
                'ar' => 'شرائح دنيس رقيقة بلحم أبيض حلو المذاق — سريعة النضج وسهلة الإفراط في طهيها، فراقب المقلاة.',
            ],
            'intro' => [
                'en' => 'Bream fillets are thinner and finer than sea bass, which makes them the fastest fish in this section: two to three minutes on the skin is usually enough. Their sweetness pairs well with acidity, so finish with lemon, orange or a tomato dressing rather than heavy sauces.',
                'ar' => 'شرائح الدنيس أرفع وأنعم من القاروص، ما يجعلها الأسرع نضجاً في هذا القسم: دقيقتان إلى ثلاث على جهة الجلد تكفي عادةً. وحلاوتها تنسجم مع الحموضة، لذا قدّمها مع الليمون أو البرتقال أو صلصة الطماطم بدلاً من الصلصات الثقيلة.',
            ],
            'spec' => [
                'species' => ['en' => 'Gilt-head sea bream', 'ar' => 'الدنيس ذهبي الرأس'],
                'scientific' => 'Sparus aurata',
                'origin' => ['en' => 'Farmed in Türkiye', 'ar' => 'مستزرع في تركيا'],
                'source' => ['en' => 'Farmed', 'ar' => 'استزراع سمكي'],
                'condition' => ['en' => 'Fresh, chilled', 'ar' => 'طازج، مبرّد'],
                'cut' => ['en' => 'Skin-on fillet, pin-boned', 'ar' => 'فيليه بالجلد، منزوع الشوك'],
                'packaging' => ['en' => 'Two fillets per tray, film-sealed', 'ar' => 'شريحتان في كل عبوة، مغلّفة بإحكام'],
                'weight' => ['en' => '120 g – 160 g per fillet', 'ar' => '120 – 160 جم للشريحة'],
            ],
            'storage' => [
                'en' => 'Keep at 0–4 °C and cook within 24 hours. Freezing is possible at −18 °C but the texture softens after a month.',
                'ar' => 'تُحفظ عند 0–4 مئوية وتُطهى خلال 24 ساعة. يمكن تجميدها عند −18 مئوية لكن قوامها يلين بعد شهر.',
            ],
            'cooking' => [
                'en' => [
                    'Sear skin-side down for two to three minutes and serve immediately.',
                    'Bake in parchment with cherry tomatoes and thyme.',
                    'Slice raw for crudo with olive oil, sea salt and lemon.',
                ],
                'ar' => [
                    'اقلها على جهة الجلد لدقيقتين إلى ثلاث وقدّمها فوراً.',
                    'اخبزها في ورق الزبدة مع الطماطم الكرزية والزعتر.',
                    'قطّعها نيئة لتحضير الكرودو مع زيت الزيتون وملح البحر والليمون.',
                ],
            ],
            'nutrition' => [
                'en' => 'High in protein with a moderate fat content and a good source of phosphorus.',
                'ar' => 'غنية بالبروتين بمحتوى دهني معتدل، ومصدر جيد للفوسفور.',
            ],
            'seo' => [
                'title' => 'Sea Bream Fillet, Skin-On | Pin-Boned, Cut to Order',
                'description' => 'Fresh gilt-head bream fillets, 120–160 g each, skin-on and pin-boned. Sweet, delicate flesh that cooks in minutes.',
            ],
            'tags' => ['sea bream', 'fillet', 'white fish'],
            'variants' => null,
        ],

        [
            'sku' => 'BW-FIL-TILA-15',
            'slug' => 'tilapia-fillet',
            'category' => 'fresh-fish',
            'sub_category' => 'fish-fillets',
            'image' => null,
            'unit' => 'kg',
            'price' => 38.00,
            'compare_at' => 44.00,
            'stock' => 90,
            'name' => ['en' => 'Tilapia Fillet, Skinless', 'ar' => 'فيليه البلطي بدون جلد'],
            'short' => [
                'en' => 'Boneless, skinless tilapia fillets — the most economical way to put white fish on the table.',
                'ar' => 'شرائح بلطي بدون شوك أو جلد — الطريقة الأوفر لتقديم السمك الأبيض على المائدة.',
            ],
            'intro' => [
                'en' => 'These fillets are trimmed of skin and bones, which makes them the easiest fish in the shop to cook with children in the house. The flavour is mild, so treat them as a carrier: breadcrumb them, spice them heavily, or simmer them in a sauce.',
                'ar' => 'تُنزع من هذه الشرائح الجلد والشوك، ما يجعلها أسهل سمك في المتجر للطهي في وجود الأطفال. نكهتها هادئة، لذا تعامل معها كقاعدة تحمل النكهات: غلّفها بالبقسماط، أو تبّلها بقوة، أو اطهها في صلصة.',
            ],
            'spec' => [
                'species' => ['en' => 'Nile tilapia', 'ar' => 'البلطي النيلي'],
                'scientific' => 'Oreochromis niloticus',
                'origin' => ['en' => 'Farmed in Egypt', 'ar' => 'مستزرع في مصر'],
                'source' => ['en' => 'Farmed', 'ar' => 'استزراع سمكي'],
                'condition' => ['en' => 'Fresh, chilled', 'ar' => 'طازج، مبرّد'],
                'cut' => ['en' => 'Skinless, boneless fillet', 'ar' => 'فيليه بدون جلد أو شوك'],
                'packaging' => ['en' => 'Film-sealed tray, 500 g or 1 kg', 'ar' => 'عبوة مغلّفة بإحكام، 500 جم أو 1 كجم'],
                'weight' => ['en' => '100 g – 150 g per fillet', 'ar' => '100 – 150 جم للشريحة'],
            ],
            'storage' => [
                'en' => 'Keep at 0–4 °C and cook within 24 hours, or freeze at −18 °C for up to three months.',
                'ar' => 'تُحفظ عند 0–4 مئوية وتُطهى خلال 24 ساعة، أو تُجمّد عند −18 مئوية لمدة تصل إلى ثلاثة أشهر.',
            ],
            'cooking' => [
                'en' => [
                    'Coat in seasoned breadcrumbs and shallow-fry for three minutes a side.',
                    'Bake with a tahini and lemon crust at 200 °C.',
                    'Cut into strips for fish tacos with lime and cabbage.',
                ],
                'ar' => [
                    'غلّفها بالبقسماط المتبّل واقلها ثلاث دقائق لكل جهة.',
                    'اخبزها بطبقة من الطحينة والليمون على 200 مئوية.',
                    'قطّعها شرائح لتحضير تاكو السمك مع الليمون والملفوف.',
                ],
            ],
            'nutrition' => [
                'en' => 'A lean, low-fat source of protein that suits light everyday cooking.',
                'ar' => 'مصدر للبروتين قليل الدهون يناسب الطهي اليومي الخفيف.',
            ],
            'seo' => [
                'title' => 'Tilapia Fillet, Skinless & Boneless | 500 g and 1 kg Packs',
                'description' => 'Fresh skinless tilapia fillets, 100–150 g each, trimmed of bones. An economical white fish for frying, baking and everyday meals.',
            ],
            'tags' => ['tilapia', 'fillet', 'white fish'],
            'variants' => [
                'axes' => ['Pack Size' => ['500 g', '1 kg']],
                'combinations' => [
                    '500g' => ['price' => 20.00, 'qty' => 55, 'sku' => 'BW-FIL-TILA-15-500'],
                    '1kg' => ['price' => 38.00, 'qty' => 35, 'sku' => 'BW-FIL-TILA-15-1KG'],
                ],
            ],
        ],

        [
            'sku' => 'BW-FIL-HAMR-16',
            'slug' => 'hammour-fillet',
            'category' => 'fresh-fish',
            'sub_category' => 'fish-fillets',
            'image' => null,
            'unit' => 'kg',
            'price' => 130.00,
            'compare_at' => null,
            'stock' => 25,
            'name' => ['en' => 'Hammour Fillet, Skinless', 'ar' => 'فيليه الهامور بدون جلد'],
            'short' => [
                'en' => 'Thick, boneless hammour fillet — the premium white cut for grilling and for fish curry.',
                'ar' => 'فيليه هامور سميك بدون شوك — القطعة البيضاء الفاخرة للشوي ولكاري السمك.',
            ],
            'intro' => [
                'en' => 'Cut from large Gulf hammour, these fillets are thick enough to be treated like a steak. The flesh is dense and holds together in the pan, on skewers and in a simmering curry, which is why it costs more per kilo than any other white fish we sell.',
                'ar' => 'تُقطع هذه الشرائح من أسماك الهامور الكبيرة في الخليج، وهي سميكة بما يكفي للتعامل معها كشريحة لحم. لحمها كثيف ومتماسك في المقلاة وعلى الأسياخ وفي الكاري أثناء الغليان، ولهذا سعرها للكيلو أعلى من أي سمك أبيض آخر نبيعه.',
            ],
            'spec' => [
                'species' => ['en' => 'Orange-spotted grouper (hammour)', 'ar' => 'الهامور المرقّط'],
                'scientific' => 'Epinephelus coioides',
                'origin' => ['en' => 'Arabian Gulf', 'ar' => 'الخليج العربي'],
                'source' => ['en' => 'Wild-caught', 'ar' => 'صيد بري'],
                'condition' => ['en' => 'Fresh, chilled', 'ar' => 'طازج، مبرّد'],
                'cut' => ['en' => 'Skinless, boneless fillet', 'ar' => 'فيليه بدون جلد أو شوك'],
                'packaging' => ['en' => 'Vacuum-packed, 500 g portions', 'ar' => 'مغلّف بتفريغ الهواء بعبوات 500 جم'],
                'weight' => ['en' => '500 g per pack', 'ar' => '500 جم للعبوة'],
            ],
            'storage' => [
                'en' => 'Keep at 0–4 °C and use within two days. Freezes well at −18 °C for up to three months.',
                'ar' => 'يُحفظ عند 0–4 مئوية ويُستخدم خلال يومين. يتحمل التجميد عند −18 مئوية لمدة تصل إلى ثلاثة أشهر.',
            ],
            'cooking' => [
                'en' => [
                    'Cut into cubes for a coconut and curry-leaf fish curry.',
                    'Grill thick pieces with turmeric, garlic and lime.',
                    'Pan-fry in ghee and finish with dried lime and black pepper.',
                ],
                'ar' => [
                    'قطّعه مكعبات لكاري السمك بجوز الهند وأوراق الكاري.',
                    'اشوِ القطع السميكة مع الكركم والثوم والليمون.',
                    'اقله بالسمن وقدّمه مع اللومي والفلفل الأسود.',
                ],
            ],
            'nutrition' => [
                'en' => 'Very lean and high in protein, with a low fat content and a good level of selenium.',
                'ar' => 'قليل الدهون جداً وغني بالبروتين، ومصدر جيد للسيلينيوم.',
            ],
            'seo' => [
                'title' => 'Hammour Fillet, Skinless | Gulf Grouper, Vacuum-Packed',
                'description' => 'Fresh hammour fillet from Gulf grouper, skinless and boneless, vacuum-packed in 500 g portions. Dense white flesh for grilling and curry.',
            ],
            'tags' => ['hammour', 'fillet', 'grouper'],
            'variants' => null,
        ],

        /* ---------------------------------------------------------------
         | Fresh fish · steaks and portions
         --------------------------------------------------------------- */

        [
            'sku' => 'BW-STK-KING-17',
            'slug' => 'kingfish-steaks',
            'category' => 'fresh-fish',
            'sub_category' => 'fish-steaks-and-portions',
            'image' => null,
            'unit' => 'kg',
            'price' => 55.00,
            'compare_at' => 62.00,
            'stock' => 60,
            'name' => ['en' => 'Kingfish Steaks', 'ar' => 'شرائح الكنعد'],
            'short' => [
                'en' => 'Thick cross-cut kingfish steaks with firm, meaty flesh — the classic fish for a hard sear.',
                'ar' => 'شرائح كنعد سميكة مقطّعة عرضياً بلحم متماسك — السمك الكلاسيكي للقلي السريع على نار عالية.',
            ],
            'intro' => [
                'en' => 'Kingfish (chanaad) is cut across the bone into steaks about two centimetres thick. The flesh is firm and slightly oily, with a single central bone and no small bones to pick out, which is why it is the standard fish for a quick fried lunch across the Gulf.',
                'ar' => 'يُقطّع الكنعد (الشناد) عرضياً مع العظم إلى شرائح بسماكة سنتيمترين تقريباً. لحمه متماسك ودسم قليلاً، وله عظمة مركزية واحدة دون شوك صغير، ولهذا هو السمك المعتاد لغداء مقلي سريع في أنحاء الخليج.',
            ],
            'spec' => [
                'species' => ['en' => 'Narrow-barred Spanish mackerel (kingfish)', 'ar' => 'الكنعد'],
                'scientific' => 'Scomberomorus commerson',
                'origin' => ['en' => 'Arabian Sea, Oman', 'ar' => 'بحر العرب، عُمان'],
                'source' => ['en' => 'Wild-caught', 'ar' => 'صيد بري'],
                'condition' => ['en' => 'Fresh, chilled on ice', 'ar' => 'طازج، محفوظ على الثلج'],
                'cut' => ['en' => 'Bone-in cross-cut steaks, ~2 cm thick', 'ar' => 'شرائح عرضية بالعظم بسماكة نحو 2 سم'],
                'packaging' => ['en' => 'Film-sealed tray on absorbent pad', 'ar' => 'عبوة مغلّفة بإحكام فوق وسادة ماصّة'],
                'weight' => ['en' => '180 g – 250 g per steak', 'ar' => '180 – 250 جم للشريحة'],
            ],
            'storage' => [
                'en' => 'Keep at 0–4 °C and cook within 24 hours. Freeze at −18 °C for up to two months.',
                'ar' => 'تُحفظ عند 0–4 مئوية وتُطهى خلال 24 ساعة. يمكن تجميدها عند −18 مئوية لمدة تصل إلى شهرين.',
            ],
            'cooking' => [
                'en' => [
                    'Marinate in turmeric, chilli and lime, then shallow-fry until crusted.',
                    'Grill over charcoal for four minutes a side.',
                    'Simmer in a tamarind curry — the steaks hold their shape.',
                ],
                'ar' => [
                    'تبّلها بالكركم والفلفل الحار والليمون ثم اقلها حتى تتكوّن قشرة.',
                    'اشوها على الفحم أربع دقائق لكل جهة.',
                    'اطهها في كاري التمر الهندي — تحافظ الشرائح على شكلها.',
                ],
            ],
            'nutrition' => [
                'en' => 'A firm, protein-rich fish with a moderate oil content that provides omega-3 fatty acids.',
                'ar' => 'سمك متماسك غني بالبروتين بمحتوى دهني معتدل يوفر أحماض أوميغا-3.',
            ],
            'seo' => [
                'title' => 'Kingfish Steaks (Chanaad) | Cut to Order, Chilled',
                'description' => 'Fresh kingfish steaks, 180–250 g each, cross-cut about 2 cm thick. Firm, meaty flesh for frying, grilling and curry.',
            ],
            'tags' => ['kingfish', 'steaks', 'gulf fish'],
            'variants' => null,
        ],

        [
            'sku' => 'BW-STK-TUNA-18',
            'slug' => 'yellowfin-tuna-steaks',
            'category' => 'fresh-fish',
            'sub_category' => 'fish-steaks-and-portions',
            'image' => null,
            'unit' => 'kg',
            'price' => 65.00,
            'compare_at' => null,
            'stock' => 40,
            'name' => ['en' => 'Yellowfin Tuna Steaks', 'ar' => 'شرائح التونة صفراء الزعانف'],
            'short' => [
                'en' => 'Deep-red yellowfin tuna loin steaks, cut thick — sear the outside and leave the centre rare.',
                'ar' => 'شرائح من خاصرة التونة صفراء الزعانف بلون أحمر داكن، مقطّعة سميكة — اقلها من الخارج واترك وسطها نيئاً.',
            ],
            'intro' => [
                'en' => 'Tuna is the one fish that is genuinely better undercooked. Cut from the loin, these steaks are dense and almost meat-like; taken past medium they turn dry and grey. Sear them hard for a minute on each side and slice against the grain.',
                'ar' => 'التونة هي السمكة الوحيدة التي تكون فعلاً أفضل عند طهيها قليلاً. تُقطع هذه الشرائح من الخاصرة، وهي كثيفة تشبه اللحم الأحمر؛ وإذا تجاوزت الطهي المتوسط تصبح جافة ورمادية. اقلها بقوة دقيقة لكل جهة وقطّعها عكس اتجاه الألياف.',
            ],
            'spec' => [
                'species' => ['en' => 'Yellowfin tuna', 'ar' => 'التونة صفراء الزعانف'],
                'scientific' => 'Thunnus albacares',
                'origin' => ['en' => 'Indian Ocean', 'ar' => 'المحيط الهندي'],
                'source' => ['en' => 'Wild-caught', 'ar' => 'صيد بري'],
                'condition' => ['en' => 'Fresh, chilled', 'ar' => 'طازج، مبرّد'],
                'cut' => ['en' => 'Boneless loin steaks, ~2.5 cm thick', 'ar' => 'شرائح خاصرة بدون عظم بسماكة نحو 2.5 سم'],
                'packaging' => ['en' => 'Vacuum-packed, two steaks per pack', 'ar' => 'مغلّفة بتفريغ الهواء، شريحتان في العبوة'],
                'weight' => ['en' => '200 g – 250 g per steak', 'ar' => '200 – 250 جم للشريحة'],
            ],
            'storage' => [
                'en' => 'Keep at 0–4 °C and use within 24 hours. Freeze at −18 °C for up to two months; thaw slowly in the fridge to protect the colour.',
                'ar' => 'تُحفظ عند 0–4 مئوية وتُستخدم خلال 24 ساعة. يمكن تجميدها عند −18 مئوية لمدة تصل إلى شهرين، مع إذابتها ببطء في الثلاجة للحفاظ على لونها.',
            ],
            'cooking' => [
                'en' => [
                    'Sear one minute a side in a smoking-hot pan and rest before slicing.',
                    'Marinate in soy, ginger and sesame, then grill briefly.',
                    'Dice raw for tartare with lime, chilli and olive oil.',
                ],
                'ar' => [
                    'اقلها دقيقة لكل جهة في مقلاة شديدة السخونة وأرحها قبل التقطيع.',
                    'تبّلها بصلصة الصويا والزنجبيل والسمسم ثم اشوها سريعاً.',
                    'قطّعها نيئة مكعبات لتحضير التارتار مع الليمون والفلفل الحار وزيت الزيتون.',
                ],
            ],
            'nutrition' => [
                'en' => 'Very high in protein and low in fat, and a source of omega-3 fatty acids, niacin and selenium.',
                'ar' => 'غنية جداً بالبروتين وقليلة الدهون، ومصدر لأحماض أوميغا-3 والنياسين والسيلينيوم.',
            ],
            'seo' => [
                'title' => 'Yellowfin Tuna Steaks | Loin-Cut, Vacuum-Packed',
                'description' => 'Fresh yellowfin tuna loin steaks, 200–250 g each, cut about 2.5 cm thick. Best seared rare and sliced against the grain.',
            ],
            'tags' => ['tuna', 'steaks', 'grilling fish'],
            'variants' => null,
        ],

        [
            'sku' => 'BW-STK-SALM-19',
            'slug' => 'salmon-steaks',
            'category' => 'fresh-fish',
            'sub_category' => 'fish-steaks-and-portions',
            'image' => null,
            'unit' => 'kg',
            'price' => 89.00,
            'compare_at' => null,
            'stock' => 40,
            'name' => ['en' => 'Salmon Steaks, Bone-In', 'ar' => 'شرائح السلمون بالعظم'],
            'short' => [
                'en' => 'Horseshoe-cut salmon steaks on the bone — more flavour than a fillet and harder to dry out.',
                'ar' => 'شرائح سلمون بالعظم على شكل حدوة حصان — نكهة أعمق من الفيليه وأصعب في الجفاف.',
            ],
            'intro' => [
                'en' => 'Cut across the whole side, salmon steaks keep the bone and the belly flap, and both do useful work: the bone slows the heat, the belly adds fat. It is the cut to choose for a barbecue, where a boneless fillet would dry out before the centre is done.',
                'ar' => 'تُقطع شرائح السلمون عرضياً عبر الشريحة الكاملة مع بقاء العظم وطرف البطن، وكلاهما مفيد: العظم يبطئ وصول الحرارة، وطرف البطن يضيف الدهن. وهي القطعة المناسبة للشوي في الهواء الطلق، حيث يجف الفيليه منزوع العظم قبل أن ينضج وسطه.',
            ],
            'spec' => [
                'species' => ['en' => 'Atlantic salmon', 'ar' => 'السلمون الأطلسي'],
                'scientific' => 'Salmo salar',
                'origin' => ['en' => 'Farmed in Norway', 'ar' => 'مستزرع في النرويج'],
                'source' => ['en' => 'Farmed', 'ar' => 'استزراع سمكي'],
                'condition' => ['en' => 'Fresh, chilled', 'ar' => 'طازج، مبرّد'],
                'cut' => ['en' => 'Bone-in cross-cut steaks, ~2.5 cm thick', 'ar' => 'شرائح عرضية بالعظم بسماكة نحو 2.5 سم'],
                'packaging' => ['en' => 'Film-sealed tray, two steaks per pack', 'ar' => 'عبوة مغلّفة بإحكام، شريحتان في العبوة'],
                'weight' => ['en' => '220 g – 280 g per steak', 'ar' => '220 – 280 جم للشريحة'],
            ],
            'storage' => [
                'en' => 'Keep at 0–4 °C and cook within two days. Freeze at −18 °C for up to three months.',
                'ar' => 'تُحفظ عند 0–4 مئوية وتُطهى خلال يومين. يمكن تجميدها عند −18 مئوية لمدة تصل إلى ثلاثة أشهر.',
            ],
            'cooking' => [
                'en' => [
                    'Grill over medium coals for five minutes a side.',
                    'Roast at 190 °C with lemon slices tucked into the cavity.',
                    'Poach in court-bouillon and serve cold with dill mayonnaise.',
                ],
                'ar' => [
                    'اشوها على فحم متوسط الحرارة خمس دقائق لكل جهة.',
                    'اخبزها على 190 مئوية مع شرائح الليمون داخل التجويف.',
                    'اسلقها في مرق الخضار وقدّمها باردة مع مايونيز الشبت.',
                ],
            ],
            'nutrition' => [
                'en' => 'Rich in omega-3 fatty acids, protein, vitamin D and vitamin B12.',
                'ar' => 'غنية بأحماض أوميغا-3 والبروتين وفيتامين د وفيتامين ب12.',
            ],
            'seo' => [
                'title' => 'Salmon Steaks, Bone-In | Cut to Order',
                'description' => 'Fresh Norwegian salmon steaks on the bone, 220–280 g each, cut about 2.5 cm thick. The best salmon cut for the barbecue.',
            ],
            'tags' => ['salmon', 'steaks', 'grilling fish'],
            'variants' => null,
        ],

        /* ---------------------------------------------------------------
         | Shellfish · shrimp and prawns
         --------------------------------------------------------------- */

        [
            'sku' => 'BW-SHR-JUMBO-20',
            'slug' => 'jumbo-tiger-prawns',
            'category' => 'shellfish',
            'sub_category' => 'shrimp-and-prawns',
            'image' => 'shrimp-on-ice.webp',
            'unit' => 'kg',
            'price' => 89.00,
            'compare_at' => 99.00,
            'stock' => 70,
            'name' => ['en' => 'Jumbo Tiger Prawns', 'ar' => 'روبيان النمر العملاق'],
            'short' => [
                'en' => 'Large head-on tiger prawns, 8–10 pieces per kilo — the centrepiece shellfish for a grill.',
                'ar' => 'روبيان نمر كبير برأسه، 8–10 حبات في الكيلو — نجم المائدة من القشريات على الشواية.',
            ],
            'intro' => [
                'en' => 'Tiger prawns are sold head-on because the head carries most of the flavour: split them along the back, grill them shell-side down and the juices baste the meat as it cooks. Ask for them peeled and deveined if you are cooking a curry or a pasta instead.',
                'ar' => 'يُباع روبيان النمر برأسه لأن الرأس يحمل معظم النكهة: شقّه من الظهر واشوه على جهة القشرة فتتشرب اللحمة عصارتها أثناء الطهي. واطلبه مقشراً ومنزوع العرق إن كنت ستحضّر كاري أو معكرونة.',
            ],
            'spec' => [
                'species' => ['en' => 'Giant tiger prawn', 'ar' => 'الروبيان النمر العملاق'],
                'scientific' => 'Penaeus monodon',
                'origin' => ['en' => 'Farmed in India', 'ar' => 'مستزرع في الهند'],
                'source' => ['en' => 'Farmed', 'ar' => 'استزراع'],
                'condition' => ['en' => 'Fresh, chilled on ice', 'ar' => 'طازج، محفوظ على الثلج'],
                'cut' => ['en' => 'Head-on shell-on, or peeled and deveined', 'ar' => 'برأسه وقشره، أو مقشّر ومنزوع العرق'],
                'packaging' => ['en' => 'Packed on ice, 1 kg', 'ar' => 'يُعبأ على الثلج، 1 كجم'],
                'weight' => ['en' => '8–10 pieces per kilo', 'ar' => '8–10 حبات في الكيلو'],
            ],
            'storage' => [
                'en' => 'Keep on ice at 0–4 °C and cook within 24 hours. Freeze at −18 °C for up to three months; thaw in the fridge, never in water.',
                'ar' => 'يُحفظ على الثلج عند 0–4 مئوية ويُطهى خلال 24 ساعة. يمكن تجميده عند −18 مئوية لمدة تصل إلى ثلاثة أشهر، ويُذاب في الثلاجة لا في الماء.',
            ],
            'cooking' => [
                'en' => [
                    'Butterfly along the back, brush with garlic butter and grill for three minutes.',
                    'Roast whole at 220 °C with chilli, lemon and olive oil.',
                    'Peel and stir-fry for 90 seconds — any longer and they toughen.',
                ],
                'ar' => [
                    'شقّه من الظهر وادهنه بزبدة الثوم واشوه ثلاث دقائق.',
                    'حمّره كاملاً على 220 مئوية مع الفلفل الحار والليمون وزيت الزيتون.',
                    'قشّره وقلّبه سريعاً 90 ثانية فقط — أكثر من ذلك يجعله قاسياً.',
                ],
            ],
            'nutrition' => [
                'en' => 'High in protein and very low in fat, and a good source of selenium and vitamin B12.',
                'ar' => 'غني بالبروتين وقليل الدهون جداً، ومصدر جيد للسيلينيوم وفيتامين ب12.',
            ],
            'seo' => [
                'title' => 'Jumbo Tiger Prawns | Head-On, 8–10 per Kilo',
                'description' => 'Fresh jumbo tiger prawns, 8–10 pieces per kilo, sold head-on or peeled and deveined. Chilled on ice and ready for the grill.',
            ],
            'tags' => ['prawns', 'shrimp', 'tiger prawns'],
            'variants' => [
                'axes' => ['Preparation' => ['Head On', 'Peeled']],
                'combinations' => [
                    'HeadOn' => ['price' => 89.00, 'qty' => 45, 'sku' => 'BW-SHR-JUMBO-20-HO'],
                    'Peeled' => ['price' => 119.00, 'qty' => 25, 'sku' => 'BW-SHR-JUMBO-20-PD'],
                ],
            ],
        ],

        [
            'sku' => 'BW-SHR-LARGE-21',
            'slug' => 'large-white-shrimp',
            'category' => 'shellfish',
            'sub_category' => 'shrimp-and-prawns',
            'image' => 'shrimp-on-ice.webp',
            'unit' => 'kg',
            'price' => 69.00,
            'compare_at' => null,
            'stock' => 85,
            'name' => ['en' => 'Large White Shrimp', 'ar' => 'الروبيان الأبيض الكبير'],
            'short' => [
                'en' => 'Gulf white shrimp, 20–30 per kilo — sweeter and softer than tiger prawns, and the local favourite for machboos.',
                'ar' => 'روبيان أبيض خليجي، 20–30 حبة في الكيلو — أحلى وألين من روبيان النمر، والمفضل محلياً في المجبوس.',
            ],
            'intro' => [
                'en' => 'Indian white shrimp from Gulf waters have thinner shells and a sweeter, softer meat than farmed tiger prawns. They are the size most cooks want for rice dishes and stews, where the shrimp goes in at the very end of the cooking.',
                'ar' => 'الروبيان الأبيض الهندي من مياه الخليج له قشرة أرق ولحم أحلى وألين من روبيان النمر المستزرع. وهو الحجم الذي يفضّله معظم الطهاة لأطباق الأرز واليخنات، حيث يُضاف الروبيان في نهاية الطهي تماماً.',
            ],
            'spec' => [
                'species' => ['en' => 'Indian white prawn', 'ar' => 'الروبيان الأبيض الهندي'],
                'scientific' => 'Penaeus indicus',
                'origin' => ['en' => 'Arabian Gulf', 'ar' => 'الخليج العربي'],
                'source' => ['en' => 'Wild-caught', 'ar' => 'صيد بري'],
                'condition' => ['en' => 'Fresh, chilled on ice', 'ar' => 'طازج، محفوظ على الثلج'],
                'cut' => ['en' => 'Head-on shell-on, or peeled and deveined', 'ar' => 'برأسه وقشره، أو مقشّر ومنزوع العرق'],
                'packaging' => ['en' => 'Packed on ice, 500 g or 1 kg', 'ar' => 'يُعبأ على الثلج، 500 جم أو 1 كجم'],
                'weight' => ['en' => '20–30 pieces per kilo', 'ar' => '20–30 حبة في الكيلو'],
            ],
            'storage' => [
                'en' => 'Keep on ice at 0–4 °C and cook within 24 hours, or freeze at −18 °C for up to three months.',
                'ar' => 'يُحفظ على الثلج عند 0–4 مئوية ويُطهى خلال 24 ساعة، أو يُجمّد عند −18 مئوية لمدة تصل إلى ثلاثة أشهر.',
            ],
            'cooking' => [
                'en' => [
                    'Fold through spiced rice at the end of cooking for shrimp machboos.',
                    'Sauté in garlic, coriander and lime for two minutes.',
                    'Simmer briefly in a tomato and cumin stew.',
                ],
                'ar' => [
                    'أضفه إلى الأرز المتبّل في نهاية الطهي لتحضير مجبوس الروبيان.',
                    'اقله سريعاً مع الثوم والكزبرة والليمون لمدة دقيقتين.',
                    'اطهه لفترة قصيرة في يخنة الطماطم والكمون.',
                ],
            ],
            'nutrition' => [
                'en' => 'A low-fat source of protein, providing selenium, iodine and vitamin B12.',
                'ar' => 'مصدر للبروتين قليل الدهون، ويوفر السيلينيوم واليود وفيتامين ب12.',
            ],
            'seo' => [
                'title' => 'Large White Shrimp | Gulf-Caught, 20–30 per Kilo',
                'description' => 'Fresh Gulf white shrimp, 20–30 pieces per kilo, sold head-on or peeled and deveined. Sweet, soft meat for machboos and stews.',
            ],
            'tags' => ['shrimp', 'gulf shrimp', 'shellfish'],
            'variants' => [
                'axes' => ['Preparation' => ['Head On', 'Peeled']],
                'combinations' => [
                    'HeadOn' => ['price' => 69.00, 'qty' => 50, 'sku' => 'BW-SHR-LARGE-21-HO'],
                    'Peeled' => ['price' => 95.00, 'qty' => 35, 'sku' => 'BW-SHR-LARGE-21-PD'],
                ],
            ],
        ],

        [
            'sku' => 'BW-SHR-SMALL-22',
            'slug' => 'small-peeled-shrimp',
            'category' => 'shellfish',
            'sub_category' => 'shrimp-and-prawns',
            'image' => 'shrimp-on-ice.webp',
            'unit' => 'kg',
            'price' => 45.00,
            'compare_at' => 52.00,
            'stock' => 95,
            'name' => ['en' => 'Small Peeled Shrimp', 'ar' => 'الروبيان الصغير المقشّر'],
            'short' => [
                'en' => 'Small peeled and deveined shrimp — no preparation, straight into rice, pasta or a salad.',
                'ar' => 'روبيان صغير مقشّر ومنزوع العرق — بلا تحضير، يُضاف مباشرة إلى الأرز أو المعكرونة أو السلطة.',
            ],
            'intro' => [
                'en' => 'These are the small Gulf shrimp, already peeled and deveined, sold for cooks who want the flavour without the work. They cook in about a minute, so add them last: to fried rice as it comes off the heat, to pasta with the sauce, or blanched and chilled for a salad.',
                'ar' => 'هذا هو الروبيان الخليجي الصغير، مقشّر ومنزوع العرق مسبقاً، لمن يريد النكهة دون عناء التحضير. ينضج خلال دقيقة تقريباً، لذا أضفه في النهاية: إلى الأرز المقلي عند رفعه عن النار، أو إلى المعكرونة مع الصلصة، أو مسلوقاً سلقاً سريعاً ومبرداً للسلطة.',
            ],
            'spec' => [
                'species' => ['en' => 'Gulf shrimp', 'ar' => 'روبيان الخليج'],
                'scientific' => 'Penaeus spp.',
                'origin' => ['en' => 'Arabian Gulf', 'ar' => 'الخليج العربي'],
                'source' => ['en' => 'Wild-caught', 'ar' => 'صيد بري'],
                'condition' => ['en' => 'Fresh, chilled', 'ar' => 'طازج، مبرّد'],
                'cut' => ['en' => 'Peeled and deveined, tail off', 'ar' => 'مقشّر ومنزوع العرق وبدون ذيل'],
                'packaging' => ['en' => 'Film-sealed tray, 500 g', 'ar' => 'عبوة مغلّفة بإحكام، 500 جم'],
                'weight' => ['en' => '60–80 pieces per kilo', 'ar' => '60–80 حبة في الكيلو'],
            ],
            'storage' => [
                'en' => 'Keep at 0–4 °C and use within 24 hours, or freeze at −18 °C for up to two months.',
                'ar' => 'يُحفظ عند 0–4 مئوية ويُستخدم خلال 24 ساعة، أو يُجمّد عند −18 مئوية لمدة تصل إلى شهرين.',
            ],
            'cooking' => [
                'en' => [
                    'Toss into fried rice in the last minute of cooking.',
                    'Stir through pasta with garlic, chilli and parsley.',
                    'Blanch for one minute, chill, and fold into a lemon salad.',
                ],
                'ar' => [
                    'أضفه إلى الأرز المقلي في الدقيقة الأخيرة من الطهي.',
                    'قلّبه مع المعكرونة والثوم والفلفل الحار والبقدونس.',
                    'اسلقه دقيقة واحدة وبرّده وأضفه إلى سلطة الليمون.',
                ],
            ],
            'nutrition' => [
                'en' => 'High in protein and low in fat, with useful amounts of iodine and selenium.',
                'ar' => 'غني بالبروتين وقليل الدهون، ويحتوي كميات مفيدة من اليود والسيلينيوم.',
            ],
            'seo' => [
                'title' => 'Small Peeled Shrimp | Deveined, Ready to Cook',
                'description' => 'Fresh small Gulf shrimp, peeled and deveined, 60–80 pieces per kilo in 500 g packs. Ready for rice, pasta and salads.',
            ],
            'tags' => ['shrimp', 'peeled shrimp', 'shellfish'],
            'variants' => null,
        ],

        /* ---------------------------------------------------------------
         | Shellfish · crab and lobster
         --------------------------------------------------------------- */

        [
            'sku' => 'BW-CRB-BROWN-23',
            'slug' => 'whole-brown-crab',
            'category' => 'shellfish',
            'sub_category' => 'crab-and-lobster',
            'image' => 'crab-and-lobster.webp',
            'unit' => 'kg',
            'price' => 75.00,
            'compare_at' => null,
            'stock' => 30,
            'name' => ['en' => 'Whole Brown Crab', 'ar' => 'سرطان البحر البني الكامل'],
            'short' => [
                'en' => 'Whole brown crab with heavy claws — sweet white claw meat and rich brown meat in the shell.',
                'ar' => 'سرطان بحر بني كامل بكماشات ثقيلة — لحم أبيض حلو في الكماشات ولحم بني غني داخل الصدفة.',
            ],
            'intro' => [
                'en' => 'Brown crab gives two very different meats: white meat from the claws and legs, sweet and fibrous, and dark brown meat from the body, soft and intense. Buy it heavy for its size — that is the reliable sign of a full crab — and serve it simply, cracked with mayonnaise and bread.',
                'ar' => 'يعطي السرطان البني نوعين مختلفين من اللحم: لحم أبيض من الكماشات والأرجل، حلو وليفي، ولحم بني داكن من الجسم، طري وقوي النكهة. اخترها ثقيلة الوزن بالنسبة لحجمها — فتلك علامة موثوقة على امتلائها — وقدّمها ببساطة مكسورة مع المايونيز والخبز.',
            ],
            'spec' => [
                'species' => ['en' => 'Brown crab', 'ar' => 'سرطان البحر البني'],
                'scientific' => 'Cancer pagurus',
                'origin' => ['en' => 'North-East Atlantic', 'ar' => 'شمال شرق المحيط الأطلسي'],
                'source' => ['en' => 'Wild-caught', 'ar' => 'صيد بري'],
                'condition' => ['en' => 'Fresh, chilled on ice', 'ar' => 'طازج، محفوظ على الثلج'],
                'cut' => ['en' => 'Whole, uncooked', 'ar' => 'كامل، غير مطهو'],
                'packaging' => ['en' => 'Packed on ice in an insulated box', 'ar' => 'يُعبأ على الثلج داخل صندوق معزول'],
                'weight' => ['en' => '700 g – 1.2 kg per crab', 'ar' => '700 جم – 1.2 كجم للحبة'],
            ],
            'storage' => [
                'en' => 'Keep on ice at 0–4 °C and cook the same day. Once cooked and picked, the meat keeps a further 24 hours refrigerated.',
                'ar' => 'يُحفظ على الثلج عند 0–4 مئوية ويُطهى في اليوم نفسه. وبعد الطهي واستخراج اللحم يبقى صالحاً 24 ساعة إضافية في الثلاجة.',
            ],
            'cooking' => [
                'en' => [
                    'Boil in heavily salted water for 12 minutes per 500 g, then cool before picking.',
                    'Crack the claws, dress the shell and serve cold with mayonnaise.',
                    'Stir-fry pieces with ginger, garlic and spring onion.',
                ],
                'ar' => [
                    'اسلقه في ماء مملّح بغزارة 12 دقيقة لكل 500 جم ثم برّده قبل استخراج اللحم.',
                    'اكسر الكماشات وجهّز الصدفة وقدّمه بارداً مع المايونيز.',
                    'قلّب قطعه سريعاً مع الزنجبيل والثوم والبصل الأخضر.',
                ],
            ],
            'nutrition' => [
                'en' => 'A good source of protein, zinc and vitamin B12; the brown meat is higher in fat than the white.',
                'ar' => 'مصدر جيد للبروتين والزنك وفيتامين ب12؛ واللحم البني أعلى في الدهون من اللحم الأبيض.',
            ],
            'seo' => [
                'title' => 'Whole Brown Crab | Live-Chilled, 700 g – 1.2 kg',
                'description' => 'Whole uncooked brown crab, 700 g – 1.2 kg each, chilled on ice. Sweet white claw meat and rich brown body meat.',
            ],
            'tags' => ['crab', 'shellfish'],
            'variants' => null,
        ],

        [
            'sku' => 'BW-LOB-WHOLE-24',
            'slug' => 'whole-fresh-lobster',
            'category' => 'shellfish',
            'sub_category' => 'crab-and-lobster',
            'image' => 'crab-and-lobster.webp',
            'unit' => 'kg',
            'price' => 165.00,
            'compare_at' => 189.00,
            'stock' => 20,
            'name' => ['en' => 'Whole Fresh Lobster', 'ar' => 'الاستاكوزا الطازجة الكاملة'],
            'short' => [
                'en' => 'Whole clawed lobster with dense, sweet tail meat — the occasion shellfish, best split and grilled.',
                'ar' => 'استاكوزا كاملة بكماشات ولحم ذيل كثيف حلو المذاق — قشريات المناسبات، وأفضل طهيها مشقوقة ومشوية.',
            ],
            'intro' => [
                'en' => 'European lobster has firmer, sweeter meat than warm-water species, and both the tail and the claws are worth the work. The simplest treatment is the best: split lengthways, brush the flesh with butter and grill shell-side down until just opaque.',
                'ar' => 'الاستاكوزا الأوروبية لحمها أكثر تماسكاً وحلاوة من أنواع المياه الدافئة، وذيلها وكماشاتها يستحقان العناء معاً. وأبسط طريقة هي الأفضل: تُشق طولياً، ويُدهن لحمها بالزبدة، وتُشوى على جهة القشرة حتى يصبح اللحم معتماً فقط.',
            ],
            'spec' => [
                'species' => ['en' => 'European lobster', 'ar' => 'الاستاكوزا الأوروبية'],
                'scientific' => 'Homarus gammarus',
                'origin' => ['en' => 'North-East Atlantic', 'ar' => 'شمال شرق المحيط الأطلسي'],
                'source' => ['en' => 'Wild-caught', 'ar' => 'صيد بري'],
                'condition' => ['en' => 'Fresh, chilled on ice', 'ar' => 'طازجة، محفوظة على الثلج'],
                'cut' => ['en' => 'Whole, uncooked', 'ar' => 'كاملة، غير مطهوة'],
                'packaging' => ['en' => 'Individually packed on ice', 'ar' => 'تُعبأ فردياً على الثلج'],
                'weight' => ['en' => '500 g – 800 g per lobster', 'ar' => '500 – 800 جم للحبة'],
            ],
            'storage' => [
                'en' => 'Keep on ice at 0–4 °C and cook the same day. Do not store in fresh water.',
                'ar' => 'تُحفظ على الثلج عند 0–4 مئوية وتُطهى في اليوم نفسه. لا تُحفظ في الماء العذب.',
            ],
            'cooking' => [
                'en' => [
                    'Split lengthways, butter the flesh and grill shell-side down for eight minutes.',
                    'Boil for 10–12 minutes, then serve cold with lemon mayonnaise.',
                    'Roast halves at 220 °C with garlic and herb butter.',
                ],
                'ar' => [
                    'شقّها طولياً وادهن اللحم بالزبدة واشوها على جهة القشرة ثماني دقائق.',
                    'اسلقها 10–12 دقيقة ثم قدّمها باردة مع مايونيز الليمون.',
                    'حمّر نصفيها على 220 مئوية مع زبدة الثوم والأعشاب.',
                ],
            ],
            'nutrition' => [
                'en' => 'High in protein and low in fat, and a source of zinc, copper and vitamin B12.',
                'ar' => 'غنية بالبروتين وقليلة الدهون، ومصدر للزنك والنحاس وفيتامين ب12.',
            ],
            'seo' => [
                'title' => 'Whole Fresh Lobster | 500–800 g, Chilled on Ice',
                'description' => 'Whole uncooked European lobster, 500–800 g each, delivered chilled on ice. Dense, sweet tail and claw meat.',
            ],
            'tags' => ['lobster', 'shellfish'],
            'variants' => null,
        ],

        /* ---------------------------------------------------------------
         | Shellfish · mussels and clams
         --------------------------------------------------------------- */

        [
            'sku' => 'BW-MUS-BLACK-25',
            'slug' => 'black-mussels',
            'category' => 'shellfish',
            'sub_category' => 'mussels-and-clams',
            'image' => 'mussels-octopus-squid-shrimp.webp',
            'unit' => 'kg',
            'price' => 38.00,
            'compare_at' => null,
            'stock' => 60,
            'name' => ['en' => 'Black Mussels', 'ar' => 'بلح البحر الأسود'],
            'short' => [
                'en' => 'Farmed black mussels in the shell, cleaned and debearded — dinner in eight minutes.',
                'ar' => 'بلح بحر أسود مستزرع بالصدفة، منظّف ومنزوع اللحية — عشاء جاهز في ثماني دقائق.',
            ],
            'intro' => [
                'en' => 'Mussels arrive cleaned and debearded, so all that is left is a quick sort: discard any that are broken, or that stay open when tapped. They cook in a covered pan in the time it takes to toast bread, and the liquor they release is the sauce.',
                'ar' => 'يصلك بلح البحر منظّفاً ومنزوع اللحية، فلا يتبقى سوى فرز سريع: تخلّص من أي صدفة مكسورة أو تبقى مفتوحة عند النقر عليها. وينضج في مقلاة مغطاة خلال الوقت اللازم لتحميص الخبز، والسائل الذي يخرج منه هو الصلصة نفسها.',
            ],
            'spec' => [
                'species' => ['en' => 'Blue mussel', 'ar' => 'بلح البحر الأزرق'],
                'scientific' => 'Mytilus edulis',
                'origin' => ['en' => 'Farmed in Spain', 'ar' => 'مستزرع في إسبانيا'],
                'source' => ['en' => 'Farmed', 'ar' => 'استزراع'],
                'condition' => ['en' => 'Fresh, live, chilled', 'ar' => 'طازج، حي، مبرّد'],
                'cut' => ['en' => 'In the shell, cleaned and debearded', 'ar' => 'بالصدفة، منظّف ومنزوع اللحية'],
                'packaging' => ['en' => 'Net bag, 1 kg', 'ar' => 'كيس شبكي، 1 كجم'],
                'weight' => ['en' => '1 kg per bag', 'ar' => '1 كجم للكيس'],
            ],
            'storage' => [
                'en' => 'Keep in the fridge at 0–4 °C covered with a damp cloth, never in sealed water, and cook within 24 hours. Do not freeze live.',
                'ar' => 'يُحفظ في الثلاجة عند 0–4 مئوية مغطى بقطعة قماش مبللة، وليس في ماء مغلق، ويُطهى خلال 24 ساعة. لا يُجمّد وهو حي.',
            ],
            'cooking' => [
                'en' => [
                    'Steam in a covered pan with garlic, white wine and parsley until the shells open.',
                    'Cook in a tomato and chilli sauce and serve over linguine.',
                    'Steam open, then grill on the half shell with garlic butter and breadcrumbs.',
                ],
                'ar' => [
                    'اطهه على البخار في مقلاة مغطاة مع الثوم والبقدونس حتى تتفتح الأصداف.',
                    'اطهه في صلصة الطماطم والفلفل الحار وقدّمه فوق المعكرونة.',
                    'افتحه على البخار ثم اشوه في نصف صدفة مع زبدة الثوم والبقسماط.',
                ],
            ],
            'nutrition' => [
                'en' => 'High in protein and notably rich in iron, selenium and vitamin B12, with very little fat.',
                'ar' => 'غني بالبروتين وبالحديد والسيلينيوم وفيتامين ب12 بشكل ملحوظ، ودهونه قليلة جداً.',
            ],
            'seo' => [
                'title' => 'Black Mussels | Cleaned and Debearded, 1 kg Bag',
                'description' => 'Fresh farmed black mussels in the shell, cleaned and debearded, in 1 kg bags. Steam them in eight minutes with garlic and parsley.',
            ],
            'tags' => ['mussels', 'shellfish'],
            'variants' => null,
        ],

        [
            'sku' => 'BW-CLM-FRESH-26',
            'slug' => 'fresh-clams',
            'category' => 'shellfish',
            'sub_category' => 'mussels-and-clams',
            'image' => null,
            'unit' => 'kg',
            'price' => 32.00,
            'compare_at' => null,
            'stock' => 45,
            'name' => ['en' => 'Fresh Clams', 'ar' => 'المحار الطازج'],
            'short' => [
                'en' => 'Small hard-shell clams with a clean, briny flavour — the base of a good vongole.',
                'ar' => 'محار صغير بصدفة صلبة ونكهة بحرية نظيفة — أساس طبق الفونجولي الجيد.',
            ],
            'intro' => [
                'en' => 'Clams carry more salt and less sweetness than mussels, which is why they work so well with pasta, garlic and white wine. Rinse them under cold running water and check each one before cooking: a clam that will not close is a clam to throw away.',
                'ar' => 'يحمل المحار ملوحة أعلى وحلاوة أقل من بلح البحر، ولهذا ينسجم تماماً مع المعكرونة والثوم. اشطفه تحت ماء بارد جارٍ وافحص كل حبة قبل الطهي: الحبة التي لا تنغلق يجب التخلص منها.',
            ],
            'spec' => [
                'species' => ['en' => 'Carpet shell clam', 'ar' => 'المحار الصدفي'],
                'scientific' => 'Ruditapes decussatus',
                'origin' => ['en' => 'Mediterranean Sea', 'ar' => 'البحر الأبيض المتوسط'],
                'source' => ['en' => 'Wild-caught', 'ar' => 'صيد بري'],
                'condition' => ['en' => 'Fresh, live, chilled', 'ar' => 'طازج، حي، مبرّد'],
                'cut' => ['en' => 'In the shell, purged', 'ar' => 'بالصدفة، منقّى من الرمل'],
                'packaging' => ['en' => 'Net bag, 500 g', 'ar' => 'كيس شبكي، 500 جم'],
                'weight' => ['en' => '500 g per bag', 'ar' => '500 جم للكيس'],
            ],
            'storage' => [
                'en' => 'Keep at 0–4 °C covered with a damp cloth and cook within 24 hours. Discard any shell that does not close when tapped.',
                'ar' => 'يُحفظ عند 0–4 مئوية مغطى بقطعة قماش مبللة ويُطهى خلال 24 ساعة. تخلّص من أي صدفة لا تنغلق عند النقر عليها.',
            ],
            'cooking' => [
                'en' => [
                    'Cook with garlic, chilli, parsley and white wine, then toss through spaghetti.',
                    'Steam open and add to a seafood rice at the end of cooking.',
                    'Simmer in a light broth with ginger and coriander.',
                ],
                'ar' => [
                    'اطهه مع الثوم والفلفل الحار والبقدونس ثم قلّبه مع السباغيتي.',
                    'افتحه على البخار وأضفه إلى أرز المأكولات البحرية في نهاية الطهي.',
                    'اطهه في مرق خفيف مع الزنجبيل والكزبرة.',
                ],
            ],
            'nutrition' => [
                'en' => 'A low-fat source of protein that is particularly high in iron and vitamin B12.',
                'ar' => 'مصدر للبروتين قليل الدهون وغني بشكل خاص بالحديد وفيتامين ب12.',
            ],
            'seo' => [
                'title' => 'Fresh Clams | Purged, 500 g Bags',
                'description' => 'Fresh live clams in the shell, purged and ready to cook, in 500 g bags. Clean, briny flavour for pasta vongole and seafood rice.',
            ],
            'tags' => ['clams', 'shellfish'],
            'variants' => null,
        ],

        /* ---------------------------------------------------------------
         | Squid and octopus
         --------------------------------------------------------------- */

        [
            'sku' => 'BW-SQD-WHOLE-27',
            'slug' => 'fresh-whole-squid',
            'category' => 'squid-and-octopus',
            'sub_category' => 'squid-and-calamari',
            'image' => 'squid-and-calamari.webp',
            'unit' => 'kg',
            'price' => 45.00,
            'compare_at' => 52.00,
            'stock' => 65,
            'name' => ['en' => 'Fresh Whole Squid', 'ar' => 'الحبار الطازج الكامل'],
            'short' => [
                'en' => 'Whole squid, cleaned on request — tubes for stuffing, tentacles for the grill.',
                'ar' => 'حبار كامل يُنظّف عند الطلب — الأنابيب للحشو والأذرع للشوي.',
            ],
            'intro' => [
                'en' => 'Squid follows one rule: cook it for two minutes or for forty, never in between. Fast and hot keeps it tender; long and slow breaks it down again. Buy it whole and you get both the tubes, which are ideal stuffed and braised, and the tentacles, which are the best part off a hot grill.',
                'ar' => 'للحبار قاعدة واحدة: اطهه دقيقتين أو أربعين دقيقة، ولا شيء بينهما. فالطهي السريع على نار عالية يبقيه طرياً، والطهي الطويل البطيء يعيد تطريته. وعند شرائه كاملاً تحصل على الأنابيب المثالية للحشو والطهي البطيء، وعلى الأذرع وهي ألذ ما يُشوى على نار قوية.',
            ],
            'spec' => [
                'species' => ['en' => 'European squid', 'ar' => 'الحبار الأوروبي'],
                'scientific' => 'Loligo vulgaris',
                'origin' => ['en' => 'Arabian Sea, Oman', 'ar' => 'بحر العرب، عُمان'],
                'source' => ['en' => 'Wild-caught', 'ar' => 'صيد بري'],
                'condition' => ['en' => 'Fresh, chilled on ice', 'ar' => 'طازج، محفوظ على الثلج'],
                'cut' => ['en' => 'Whole; cleaned into tubes and tentacles on request', 'ar' => 'كامل؛ يُنظّف إلى أنابيب وأذرع عند الطلب'],
                'packaging' => ['en' => 'Packed on ice, 1 kg', 'ar' => 'يُعبأ على الثلج، 1 كجم'],
                'weight' => ['en' => '150 g – 300 g per squid', 'ar' => '150 – 300 جم للحبة'],
            ],
            'storage' => [
                'en' => 'Keep at 0–4 °C and use within 24 hours. Squid freezes well at −18 °C for up to three months and is often more tender after thawing.',
                'ar' => 'يُحفظ عند 0–4 مئوية ويُستخدم خلال 24 ساعة. يتحمل الحبار التجميد عند −18 مئوية لمدة تصل إلى ثلاثة أشهر، وغالباً ما يصبح أطرى بعد الإذابة.',
            ],
            'cooking' => [
                'en' => [
                    'Score the tubes, grill for 90 seconds a side and dress with lemon and oil.',
                    'Stuff the tubes with rice and herbs and braise in tomato for 40 minutes.',
                    'Slice into rings, flour lightly and deep-fry for two minutes.',
                ],
                'ar' => [
                    'اعمل شقوقاً في الأنابيب واشوها 90 ثانية لكل جهة وقدّمها مع الليمون والزيت.',
                    'احشُ الأنابيب بالأرز والأعشاب واطهها في الطماطم 40 دقيقة.',
                    'قطّعه حلقات وغلّفها بالدقيق واقلها دقيقتين في زيت غزير.',
                ],
            ],
            'nutrition' => [
                'en' => 'High in protein and low in fat, and a source of selenium, copper and vitamin B12.',
                'ar' => 'غني بالبروتين وقليل الدهون، ومصدر للسيلينيوم والنحاس وفيتامين ب12.',
            ],
            'seo' => [
                'title' => 'Fresh Whole Squid | Cleaned to Order',
                'description' => 'Fresh whole squid, 150–300 g each, cleaned into tubes and tentacles on request. Chilled on ice and ready for the grill or the fryer.',
            ],
            'tags' => ['squid', 'calamari', 'seafood'],
            'variants' => [
                'axes' => ['Cut' => ['Whole', 'Tubes', 'Rings']],
                'combinations' => [
                    'Whole' => ['price' => 45.00, 'qty' => 35, 'sku' => 'BW-SQD-WHOLE-27-WHL'],
                    'Tubes' => ['price' => 58.00, 'qty' => 20, 'sku' => 'BW-SQD-WHOLE-27-TUB'],
                    'Rings' => ['price' => 62.00, 'qty' => 10, 'sku' => 'BW-SQD-WHOLE-27-RNG'],
                ],
            ],
        ],

        [
            'sku' => 'BW-SQD-RINGS-28',
            'slug' => 'calamari-rings',
            'category' => 'squid-and-octopus',
            'sub_category' => 'squid-and-calamari',
            'image' => 'squid-and-calamari.webp',
            'unit' => 'kg',
            'price' => 55.00,
            'compare_at' => 62.00,
            'stock' => 70,
            'name' => ['en' => 'Calamari Rings', 'ar' => 'حلقات الكاليماري'],
            'short' => [
                'en' => 'Cleaned squid tubes cut into even rings — no membranes, no beaks, straight into the batter.',
                'ar' => 'أنابيب حبار منظّفة ومقطّعة حلقات متساوية — بلا أغشية أو مناقير، جاهزة مباشرة للتغليف والقلي.',
            ],
            'intro' => [
                'en' => 'These are squid tubes that have already been skinned, cleaned and cut into rings of an even width, so they fry at the same rate and no piece comes out rubbery while another is still pale. Keep the coating light and the oil hot: two minutes is the whole cooking time.',
                'ar' => 'هذه أنابيب حبار مقشورة ومنظّفة ومقطّعة إلى حلقات متساوية العرض، فتُقلى بالوتيرة نفسها ولا تخرج قطعة مطاطية وأخرى ما زالت باهتة. اجعل طبقة التغليف خفيفة والزيت ساخناً: دقيقتان هما زمن الطهي كاملاً.',
            ],
            'spec' => [
                'species' => ['en' => 'European squid', 'ar' => 'الحبار الأوروبي'],
                'scientific' => 'Loligo vulgaris',
                'origin' => ['en' => 'Arabian Sea, Oman', 'ar' => 'بحر العرب، عُمان'],
                'source' => ['en' => 'Wild-caught', 'ar' => 'صيد بري'],
                'condition' => ['en' => 'Fresh, chilled', 'ar' => 'طازجة، مبرّدة'],
                'cut' => ['en' => 'Cleaned tubes, cut into 1 cm rings', 'ar' => 'أنابيب منظّفة مقطّعة حلقات بعرض 1 سم'],
                'packaging' => ['en' => 'Film-sealed tray, 500 g', 'ar' => 'عبوة مغلّفة بإحكام، 500 جم'],
                'weight' => ['en' => '500 g per pack', 'ar' => '500 جم للعبوة'],
            ],
            'storage' => [
                'en' => 'Keep at 0–4 °C and use within 24 hours, or freeze at −18 °C for up to three months.',
                'ar' => 'تُحفظ عند 0–4 مئوية وتُستخدم خلال 24 ساعة، أو تُجمّد عند −18 مئوية لمدة تصل إلى ثلاثة أشهر.',
            ],
            'cooking' => [
                'en' => [
                    'Dust in seasoned flour and deep-fry at 190 °C for two minutes.',
                    'Sear in a very hot pan with garlic and chilli for 90 seconds.',
                    'Add to a seafood stew in the final minute of cooking.',
                ],
                'ar' => [
                    'غلّفها بالدقيق المتبّل واقلها على 190 مئوية لمدة دقيقتين.',
                    'اقلها في مقلاة شديدة السخونة مع الثوم والفلفل الحار 90 ثانية.',
                    'أضفها إلى يخنة المأكولات البحرية في الدقيقة الأخيرة.',
                ],
            ],
            'nutrition' => [
                'en' => 'High in protein and naturally low in fat before frying, with selenium and vitamin B12.',
                'ar' => 'غنية بالبروتين وقليلة الدهون طبيعياً قبل القلي، وتحتوي السيلينيوم وفيتامين ب12.',
            ],
            'seo' => [
                'title' => 'Calamari Rings | Cleaned Squid Tubes, 500 g Packs',
                'description' => 'Fresh calamari rings cut from cleaned squid tubes, 1 cm wide, in 500 g packs. Ready to flour and fry in two minutes.',
            ],
            'tags' => ['calamari', 'squid', 'seafood'],
            'variants' => null,
        ],

        [
            'sku' => 'BW-OCT-WHOLE-29',
            'slug' => 'fresh-whole-octopus',
            'category' => 'squid-and-octopus',
            'sub_category' => 'octopus',
            'image' => 'mussels-octopus-squid-shrimp.webp',
            'unit' => 'kg',
            'price' => 65.00,
            'compare_at' => null,
            'stock' => 35,
            'name' => ['en' => 'Fresh Whole Octopus', 'ar' => 'الأخطبوط الطازج الكامل'],
            'short' => [
                'en' => 'Whole octopus, cleaned and ready to simmer — tender after 45 minutes, spectacular after the grill.',
                'ar' => 'أخطبوط كامل منظّف وجاهز للسلق — يصبح طرياً بعد 45 دقيقة، ورائعاً بعد الشوي.',
            ],
            'intro' => [
                'en' => 'Octopus needs patience and then heat. Simmer it gently until a knife slides into the thickest part of a tentacle, let it cool in its own liquid, then char it on the grill. Skipping the first step is the single most common reason octopus turns out tough.',
                'ar' => 'يحتاج الأخطبوط إلى صبر ثم إلى حرارة عالية. اسلقه بلطف حتى تنزلق السكين في أسمك جزء من الذراع، واتركه يبرد في سائل سلقه، ثم اشوه حتى يتفحّم قليلاً. وتخطي الخطوة الأولى هو السبب الأول لخروج الأخطبوط قاسياً.',
            ],
            'spec' => [
                'species' => ['en' => 'Common octopus', 'ar' => 'الأخطبوط الشائع'],
                'scientific' => 'Octopus vulgaris',
                'origin' => ['en' => 'Arabian Sea, Oman', 'ar' => 'بحر العرب، عُمان'],
                'source' => ['en' => 'Wild-caught', 'ar' => 'صيد بري'],
                'condition' => ['en' => 'Fresh, chilled on ice', 'ar' => 'طازج، محفوظ على الثلج'],
                'cut' => ['en' => 'Whole, cleaned (beak and ink sac removed)', 'ar' => 'كامل ومنظّف (منزوع المنقار وكيس الحبر)'],
                'packaging' => ['en' => 'Vacuum-packed, 1–2 kg', 'ar' => 'مغلّف بتفريغ الهواء، 1–2 كجم'],
                'weight' => ['en' => '1 kg – 2 kg per octopus', 'ar' => '1 – 2 كجم للحبة'],
            ],
            'storage' => [
                'en' => 'Keep at 0–4 °C and cook within 24 hours. Octopus freezes very well at −18 °C for up to three months, and freezing helps tenderise it.',
                'ar' => 'يُحفظ عند 0–4 مئوية ويُطهى خلال 24 ساعة. يتحمل الأخطبوط التجميد جيداً عند −18 مئوية لمدة تصل إلى ثلاثة أشهر، والتجميد يساعد على تطريته.',
            ],
            'cooking' => [
                'en' => [
                    'Simmer whole for 45–60 minutes, then cool in the cooking liquid.',
                    'Char the cooked tentacles on a hot grill and dress with olive oil and paprika.',
                    'Slice cooked octopus into a salad with potato, celery and lemon.',
                ],
                'ar' => [
                    'اسلقه كاملاً 45–60 دقيقة ثم اتركه يبرد في سائل السلق.',
                    'اشوِ الأذرع المسلوقة على نار قوية وقدّمها مع زيت الزيتون والبابريكا.',
                    'قطّعه بعد السلق وأضفه إلى سلطة البطاطس والكرفس والليمون.',
                ],
            ],
            'nutrition' => [
                'en' => 'High in protein and low in fat, and a good source of iron, copper and vitamin B12.',
                'ar' => 'غني بالبروتين وقليل الدهون، ومصدر جيد للحديد والنحاس وفيتامين ب12.',
            ],
            'seo' => [
                'title' => 'Fresh Whole Octopus | Cleaned, 1–2 kg',
                'description' => 'Fresh whole octopus, 1–2 kg each, cleaned with the beak and ink sac removed. Simmer until tender, then finish on the grill.',
            ],
            'tags' => ['octopus', 'seafood'],
            'variants' => null,
        ],
    ],
];
