<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BusinessPage;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Modules\Blog\app\Models\Blog;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * Builds the sitemap from the database on request, so new products and categories appear
 * without anyone regenerating a file, and nothing crawls the site over HTTP to build it.
 */
class SitemapController extends Controller
{
    private const CACHE_KEY = 'storefront_sitemap_xml';

    private const CACHE_SECONDS = 6 * 60 * 60;

    public function index(): Response
    {
        $xml = Cache::remember(self::CACHE_KEY, self::CACHE_SECONDS, fn (): string => $this->buildSitemap()->render());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function buildSitemap(): Sitemap
    {
        $sitemap = Sitemap::create()
            ->add(Url::create($this->canonicalUrl('home'))->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)->setPriority(1.0))
            ->add(Url::create($this->canonicalUrl('categories'))->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)->setPriority(0.8))
            ->add(Url::create($this->canonicalUrl('products'))->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)->setPriority(0.8))
            ->add(Url::create($this->canonicalUrl('contacts'))->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)->setPriority(0.4));

        Category::query()->select(['slug', 'updated_at'])->whereNotNull('slug')->get()
            ->each(fn (Category $category) => $sitemap->add(
                Url::create($this->canonicalUrl('category-products', ['slug' => $category->slug]))
                    ->setLastModificationDate($category->updated_at ?? now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.9)
            ));

        Product::active()->select(['slug', 'updated_at'])->whereNotNull('slug')->get()
            ->each(fn (Product $product) => $sitemap->add(
                Url::create($this->canonicalUrl('product', ['slug' => $product->slug]))
                    ->setLastModificationDate($product->updated_at ?? now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8)
            ));

        BusinessPage::query()->where('status', 1)->whereNotNull('slug')->get(['slug', 'updated_at'])
            ->each(fn (BusinessPage $page) => $sitemap->add(
                Url::create($this->canonicalUrl('business-page.view', ['slug' => $page->slug]))
                    ->setLastModificationDate($page->updated_at ?? now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.3)
            ));

        if (class_exists(Blog::class) && Route::has('frontend.blog.details')) {
            Blog::active()->whereNotNull('slug')->get(['slug', 'updated_at'])
                ->each(fn (Blog $blog) => $sitemap->add(
                    Url::create($this->canonicalUrl('frontend.blog.details', ['slug' => $blog->slug]))
                        ->setLastModificationDate($blog->updated_at ?? now())
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                        ->setPriority(0.6)
                ));
        }

        return $sitemap;
    }

    /**
     * Always on the main domain (APP_URL), even when the sitemap is first requested through an old one,
     * because the result is cached for everyone.
     *
     * @param  array<string, string>  $parameters
     */
    private function canonicalUrl(string $routeName, array $parameters = []): string
    {
        return rtrim(config('app.url'), '/').route($routeName, $parameters, false);
    }
}
