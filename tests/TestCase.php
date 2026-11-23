<?php

namespace Tests;

use FilesystemIterator;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Vite;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

abstract class TestCase extends BaseTestCase
{
    /**
     * Tests must not depend on a built Vite manifest (fresh clones, CI).
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * Gives browser tests real assets: the Vite dev server when it is running,
     * otherwise an up-to-date `public/build`. Skips the test when neither exists.
     */
    protected function useRealViteAssets(): void
    {
        $hotFile = public_path('hot');

        if (is_file($hotFile) && $this->isViteDevServerRunning(trim((string) file_get_contents($hotFile)))) {
            $this->withVite();

            return;
        }

        $manifest = public_path('build/manifest.json');

        if (! is_file($manifest)) {
            $this->markTestSkipped('Browser tests need real assets: run `vendor/bin/sail npm run build` (or start `vendor/bin/sail npm run dev`).');
        }

        if (filemtime($manifest) < $this->newestFrontendSourceTime()) {
            $this->markTestSkipped('public/build is older than resources/: run `vendor/bin/sail npm run build` (or start `vendor/bin/sail npm run dev`).');
        }

        $this->withVite();

        // A stale `public/hot` left by a stopped dev server must not win over the build.
        Vite::useHotFile(storage_path('framework/testing/vite-hot-disabled'));
    }

    /**
     * Whether something is listening at the dev server URL written in `public/hot`.
     */
    private function isViteDevServerRunning(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT) ?? 5173;

        if (! is_string($host)) {
            return false;
        }

        $socket = @fsockopen($host, $port, $errorCode, $errorMessage, 0.5);

        if ($socket === false) {
            return false;
        }

        fclose($socket);

        return true;
    }

    /**
     * Latest modification time among the files Vite builds from.
     */
    private function newestFrontendSourceTime(): int
    {
        $newest = (int) filemtime(base_path('vite.config.js'));

        foreach (['resources/js', 'resources/css'] as $directory) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(base_path($directory), FilesystemIterator::SKIP_DOTS),
            );

            foreach ($files as $file) {
                $newest = max($newest, $file->getMTime());
            }
        }

        return $newest;
    }
}
