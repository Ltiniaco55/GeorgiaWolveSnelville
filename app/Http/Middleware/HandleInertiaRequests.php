<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'locale' => app()->getLocale(),
            'translations' => fn (): array => $this->translations(app()->getLocale()),
        ];
    }

    /**
     * Load every translation group of the given locale (lang/{locale}/*.php),
     * keyed by group name, so the Vue components never hard-code UI copy.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function translations(string $locale): array
    {
        return collect(File::glob(lang_path("{$locale}/*.php")))
            ->mapWithKeys(fn (string $path): array => [
                pathinfo($path, PATHINFO_FILENAME) => trans(pathinfo($path, PATHINFO_FILENAME), [], $locale),
            ])
            ->all();
    }
}
