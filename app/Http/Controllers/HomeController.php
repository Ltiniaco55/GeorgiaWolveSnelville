<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Support\HomePricing;
use App\Support\Localization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Home page. In the local environment, ?intro=1 plays the brand intro even
     * when it was already seen in this session, to review it. `pricing` carries
     * the plan prices and the "When do you join?" calendar (config/billing.php);
     * `galleryPhotos` up to 9 photos with the parents' media release;
     * `leagues` and `partners` for the "Associated leagues and partners" strip.
     */
    public function __invoke(Request $request, HomePricing $pricing): Response
    {
        return Inertia::render('Home', [
            'forceIntro' => app()->isLocal() && $request->boolean('intro'),
            'pricing' => $pricing->toArray(),
            'galleryPhotos' => Photo::query()->published()->limit(9)->get()
                ->map(fn (Photo $photo): array => $photo->toCard())
                ->all(),
            'leagues' => collect(['liga-interamericana', 'gysl'])
                ->map(fn (string $slug): array => [
                    'slug' => $slug,
                    'url' => Localization::pageUrl('league', parameters: ['league' => $slug]),
                ])
                ->all(),
            'partners' => config('club.partners'),
        ]);
    }
}
