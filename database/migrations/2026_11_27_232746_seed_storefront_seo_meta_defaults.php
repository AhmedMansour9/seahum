<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The SEO settings table was empty, so search engines got the template's placeholder description
 * ("this is about us page...") and a title without the products people search for. These rows give
 * the home page (also the fallback for every page without its own SEO) and the categories page a
 * real Arabic title and description. They are only added when missing and stay editable from
 * admin > SEO settings, so a later edit there is never overwritten.
 */
return new class extends Migration
{
    /**
     * @var array<int, array{page_name: string, page_title: string, page_url: string, meta_title: string, meta_description: string}>
     */
    private array $pages = [
        [
            'page_name' => 'default',
            'page_title' => 'Home',
            'page_url' => '/',
            'meta_title' => 'Sea Hum | سمك طازج وسالمون وكافيار وسوشي - توصيل لحد البيت',
            'meta_description' => 'اطلب سمك طازج، سالمون مدخن وطازج، كافيار روسي، جمبري ومستلزمات السوشي من Sea Hum. مأكولات بحرية مختارة بعناية وتوصيل سريع لحد باب البيت.',
        ],
        [
            'page_name' => 'categories',
            'page_title' => 'Categories',
            'page_url' => '/categories',
            'meta_title' => 'أقسام السمك والمأكولات البحرية | Sea Hum',
            'meta_description' => 'تصفح أقسام Sea Hum: سالمون، كافيار، جمبري، سوشي، أسماك طازجة ومجمدة وعروض البوكسات. اطلب أونلاين ووصلك لحد البيت.',
        ],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('robots_meta_contents')) {
            return;
        }

        foreach ($this->pages as $page) {
            if (DB::table('robots_meta_contents')->where('page_name', $page['page_name'])->exists()) {
                continue;
            }

            DB::table('robots_meta_contents')->insert($page + ['created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('robots_meta_contents')) {
            return;
        }

        foreach ($this->pages as $page) {
            DB::table('robots_meta_contents')
                ->where('page_name', $page['page_name'])
                ->where('meta_title', $page['meta_title'])
                ->delete();
        }
    }
};
