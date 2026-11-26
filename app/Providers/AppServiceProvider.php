<?php

namespace App\Providers;

use App\Support\AgeGroup;
use App\Support\BillingStartCalculator;
use App\Support\Localization;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Inertia\ExceptionResponse;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * HTTP errors that always render the branded Inertia error page.
     * 500 only does when debug is off, so local development keeps the stack trace.
     *
     * @var array<int, int>
     */
    protected const BRANDED_ERROR_STATUSES = [403, 404, 419, 429, 503];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Built per resolution, so it always reflects config/billing.php.
        $this->app->bind(BillingStartCalculator::class, fn (): BillingStartCalculator => BillingStartCalculator::fromConfig());
        $this->app->bind(AgeGroup::class, fn (): AgeGroup => AgeGroup::fromConfig());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->renderErrorPagesWithInertia();

        // Public forms (registrations, later contact): 5 submissions a minute per IP.
        RateLimiter::for('forms', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));
    }

    /**
     * Render HTTP errors as the Inertia "Error" page, in the language of the URL prefix
     * (unmatched URLs such as /es/no-existe never reach the SetLocale middleware).
     */
    protected function renderErrorPagesWithInertia(): void
    {
        Inertia::handleExceptionsUsing(function (ExceptionResponse $response): ?ExceptionResponse {
            $status = $response->statusCode();
            $isBranded = in_array($status, self::BRANDED_ERROR_STATUSES, true)
                || ($status === 500 && ! config('app.debug'));

            if (! $isBranded || $response->request->expectsJson() || $response->request->is('api/*')) {
                return null;
            }

            app()->setLocale(Localization::localeFromPath($response->request->path()));

            return $response->render('Error', [
                'status' => $status,
                'homeUrl' => Localization::route('home'),
            ])->withSharedData();
        });
    }
}
