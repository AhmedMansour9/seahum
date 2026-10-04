<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * mPDF defaults its temporary directory to vendor/mpdf/mpdf/tmp, which the web
 * server user cannot write to on production, so every instance has to be
 * pointed at the storage directory instead.
 */
class MpdfTemporaryDirectoryTest extends TestCase
{
    private const SCANNED_DIRECTORIES = ['app', 'Modules'];

    public function test_every_mpdf_instance_writes_temporary_files_inside_storage(): void
    {
        $offendingLines = [];

        foreach (self::SCANNED_DIRECTORIES as $directory) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(base_path($directory), \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                foreach (file($file->getPathname()) as $number => $line) {
                    if (str_contains($line, 'new \Mpdf\Mpdf(') && ! str_contains($line, "'tempDir' => storage_path(")) {
                        $offendingLines[] = $file->getPathname().':'.($number + 1);
                    }
                }
            }
        }

        $this->assertSame(
            [],
            $offendingLines,
            'mPDF must be given a tempDir inside storage: '.implode(', ', $offendingLines)
        );
    }
}
