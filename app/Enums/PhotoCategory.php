<?php

namespace App\Enums;

/**
 * Gallery filter tabs (docs/design/home-interacciones.md, "Página /gallery").
 * Labels live in lang/{locale}/gallery.php under "categories".
 */
enum PhotoCategory: string
{
    case Training = 'training';
    case Matchday = 'matchday';
    case Champions = 'champions';
    case Events = 'events';
    case Team = 'team';
}
