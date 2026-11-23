## Proyecto Georgia Wolves

Web de Georgia Wolves Soccer Club Snellville: academia de fútbol juvenil (U6–U14) en Snellville, Georgia, EE. UU. La web también presenta las dos ligas que organiza el club: **Liga Interamericana** y **Gwinnett Youth Soccer League (GYSL)**.

### Stack
- Laravel + Inertia + Vue 3 (Composition API, `<script setup>`) + Tailwind + MySQL, todo en Laravel Sail.
- Pagos: Laravel Cashier (Stripe). Emails: Laravel Mail.
- Tests: Pest.

### Idiomas
- Inglés por defecto y español.
- Ningún texto de interfaz va escrito a mano en los componentes: todo va en `lang/en` y `lang/es`.

### Fuente de verdad del diseño
`docs/design/direccion-de-diseno.md` y `docs/design/home-interacciones.md`. Si el código contradice estos documentos, mandan los documentos. Muchas piezas están marcadas en ellos como "pendiente de aprobación": se implementan así, pero se avisa antes de darlas por cerradas.

`docs/design/plan-claude-code-prompts.md` es el plan de fases y prompts del proyecto (referencia de orden y alcance; para diseño mandan los otros dos).

Resumen de las reglas clave:

**Home: orden de las secciones**
intro → hero → cinta de logros → categorías (scroll horizontal) → tarifas → entrenador → franja "Associated leagues and partners" → galería → FAQ → CTA final "Join the pack." → pie.
Cada sección lleva una etiqueta pequeña con un icono de cursor que invita a interactuar ("Move over a card", "Open a question"…).

**Intro de marca (2,6 s)**
- Pantalla negra. El emblema amarillo entra con rebote y luego "rueda" en arco hasta el escudo del menú: unas 2,5 vueltas en sentido antihorario, encogiéndose.
- La página se revela con un círculo que crece desde el centro (`clip-path: circle`) y un anillo amarillo de 3 px que se expande y se desvanece.
- Al final, el escudo aparece con un "pop" (escala 0,7 → 1,14 → 1).
- El contenido del hero entra escalonado a partir de 1,25 s.
- Botón "Skip intro" (también con Esc, Espacio o Enter). El scroll está bloqueado durante la intro.
- Solo se muestra en la primera visita de la sesión (`sessionStorage`). Con reduced motion no hay intro.
- Punto de encaje sobre el escudo: x 50 %, y 47,4 % de su caja; diámetro del círculo = 62,4 % de su ancho.

**Hero**
- Menú fijo: transparente sobre el negro y sólido al hacer scroll. Lleva el escudo (72 px), el wordmark, los enlaces y el CTA "Register".
- Izquierda: chip, titular "TRAIN LIKE A WOLF." (con "wolf." en amarillo), subtítulo, dos CTA y chips de datos.
- Derecha: escenario hundido 4:5 (neumórfico oscuro) con dos badges. De momento lleva la camiseta 3D; más adelante, una foto de los niños.

**Botón CTA (aprobado)**
- Píldora con una flecha dentro de un círculo. El alto del botón es igual al diámetro del círculo.
- Al entrar en pantalla pasa de círculo a píldora en unos 1,4 s; el texto aparece con un fundido desde 0,75 s. Al salir vuelve a ser círculo. Se controla con un IntersectionObserver (`rootMargin` -10 %) que pone o quita la clase de estado.
- Estilo hundido: sombra interior oscura arriba, brillo abajo, línea de luz de 1 px y texto con relieve grabado. Al hacer clic se oscurece un poco.
- Hover: `scale(1.09)` con `cubic-bezier(.34,1.56,.64,1)` en 0,35 s. Al hacer clic, `scale(1.03)`.
- Principal: fondo amarillo, texto negro. En hover pasa a carbón #262626 con texto blanco sobre fondo blanco, o con texto y borde amarillos sobre fondo negro.
- Secundario: transparente con borde negro sobre blanco, o borde blanco sobre negro. En hover, carbón con texto blanco sobre blanco, o fondo blanco con texto negro sobre negro.
- Inter 600, 16 px (15 px en móvil).
- Descartados: 3D hacia fuera, inclinación hacia el cursor, halo y glassmorphism.

**Cards neumórficas**
- La card es del mismo color que el fondo de su sección y se distingue solo por dos sombras exteriores: clara arriba a la izquierda y oscura abajo a la derecha. No se usa neumorfismo sobre blanco ni negro puros.
- Tarifas (sección #ECECEC):
  - Tres cards: Free trial, Monthly y Annual. Annual es la destacada: fondo #0B0B0B, precio y checks en amarillo, etiqueta "Best value".
  - Esquinas de 28 px y separadores "grabados".
  - Hover: `scale(1.035)` con rebote en 0,4 s (el balancín se descartó para estas cards).
  - Debajo, una nota con el uniforme (pago único).
  - En móvil (< 640 px), efecto "baraja" enganchado al scroll, con Annual arriba (PricingDeck + useDeckScroll; pendiente de aprobación). Cuenta como uno de los scroll enganchados.
- Entrenador (sección #161616):
  - Card horizontal (foto 5/12 y contenido 7/12) con esquinas de 32 px. La foto va en un hueco hundido.
  - Balancín 3D con muelle: perspectiva 1000 px, inclinación máxima 4°, rigidez 0,055, amortiguación 0,86.
  - Zoom lento de la foto al pasar el ratón.

**Scroll horizontal enganchado (máximo 2 o 3 en toda la web)**
- La sección se queda fija (`position: sticky`, 100vh) y el scroll vertical mueve los paneles con `translate3d` dentro de `requestAnimationFrame`. La altura se calcula con JS.
- Barra de progreso amarilla hundida y la pista "Keep scrolling →".
- Con reduced motion pasa a ser un carrusel con `overflow-x` y `scroll-snap`.

**Interacción con el puntero por sección de la home**
- Hero: linterna. Un círculo de luz sigue al ratón y deja ver las líneas de un campo en amarillo (SVG con máscara radial).
- Cinta de logros: se pausa al pasar el ratón.
- Categorías: parallax. El número grande sigue al ratón y el número fantasma del fondo se mueve en sentido contrario.
- Tarifas: calculador "When do you join?" que explica la regla del día 15. La regla se lee de `config/billing.php` y no se repite en el frontend.
- Entrenador: balancín y zoom de la foto.
- Ligas: los escudos van en píldoras neumórficas, en gris y semitransparentes. Al pasar el ratón o enfocarlos recuperan el color, crecen y se hunden. Las ligas **no tienen sección propia en la home**: sus escudos enlazan a sus páginas.
- Galería: rastro de fotos giradas bajo el cursor. Duran 1,6 s, hay como máximo 14 a la vez y sale una cada 110 px de recorrido. En táctil, rejilla estática.
- FAQ: acordeón neumórfico. La pregunta se rellena de amarillo de izquierda a derecha y el "+" gira.
- CTA final: botón magnético. Se activa a menos de 220 px, se desplaza el 35 % de la distancia y vuelve con rebote.

**Página /gallery**
- Masonry por JS con 4, 3 o 2 columnas según el ancho.
- Pestañas de filtro en una píldora hundida, con contador e indicador amarillo deslizante. Se quedan fijas al hacer scroll y se manejan con las flechas del teclado.
- Al filtrar, las fotos se recolocan con una animación de 0,7 s.
- Pie de foto al pasar el ratón (siempre visible en táctil).
- Visor a pantalla completa con teclado (Esc, ← y →) que recorre solo el filtro activo.
- Código propio: no se usa React Bits.

**Assets**
- Los SVG vectorizados están en `resources/svg/raw/`: emblema en 4 variantes, wordmark, escudo, logos de las ligas y favicon. Los monocromos usan `currentColor`.
- Los PNG incrustados originales ya no se usan.

**Descartado (no volver a proponerlo)**
- Telón de dos mitades en la intro.
- Cursor con un mini balón.
- Sección de ligas con cards grandes.
- Estilo brutalista.
- Glassmorphism.
- 3D hacia fuera en los botones.

### Design tokens
| Token | Valor | Uso |
|---|---|---|
| Blanco | `#FFFFFF` | Color principal |
| Negro | `#0A0A0A` | Color principal |
| Carbón | `#262626` | Botones en hover |
| Amarillo | `#FFD400` | Solo acentos y CTA |
| Neumórfico claro | superficie `#ECECEC`, sombras `#C8C8C8` / `#FFFFFF`, desenfoque 14 px y 30 px | Cards en secciones blancas |
| Neumórfico oscuro | superficie `#161616`, sombras `#090909` / `#232323`, desenfoque 18 px y 36 px | Cards en secciones negras |

- Titulares, nombres y precios: Barlow Condensed 700/800. Es provisional hasta tener la fuente del wordmark.
- Texto, botones y etiquetas: Inter 400/500/600.

### Estilo
- La home empieza en negro con una intro de marca y luego pasa a secciones blancas.
- Cards neumórficas y paneles grandes con esquinas redondeadas.
- Scroll horizontal enganchado (snap) en 2 o 3 sitios como máximo en toda la web.
- Cada sección de la home tiene una interacción con el puntero.

### Interacción y movimiento
- Todo lo que depende del ratón se activa solo con `@media (hover: hover) and (pointer: fine)`. En táctil siempre hay una alternativa estática.
- Con `prefers-reduced-motion: reduce` se desactiva todo el movimiento: no hay intro, los CTA aparecen ya abiertos y no hay parallax ni balancín.

### CSS
- Estilos `scoped` en los componentes Vue, o clases globales con prefijo `gw-`.
- Nada de clases genéricas globales. En el prototipo, `.sec` (sección) chocaba con `.cta.sec` (botón secundario).

### Accesibilidad
- WCAG 2.1 AA obligatoria: contraste, foco visible, navegación por teclado, HTML semántico y textos alternativos.

### Seguridad
- Nunca guardar datos de tarjeta: el pago va por Stripe con Cashier.
- Validar siempre en el servidor (Form Requests).
- Rate limiting en todos los formularios.
- Los secretos solo van en `.env`, nunca en el código ni en el repo.

### Tests
- Suite completa: `vendor/bin/sail artisan test --compact`. Solo los de navegador: `vendor/bin/sail artisan test --compact --testsuite=Browser`.
- Los tests de navegador (`tests/Browser/`, Pest browser + Playwright) llaman a `$this->useRealViteAssets()` en su `beforeEach` (`tests/TestCase.php`):
  - Si `public/hot` existe y el servidor de Vite responde (`vendor/bin/sail npm run dev`), usan el servidor de desarrollo.
  - Si no, usan `public/build`, aunque haya un `public/hot` viejo de un `npm run dev` ya parado.
  - Si no hay build, o es más antiguo que `resources/js`, `resources/css` o `vite.config.js`, se saltan con un aviso que pide `vendor/bin/sail npm run build`. Después de un `git checkout` puede hacer falta volver a compilar.
- El resto de tests no carga assets (`withoutVite()` en `TestCase`).
- Las capturas de los fallos van a `tests/Browser/Screenshots` (ignorado en git).

### Convenciones de carpetas
- Componentes: `resources/js/Components`
- Secciones: `resources/js/Sections`
- Páginas Inertia: `resources/js/Pages`

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `vendor/bin/sail composer show --direct` to list direct dependencies with versions, or `vendor/bin/sail composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `vendor/bin/sail npm run build` or ask the user to run `vendor/bin/sail npm run dev` or `vendor/bin/sail composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `vendor/bin/sail artisan route:list`). Use `vendor/bin/sail artisan list` to discover available commands and `vendor/bin/sail artisan [command] --help` to check parameters.
- Inspect routes with `vendor/bin/sail artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `vendor/bin/sail artisan config:show app.name`, `vendor/bin/sail artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `vendor/bin/sail artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `vendor/bin/sail artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== sail rules ===

# Laravel Sail

- This project runs inside Laravel Sail's Docker containers. You MUST execute all commands through Sail.
- Start services using `vendor/bin/sail up -d` and stop them with `vendor/bin/sail stop`.
- Open the application in the browser by running `vendor/bin/sail open`.
- Always prefix PHP, Artisan, Composer, and Node commands with `vendor/bin/sail`. Examples:
    - Run Artisan Commands: `vendor/bin/sail artisan migrate`
    - Install Composer packages: `vendor/bin/sail composer install`
    - Execute Node commands: `vendor/bin/sail npm run dev`
    - Execute PHP scripts: `vendor/bin/sail php [script]`
- View all available Sail commands by running `vendor/bin/sail` without arguments.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `vendor/bin/sail artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `vendor/bin/sail artisan list` and check their parameters with `vendor/bin/sail artisan [command] --help`.
- If you're creating a generic PHP class, use `vendor/bin/sail artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `vendor/bin/sail artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `vendor/bin/sail artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/sail bin pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/sail bin pint --test --format agent`, simply run `vendor/bin/sail bin pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `vendor/bin/sail artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `vendor/bin/sail artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/sail bin pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
