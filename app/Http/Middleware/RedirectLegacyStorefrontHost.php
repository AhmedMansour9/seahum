<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The old domain (blue-wiish.com) is no longer a website: every page opened on it moves
 * permanently to the same page on the main domain (APP_URL).
 *
 * This middleware sits in the web group only, so the app API under /api, which older installed
 * versions of the customer and delivery apps still call, keeps answering on the old domain.
 * Form submissions are not redirected, since a redirect would drop the posted data.
 */
class RedirectLegacyStorefrontHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $mainUrl = rtrim((string) config('app.url'), '/');
        $mainHost = strtolower((string) parse_url($mainUrl, PHP_URL_HOST));
        $requestHost = strtolower($request->getHost());

        if ($mainHost !== ''
            && $requestHost !== $mainHost
            && in_array($requestHost, config('app.legacy_storefront_hosts', []), true)
            && ($request->isMethod('GET') || $request->isMethod('HEAD'))) {
            return redirect()->away($mainUrl.$request->getRequestUri(), Response::HTTP_MOVED_PERMANENTLY);
        }

        return $next($request);
    }
}
