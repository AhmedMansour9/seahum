{{-- schema.org data for the store itself (name, logo, contact, site search), shown on the home page. --}}
@php
    $structuredStoreUrl = rtrim(config('app.url'), '/').'/';
    $structuredStoreData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            array_filter([
                '@type' => 'OnlineStore',
                '@id' => $structuredStoreUrl.'#store',
                'name' => $web_config['company_name'],
                'url' => $structuredStoreUrl,
                'logo' => $web_config['web_logo']['path'] ?? null,
                'telephone' => $web_config['phone'] ?? null,
                'description' => $robotsMetaContentData?->meta_description ?? null,
            ]),
            [
                '@type' => 'WebSite',
                'name' => $web_config['company_name'],
                'url' => $structuredStoreUrl,
                'inLanguage' => app()->getLocale() === 'en' ? 'en' : 'ar',
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => rtrim(config('app.url'), '/').route('products', [], false).'?name={search_term_string}',
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($structuredStoreData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
