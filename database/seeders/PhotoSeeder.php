<?php

namespace Database\Seeders;

use App\Enums\PhotoCategory;
use App\Models\Photo;
use Illuminate\Database\Seeder;

/**
 * The sample gallery photos of the approved prototype (docs/design/prototype/home.html),
 * in public/images/gallery. Marked as having the media release so they show
 * while the site is built: confirm the parents' release before going live.
 * Safe to run again (matched by image).
 */
class PhotoSeeder extends Seeder
{
    public function run(): void
    {
        $photos = [
            ['team-talk', PhotoCategory::Training, 382, 420, ['en' => 'Team talk', 'es' => 'Charla de equipo'],
                ['en' => 'Players in yellow and black kits listen to the coach under the tent; one of them smiles.', 'es' => 'Jugadores con la equipación amarilla y negra escuchan al entrenador bajo la carpa; uno de ellos sonríe.']],
            ['big-smiles', PhotoCategory::Training, 416, 400, ['en' => 'Big smiles', 'es' => 'Grandes sonrisas'],
                ['en' => 'A smiling player listens to the coach during a team talk.', 'es' => 'Un jugador sonríe mientras escucha al entrenador en una charla de equipo.']],
            ['coaches-corner', PhotoCategory::Training, 316, 350, ['en' => 'Coach’s corner', 'es' => 'El rincón del entrenador'],
                ['en' => 'The coach, in a yellow shirt that reads Coach, talks to two smiling players.', 'es' => 'El entrenador, con una camiseta amarilla que dice Coach, habla con dos jugadores sonrientes.']],
            ['half-time', PhotoCategory::Matchday, 420, 232, ['en' => 'Half-time', 'es' => 'Descanso'],
                ['en' => 'Cleats, socks and water bottles on the grass during a break.', 'es' => 'Botas, medias y botellas de agua sobre el césped durante un descanso.']],
            ['gysl-champions', PhotoCategory::Champions, 420, 385, ['en' => 'GYSL champions', 'es' => 'Campeones de la GYSL'],
                ['en' => 'Young players in grey kits with champions’ medals in front of the Gwinnett Youth Soccer League banner.', 'es' => 'Jugadores pequeños con equipación gris y medallas de campeones delante de la pancarta de la Gwinnett Youth Soccer League.']],
            ['with-the-cup', PhotoCategory::Champions, 420, 385, ['en' => 'With the cup', 'es' => 'Con la copa'],
                ['en' => 'A girl holds the champions’ trophy, sitting between two teammates with medals.', 'es' => 'Una niña sostiene el trofeo de campeones, sentada entre dos compañeros con medalla.']],
            ['medal-day', PhotoCategory::Champions, 420, 376, ['en' => 'Medal day', 'es' => 'Día de medallas'],
                ['en' => 'Three young players kneel with their champions’ medals in front of the league banner.', 'es' => 'Tres jugadores pequeños, de rodillas, con sus medallas de campeones delante de la pancarta de la liga.']],
            ['team-photo', PhotoCategory::Team, 420, 328, ['en' => 'Team photo', 'es' => 'Foto de equipo'],
                ['en' => 'The team on the grass with their medals and the trophy, next to the Gwinnett Youth Soccer League logo.', 'es' => 'El equipo sobre el césped con sus medallas y el trofeo, junto al logo de la Gwinnett Youth Soccer League.']],
            ['proud-pack', PhotoCategory::Champions, 420, 280, ['en' => 'Proud pack', 'es' => 'Manada orgullosa'],
                ['en' => 'Four players with medals and the trophy in front of the league banner.', 'es' => 'Cuatro jugadores con medallas y el trofeo delante de la pancarta de la liga.']],
        ];

        foreach ($photos as $order => [$name, $category, $width, $height, $title, $alt]) {
            Photo::updateOrCreate(['image' => "images/gallery/{$name}"], [
                'category' => $category,
                'title' => $title,
                'alt' => $alt,
                'width' => $width,
                'height' => $height,
                'has_media_release' => true,
                'sort_order' => $order,
            ]);
        }
    }
}
