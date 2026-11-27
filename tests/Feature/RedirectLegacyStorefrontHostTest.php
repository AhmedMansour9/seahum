<?php

namespace Tests\Feature;

use App\Http\Middleware\RedirectLegacyStorefrontHost;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tests\TestCase;

class RedirectLegacyStorefrontHostTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.url' => 'https://shop.sea-hum.store/',
            'app.legacy_storefront_hosts' => ['blue-wiish.com', 'www.blue-wiish.com'],
        ]);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function publicPages(): array
    {
        return [
            'home' => ['https://www.blue-wiish.com/'],
            'product' => ['https://blue-wiish.com/product/smoked-salmon'],
            'category with query' => ['https://www.blue-wiish.com/category/salmon?sort=low'],
            'sitemap' => ['https://blue-wiish.com/sitemap.xml'],
        ];
    }

    #[DataProvider('publicPages')]
    public function test_public_pages_on_the_old_domain_move_permanently_to_the_main_one(string $url): void
    {
        $response = $this->handle(Request::create($url));

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('https://shop.sea-hum.store'.parse_url($url, PHP_URL_PATH).(parse_url($url, PHP_URL_QUERY) ? '?'.parse_url($url, PHP_URL_QUERY) : ''), $response->headers->get('Location'));
    }

    /**
     * Older app versions and in-flight payments still use the old domain, so these must keep working there.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function untouchedRequests(): array
    {
        return [
            'app API' => ['GET', 'https://blue-wiish.com/api/v1/config'],
            'checkout' => ['GET', 'https://blue-wiish.com/checkout-details'],
            'payment return' => ['GET', 'https://blue-wiish.com/payment-success'],
            'admin' => ['GET', 'https://blue-wiish.com/admin/login'],
            'form post on a public page' => ['POST', 'https://blue-wiish.com/products'],
            'main domain' => ['GET', 'https://shop.sea-hum.store/product/smoked-salmon'],
            'unknown domain' => ['GET', 'https://example.test/product/smoked-salmon'],
        ];
    }

    #[DataProvider('untouchedRequests')]
    public function test_other_requests_pass_through(string $method, string $url): void
    {
        $response = $this->handle(Request::create($url, $method));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('passed', $response->getContent());
    }

    private function handle(Request $request): SymfonyResponse
    {
        return (new RedirectLegacyStorefrontHost)->handle($request, fn () => new Response('passed'));
    }
}
