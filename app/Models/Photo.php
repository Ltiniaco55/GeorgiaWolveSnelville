<?php

namespace App\Models;

use App\Enums\PhotoCategory;
use Database\Factories\PhotoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A gallery photo (home "Life in the pack" and /gallery). Only photos with the
 * parents' media release (`has_media_release`) may be shown: use the `published`
 * scope. Texts are stored per locale; `text()` picks the current one.
 *
 * @property PhotoCategory $category
 * @property array<string, string> $title
 * @property array<string, string>|null $subtitle
 * @property array<string, string> $alt
 * @property string $image Public path without extension.
 * @property int $width
 * @property int $height
 * @property bool $has_media_release
 * @property int $sort_order
 */
class Photo extends Model
{
    /** @use HasFactory<PhotoFactory> */
    use HasFactory;

    protected $fillable = [
        'category',
        'title',
        'subtitle',
        'alt',
        'image',
        'width',
        'height',
        'has_media_release',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => PhotoCategory::class,
            'title' => 'array',
            'subtitle' => 'array',
            'alt' => 'array',
            'has_media_release' => 'boolean',
        ];
    }

    /**
     * Photos that can be published, in their display order.
     *
     * @param  Builder<Photo>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('has_media_release', true)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * One of the per-locale texts in the current locale, or in English.
     */
    public function text(string $attribute): string
    {
        $texts = $this->{$attribute} ?? [];

        return $texts[app()->getLocale()] ?? $texts['en'] ?? '';
    }

    /**
     * What the frontend needs to show the photo.
     *
     * @return array{id: int, category: string, title: string, subtitle: string, alt: string, avif: string, webp: string, width: int, height: int}
     */
    public function toCard(): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category->value,
            'title' => $this->text('title'),
            'subtitle' => $this->text('subtitle'),
            'alt' => $this->text('alt'),
            'avif' => asset("{$this->image}.avif"),
            'webp' => asset("{$this->image}.webp"),
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
