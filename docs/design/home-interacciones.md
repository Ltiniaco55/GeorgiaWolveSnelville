# Home — interacción con el puntero por sección (22 nov 2026; APROBADA el 25 nov 2026)

Petición de Luciano: que cada sección tenga algo interactivo con el puntero, para enganchar al scroll y que den ganas de seguir descubriendo.
Referencia aprobada (25 nov 2026): el prototipo docs/design/prototype/home.html, la home completa con la intro. Si algo de aquí no coincide con él, manda el prototipo. Prueba original: artifact "Georgia Wolves Home" (https://claude.ai/artifact/BzESgWw8NL8WXqgCGQZha6).
El estilo visual y los componentes están en direccion-de-diseno.md.

Orden de la home: intro → hero → cinta de logros → categorías (scroll horizontal) → tarifas → entrenador → franja de ligas asociadas → galería → FAQ → CTA final → pie.

Cada sección lleva una etiqueta pequeña con un icono de cursor ("Move over a card", "Open a question"…), que invita a interactuar.

| Sección | Interacción |
|---|---|
| Hero | Linterna sobre la foto (25 nov 2026): al pasar el ratón por el hero, la foto (oscurecida) se ilumina en un círculo de 230 px alrededor del puntero (máscara radial). Solo con puntero fino y sin reduced motion. Ya no hay líneas del campo ni pista "Move your cursor to light up the pitch" |
| Cinta de logros | Frases con el lobo negro entre ellas. Se pausa al pasar el ratón (y con un botón de pausa que aparece al llegar con el teclado) |
| Categorías | Scroll horizontal enganchado. Además, parallax dentro de cada panel: el número grande sigue un poco al ratón y el número fantasma de fondo se mueve en sentido contrario, lo que da profundidad |
| Tarifas | Cards con expansión al pasar el ratón (aprobada) y un calculador "When do you join?". Muestra los días del mes actual; al pasar el ratón, enfocar o tocar un día, explica la regla del 15: si te apuntas del 1 al 15 pagas el mes ahora (100 $); del 16 en adelante, 0 $ hoy y el primer cobro el día 1 del mes siguiente. Los días a partir del 16 van marcados en otro tono |
| Entrenador | Balancín 3D de la card (aprobado) y zoom lento de la foto al pasar el ratón |
| Ligas asociadas | Ya no son una sección propia. Decisión de Luciano: las ligas (Liga Interamericana y GYSL) aparecen como escudos en una franja de "Associated leagues and partners", al estilo de los patrocinadores. Los logos van en píldoras neumórficas, en gris y semitransparentes; al pasar el ratón o enfocarlos recuperan el color, crecen un poco y se hunden. Ya existen los SVG de las ligas (resources/svg/raw/liga-interamericana.svg y gysl.svg). Sirve también para patrocinadores (Joma, CER…) si se añaden |
| Galería "Life in the pack" (home) | Rastro de fotos: al mover el ratón por la sección aparecen tarjetas (fotos de ejemplo de momento) bajo el cursor, giradas al azar. Duran 1,6 s y se desvanecen. Máximo unas 14 a la vez y una cada 110 px de recorrido. En táctil se muestra una rejilla estática. CTA "View full gallery" que lleva a /gallery |
| FAQ | Acordeón neumórfico. Al pasar el ratón por una pregunta, se rellena de amarillo de izquierda a derecha. Al abrirla, el "+" gira y se vuelve negro con amarillo |
| CTA final "Join the pack." | Botón magnético: cuando el ratón se acerca a menos de 220 px, el botón se desplaza hacia él (35 % de la distancia) y vuelve con un rebote al alejarse |

## Página /gallery (propuesta del 22 nov 2026)
Referencia de estilo pedida por Luciano: React Bits Pro "Gallery 8" (masonry de fotos de boda con pestañas de filtro, reordenación animada y pie de foto al pasar el ratón; https://pro.reactbits.dev/docs/blocks/gallery/gallery-8). No se usa ese bloque: es de pago (licencia Pro) y está hecho para React, mientras que la web va en Vue. Se replicó el comportamiento con código propio.
Prueba: gallery.html en el mismo artifact; se llega desde el enlace "Gallery" del menú.
- Cabecera "LIFE IN THE PACK." con "pack." en amarillo y un enlace "← Back to home"
- Pestañas de filtro en una píldora hundida (All, Training, Matchday, Champions, Events, Team), cada una con su contador. Un indicador amarillo se desliza hasta la pestaña activa. Las pestañas se quedan fijas al hacer scroll y se manejan con las flechas del teclado
- Masonry por JS: 4, 3 o 2 columnas según el ancho. Cada foto se coloca en la columna más baja. Al filtrar, las fotos que sobran se encogen y se desvanecen, y las que quedan se recolocan con una animación de transform de 0,7 s (reflow animado). La altura del muro también se anima
- Al pasar el ratón: zoom lento de la foto y el pie de foto sube desde abajo, con la etiqueta amarilla de la categoría, el título y el subtítulo. En táctil el pie de foto está siempre visible
- Al hacer clic se abre un visor a pantalla completa con flechas de anterior y siguiente, contador, cierre y teclado (Esc, ← y →). Recorre solo las fotos del filtro activo
- 18 fotos de ejemplo (placeholders). Las reales necesitan el permiso de imagen de los padres

Implementación en la home (fase B, 25 nov 2026):
- Franja de ligas: los escudos enlazan a /leagues/{slug} (/es/ligas/{slug}). Joma y CER salen de config/club.php ("partners"): mientras no tengan logo muestran sus iniciales en un círculo hundido y, sin URL, no son enlace. El foco con teclado se ve igual que el hover
- Galería: modelo Photo (categoría, título, subtítulo y texto alternativo en inglés y español, imagen en WebP y AVIF, has_media_release y orden). La home solo recibe las que tienen el permiso de imagen (como mucho 9). Las 9 fotos de ejemplo del prototipo están en public/images/gallery y en PhotoSeeder, marcadas con permiso para poder maquetar: hay que confirmar el permiso real antes de publicar
- El rastro es decorativo (aria-hidden); en táctil y con reduced motion, la rejilla estática lleva los textos alternativos. Sin fotos publicadas no hay ni rastro ni rejilla

Notas técnicas:
- Todo lo que depende del ratón se activa solo con `(hover:hover) and (pointer:fine)`. Con prefers-reduced-motion se desactiva lo que tiene movimiento
- Fallo encontrado y corregido en la prueba: la clase `.sec` de las secciones chocaba con la clase de los botones secundarios `.cta.sec`. En el proyecto real conviene usar nombres de clase con prefijo o componentes con estilos aislados (scoped)
- Las fotos de la galería y la del entrenador son de ejemplo, pendientes del cliente

Decisión posterior (22 nov 2026): el escenario 4:5 del hero lleva de momento la camiseta 3D; más adelante se cambiará por una foto de los niños de la academia, para que la web sea más humana y cercana.
Actualización (25 nov 2026): hecho. El hero ya no tiene escenario 4:5: lleva la foto a sangre a la derecha (HeroStage en modo "photo"). La camiseta 3D se guarda para más adelante.

Direcciones descartadas:
- Cursor personalizado con un mini balón que seguía al ratón y giraba
- Sección de ligas con cards grandes y acordeón horizontal (sustituida por la franja de escudos asociados)
- Estilo brutalista (artifact "Georgia Wolves Brutalist", con bordes de 3 px, sombras duras y rejilla a la vista). Luciano prefirió seguir con la línea negra, neumórfica y con amarillo
- Telón de dos mitades en la intro (sustituido por el revelado circular)
