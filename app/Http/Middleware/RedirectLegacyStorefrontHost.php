<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Permanently moves storefront pages opened on the old domain (blue-wiish.com) to the main one,
 * so search engines index shop.sea-hum.store instead of keeping two copies of the site.
 *
 * Only public, indexable pages are moved. The API, app link files, uploads, checkout, payment
 * returns, account and admin pages keep working on the old domain, because older app versions
 * and in-flight payments still use it.
 */
class RedirectLegacyStorefrontHost
{
    /** @var array<int, string> */
    private const PUBLIC_PAGE_PATTERNS = [
        '/',
        'product/*',
        'products',
        'category/*',
        'categories',
        'brands',
        'flash-deals/*',
        'contacts',
        'business-page/*',
        'blog',
        'blog/*',
        'blogs',
        'blogs/*',
        'sitemap.xml',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $mainUrl = rtrim((string) config('app.url'), '/');
        $mainHost = parse_url($mainUrl, PHP_URL_HOST);

        if ($mainHost
            && $request->isMethod('GET')
            && in_array(strtolower($request->getHost()), config('app.legacy_storefront_hosts', []), true)
            && strtolower($request->getHost()) !== strtolower($mainHost)
            && $request->is(...self::PUBLIC_PAGE_PATTERNS)) {
            return redirect()->away($mainUrl.$request->getRequestUri(), Response::HTTP_MOVED_PERMANENTLY);
        }

        return $next($request);
    }
}
