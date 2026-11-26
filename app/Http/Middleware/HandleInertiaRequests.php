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
     * Pages with their own URL linked from the header, the mobile menu, the mobile
     * CTA bar and the footer.
     *
     * @var array<int, string>
     */
    private const NAVIGATION_PAGES = [
        'home', 'programs', 'gallery', 'register', 'free_training',
        'privacy', 'terms', 'refund_policy', 'liability_waiver', 'media_release', 'accessibility',
    ];

    /**
     * Menu entries that are sections of the home page (navigation key => anchor):
     * pricing, the coach (About), the leagues strip and the FAQ.
     *
     * @var array<string, string>
     */
    private const HOME_SECTIONS = ['pricing' => 'pricing', 'about' => 'coach', 'leagues' => 'leagues', 'faq' => 'faq'];

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
                ->merge(collect(self::HOME_SECTIONS)->map(fn (string $anchor): string => Localization::pageUrl('home').'#'.$anchor))
                ->all(),
            'leagues' => fn (): array => config('club.leagues'),
            'club' => fn (): array => [
                'name' => config('club.name'),
                'legalName' => config('club.legal_name'),
                'email' => config('club.email'),
                'phone' => config('club.phone'),
                'address' => config('club.address'),
                'mapsUrl' => config('club.maps_url'),
            ],
            'whatsappUrl' => fn (): ?string => config('club.whatsapp_number')
                ? 'https://wa.me/'.config('club.whatsapp_number')
                : null,
            'socialLinks' => fn (): array => collect(config('club.social'))
                ->filter()
                ->map(fn (string $url, string $network): array => ['network' => $network, 'url' => $url])
                ->values()
                ->all(),
        ];
    }
}
