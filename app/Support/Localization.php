<?php

namespace App\Support;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Locale-aware routing: English without prefix, other locales under /{locale}
 * with translated slugs from lang/{locale}/routes.php.
 *
 * Localized route names are "{locale}.{name}", e.g. "en.about" and "es.about".
 */
class Localization
{
    /**
     * @return array<int, string>
     */
    public static function locales(): array
    {
        return config('localization.locales');
    }

    /**
     * The unprefixed locale. Not app.locale, which setLocale() overwrites per request.
     */
    public static function defaultLocale(): string
    {
        return config('localization.default');
    }

    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::locales(), true);
    }

    /**
     * URL prefix of a locale: none for the default locale, "/{locale}" otherwise.
     */
    public static function prefix(string $locale): string
    {
        return $locale === self::defaultLocale() ? '' : $locale;
    }

    /**
     * Locale of a request path, taken from its first segment ("es/nosotros" is Spanish).
     */
    public static function localeFromPath(string $path): string
    {
        $firstSegment = Str::before(trim($path, '/'), '/');

        return self::isSupported($firstSegment) && $firstSegment !== self::defaultLocale()
            ? $firstSegment
            : self::defaultLocale();
    }

    /**
     * Translated path of a route, without the locale prefix (lang/{locale}/routes.php).
     */
    public static function slug(string $name, string $locale): string
    {
        if (! Lang::has("routes.{$name}", $locale, false)) {
            throw new InvalidArgumentException("Missing slug [routes.{$name}] for locale [{$locale}].");
        }

        return '/'.ltrim(trans("routes.{$name}", [], $locale), '/');
    }

    /**
     * Register the routes defined in the callback once per locale, with the locale's
     * URL prefix and a "{locale}." name prefix. The callback receives the locale.
     *
     * @param  Closure(string): void  $routes
     */
    public static function routes(Closure $routes): void
    {
        foreach (self::locales() as $locale) {
            Route::prefix(self::prefix($locale))
                ->name("{$locale}.")
                ->group(fn () => $routes($locale));
        }
    }

    /**
     * URL of a localized route. Defaults to the current locale.
     *
     * @param  array<string, mixed>  $parameters
     */
    public static function route(string $name, array $parameters = [], ?string $locale = null): string
    {
        return route(($locale ?? app()->getLocale()).".{$name}", $parameters);
    }

    /**
     * Name of the current localized route without its locale, e.g. "about" for "es.about".
     */
    public static function currentRouteName(?Request $request = null): ?string
    {
        $routeName = ($request ?? request())->route()?->getName();

        if ($routeName === null || ! Str::contains($routeName, '.')) {
            return null;
        }

        [$locale, $name] = explode('.', $routeName, 2);

        return self::isSupported($locale) ? $name : null;
    }

    /**
     * The current page's URL in every locale, for the EN/ES switcher.
     * Pages that are not localized routes (errors) fall back to each locale's home.
     *
     * @return array<string, string>
     */
    public static function equivalentUrls(?Request $request = null): array
    {
        $request ??= request();
        $name = self::currentRouteName($request);
        $parameters = $name === null ? [] : $request->route()->parameters();

        return collect(self::locales())
            ->mapWithKeys(fn (string $locale): array => [
                $locale => self::route($name ?? 'home', $parameters, $locale),
            ])
            ->all();
    }

    /**
     * hreflang alternates (one per locale plus x-default) for the current page.
     * Empty when the page is not a localized route.
     *
     * @return array<string, string>
     */
    public static function alternateUrls(?Request $request = null): array
    {
        if (self::currentRouteName($request) === null) {
            return [];
        }

        $urls = self::equivalentUrls($request);

        return [...$urls, 'x-default' => $urls[self::defaultLocale()]];
    }

    /**
     * Canonical URL of the current page (its own locale, without query string).
     */
    public static function canonicalUrl(?Request $request = null): ?string
    {
        if (self::currentRouteName($request) === null) {
            return null;
        }

        return self::equivalentUrls($request)[app()->getLocale()];
    }

    /**
     * UI translation groups of a locale, keyed by group. The "routes" slugs are
     * left out because they are only used on the server.
     *
     * @return array<string, mixed>
     */
    public static function translations(string $locale): array
    {
        return collect(File::glob(lang_path("{$locale}/*.php")))
            ->map(fn (string $path): string => pathinfo($path, PATHINFO_FILENAME))
            ->reject(fn (string $group): bool => $group === 'routes')
            ->mapWithKeys(fn (string $group): array => [$group => trans($group, [], $locale)])
            ->all();
    }
}
