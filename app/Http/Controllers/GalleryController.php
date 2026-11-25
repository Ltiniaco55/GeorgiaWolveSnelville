<?php

namespace App\Http\Controllers;

use App\Enums\PhotoCategory;
use App\Models\Photo;
use Inertia\Inertia;
use Inertia\Response;

class GalleryController extends Controller
{
    /**
     * The /gallery page (docs/design/home-interacciones.md, "Página /gallery"): every
     * photo with the parents' media release, in display order, and the filter tabs
     * that have photos (in the order of PhotoCategory).
     */
    public function __invoke(): Response
    {
        $photos = Photo::query()->published()->get();
        $used = $photos->pluck('category')->unique();

        return Inertia::render('Gallery', [
            'photos' => $photos->map(fn (Photo $photo): array => $photo->toCard())->all(),
            'categories' => collect(PhotoCategory::cases())
                ->filter(fn (PhotoCategory $category): bool => $used->contains($category))
                ->map(fn (PhotoCategory $category): string => $category->value)
                ->values()
                ->all(),
        ]);
    }
}
