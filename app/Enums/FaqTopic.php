<?php

namespace App\Enums;

/**
 * FAQ tabs of the /faq page (docs/design/sitemap.md, "FAQ").
 */
enum FaqTopic: string
{
    case Programs = 'programs';
    case Registration = 'registration';
    case Leagues = 'leagues';
    case Safety = 'safety';
    case General = 'general';
}
