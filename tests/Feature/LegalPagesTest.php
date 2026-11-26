<?php

use Inertia\Testing\AssertableInertia as Assert;

/**
 * The six legal pages share one template; their texts are drafts (lang/{locale}/legal.php).
 */
test('every legal page renders in both languages', function (string $document, string $english, string $spanish) {
    foreach ([$english, $spanish] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('LegalPage')->where('document', $document)->where('currentPage', $document));
    }
})->with([
    ['privacy', '/privacy', '/es/privacidad'],
    ['terms', '/terms', '/es/terminos'],
    ['refund_policy', '/refund-policy', '/es/politica-de-reembolso'],
    ['liability_waiver', '/liability-waiver', '/es/exencion-de-responsabilidad'],
    ['media_release', '/media-release', '/es/autorizacion-de-imagen'],
    ['accessibility', '/accessibility', '/es/accesibilidad'],
]);

test('every legal document has a title, an intro and sections with unique anchors', function (string $locale) {
    foreach (__('legal.documents', locale: $locale) as $document) {
        $ids = array_column($document['sections'], 'id');

        expect($document['title'])->not->toBeEmpty()
            ->and($document['intro'])->not->toBeEmpty()
            ->and($ids)->not->toBeEmpty()
            ->and(array_unique($ids))->toBe($ids);
    }
})->with(['en', 'es']);
