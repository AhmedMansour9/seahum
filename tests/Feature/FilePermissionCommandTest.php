<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FilePermissionCommandTest extends TestCase
{
    private string $temporaryBasePath;

    private string $originalBasePath;

    private string $originalPublicPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalBasePath = base_path();
        $this->originalPublicPath = public_path();
        $this->temporaryBasePath = sys_get_temp_dir() . '/file-permission-' . uniqid();

        File::makeDirectory(path: $this->temporaryBasePath . '/public', recursive: true);
        $this->app->setBasePath($this->temporaryBasePath);
        $this->app->usePublicPath($this->temporaryBasePath . '/public');
    }

    protected function tearDown(): void
    {
        $this->app->setBasePath($this->originalBasePath);
        $this->app->usePublicPath($this->originalPublicPath);
        File::deleteDirectory($this->temporaryBasePath);

        parent::tearDown();
    }

    public function test_firebase_service_worker_files_become_writable(): void
    {
        $fileNames = ['sitemap.xml', 'robots.txt', 'firebase-messaging-sw.js'];
        foreach ($fileNames as $fileName) {
            $this->createReadOnlyFile(filePath: base_path($fileName));
            $this->createReadOnlyFile(filePath: public_path($fileName));
        }

        $this->artisan('file:permission')->assertExitCode(0);

        clearstatcache();
        $this->assertSame('0777', $this->getFileMode(filePath: base_path('firebase-messaging-sw.js')));
        $this->assertSame('0777', $this->getFileMode(filePath: public_path('firebase-messaging-sw.js')));
    }

    private function createReadOnlyFile(string $filePath): void
    {
        File::put($filePath, '');
        chmod($filePath, 0444);
    }

    private function getFileMode(string $filePath): string
    {
        return substr(sprintf('%o', fileperms($filePath)), -4);
    }
}
