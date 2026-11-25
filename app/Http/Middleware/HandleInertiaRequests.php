<?php

namespace App\Http\Middleware;

use App\Support\Localization;
use Illuminate\Http\Request;
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
     * Sitemap pages linked from the header, the mobile menu and the mobile CTA bar.
     *
     * @var array<int, string>
     */
    private const NAVIGATION_PAGES = [
        'home', 'programs', 'pricing', 'about', 'teams', 'leagues',
        'gallery', 'faq', 'contact', 'register', 'free_training',
        'privacy', 'terms', 'refund_policy', 'liability_waiver', 'media_release', 'accessibility',
    ];

    /**
     * League pages linked from the footer (slug => navigation key).
     */
    private const LEAGUE_PAGES = ['liga-interamericana' => 'liga_interamericana', 'gysl' => 'gysl'];

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
            'locale' => fn (): string => app()->getLocale(),
            'localeUrls' => fn (): array => Localization::equivalentUrls($request),
            'translations' => fn (): array => Localization::translations(app()->getLocale()),
            'currentPage' => fn (): ?string => Localization::currentRouteName($request),
            'navigation' => fn (): array => collect(self::NAVIGATION_PAGES)
                ->mapWithKeys(fn (string $page): array => [$page => Localization::pageUrl($page)])
                ->merge(collect(self::LEAGUE_PAGES)->mapWithKeys(fn (string $key, string $slug): array => [
                    $key => Localization::pageUrl('league', parameters: ['league' => $slug]),
                ]))
                ->all(),
            'club' => fn (): array => [
                'name' => config('club.name'),
                'legalName' => config('club.legal_name'),
                'email' => config('club.email'),
                'phone' => config('club.phone'),
                'address' => config('club.address'),
                'mapsUrl' => config('club.maps_url'),
            ],
            'whatsappUrl' => fn (): string => config('club.whatsapp_number')
                ? 'https://wa.me/'.config('club.whatsapp_number')
                : Localization::pageUrl('contact'),
            'socialLinks' => fn (): array => collect(config('club.social'))
                ->filter()
                ->map(fn (string $url, string $network): array => ['network' => $network, 'url' => $url])
                ->values()
                ->all(),
        ];
    }
}
