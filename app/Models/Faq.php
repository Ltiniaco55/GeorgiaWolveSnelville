<?php

namespace App\Models;

use App\Enums\FaqTopic;
use App\Support\HomePricing;
use Database\Factories\FaqFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A frequently asked question, shared by the home FAQ, the /faq page and the
 * WhatsApp bot. Texts are stored per locale; billing figures are placeholders
 * (:cutoff_day, :monthly_price) filled from config/billing.php, so the rule is
 * never copied into the content.
 *
 * @property FaqTopic $topic
 * @property array<string, string> $question
 * @property array<string, string> $answer
 * @property bool $is_published
 * @property bool $show_on_home
 * @property int $sort_order
 */
class Faq extends Model
{
    /** @use HasFactory<FaqFactory> */
    use HasFactory;

    protected $fillable = [
        'topic',
        'question',
        'answer',
        'is_published',
        'show_on_home',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'topic' => FaqTopic::class,
            'question' => 'array',
            'answer' => 'array',
            'is_published' => 'boolean',
            'show_on_home' => 'boolean',
        ];
    }

    /**
     * Published questions in their display order.
     *
     * @param  Builder<Faq>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * The question in the current locale (or English), with the billing figures filled in.
     */
    public function questionText(): string
    {
        return self::fillBillingFigures($this->question[app()->getLocale()] ?? $this->question['en'] ?? '');
    }

    /**
     * The answer in the current locale (or English), with the billing figures filled in.
     */
    public function answerText(): string
    {
        return self::fillBillingFigures($this->answer[app()->getLocale()] ?? $this->answer['en'] ?? '');
    }

    /**
     * Replaces :cutoff_day and :monthly_price with the values of config/billing.php.
     */
    private static function fillBillingFigures(string $text): string
    {
        return strtr($text, [
            ':cutoff_day' => HomePricing::cutoffLabel((int) config('billing.cutoff_day')),
            ':monthly_price' => HomePricing::money((int) config('billing.prices.monthly')),
        ]);
    }

    /**
     * @return array{id: int, question: string, answer: string}
     */
    public function toItem(): array
    {
        return [
            'id' => $this->id,
            'question' => $this->questionText(),
            'answer' => $this->answerText(),
        ];
    }
}
