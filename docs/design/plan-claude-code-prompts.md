# Georgia Wolves Snellville — Plan de construcción con Claude Code (actualizado 22 nov 2026)

Documentos de referencia del proyecto (cópialos al repo en `docs/design/` antes del prompt 1, para que Claude Code los lea):
- `direccion-de-diseno.md`: colores, tipografía, botón CTA, cards neumórficas, intro y hero, scroll horizontal
- `home-interacciones.md`: orden de la home, la interacción con el puntero de cada sección y la página /gallery
Si puedes, descarga también el HTML del artifact "Georgia Wolves Home" (home y gallery.html) y guárdalo en `docs/design/prototype/` como referencia visual. Es un prototipo, no código para copiar tal cual.

Stack: Laravel 11/12 + Inertia + Vue 3 + Tailwind + MySQL, en Docker (Laravel Sail). Stripe con Laravel Cashier. Web bilingüe EN (principal) / ES.

---

## FASE 0 — Herramientas que hay que instalar en Claude Code (una sola vez)

Ejecuta esto en la terminal del proyecto:

```bash
# Stripe: crear productos y precios, revisar suscripciones y leer la doc
claude mcp add --transport http stripe https://mcp.stripe.com

# Context7: documentación actual de Laravel, Cashier, Three.js, etc.
claude mcp add --transport http context7 https://mcp.context7.com/mcp

# Playwright: Claude abre la web, hace clic y prueba los flujos
claude mcp add playwright -- npx @playwright/mcp@latest

# Laravel Boost (MCP oficial de Laravel; se instala después de crear el proyecto Laravel)
composer require laravel/boost --dev && php artisan boost:install
```

Dentro de Claude Code, escribe `/plugin`, busca estos plugins en el marketplace oficial e instálalos:
- **frontend-design**: para que el diseño no parezca una plantilla
- **feature-dev**: flujo de explorar → planificar → implementar para las features grandes (Stripe, WhatsApp)
- **code-review** / **security-guidance**: si aparecen en el marketplace

Comandos que ya vienen de serie y usaremos: `/init`, `/security-review`, `/review`, el modo plan (Shift+Tab dos veces) y `/compact` entre fases.

---

## FASE 1 — CLAUDE.md (memoria del proyecto)

> Prompt 1
```
Vamos a construir desde cero la web de Georgia Wolves Soccer Club Snellville (academia de fútbol juvenil, U6–U14, Snellville, Georgia, EE. UU.). El dueño también organiza dos ligas activas: Liga Interamericana y Gwinnett Youth Soccer League (GYSL), que también tienen que aparecer en la web.

Crea un CLAUDE.md en la raíz que deje fijado lo siguiente:
- Stack: Laravel + Inertia + Vue 3 (Composition API, <script setup>) + Tailwind + MySQL, en Laravel Sail. Pagos con Laravel Cashier (Stripe). Emails con Laravel Mail.
- Idiomas: inglés por defecto y español. Ningún texto va escrito a mano en los componentes; todo va en lang/en y lang/es.
- Fuente de verdad del diseño: docs/design/direccion-de-diseno.md y docs/design/home-interacciones.md. Léelos enteros y resume en el CLAUDE.md las reglas clave; si algo del código contradice esos documentos, mandan los documentos.
- Design tokens: blanco #FFFFFF y negro #0A0A0A como colores principales. Carbón #262626 para los botones en hover. Amarillo #FFD400 solo para acentos y CTA. Superficies neumórficas: claro #ECECEC (sombras #C8C8C8 / #FFFFFF, 14 px y 30 px de desenfoque) y oscuro #161616 (sombras #090909 / #232323, 18 px y 36 px de desenfoque). Tipografía: Barlow Condensed 700/800 en titulares, nombres y precios (provisional, hasta tener la fuente del wordmark) e Inter 400/500/600 en texto, botones y etiquetas.
- Estilo: la home empieza en negro con una intro de marca y pasa a secciones blancas. Cards neumórficas, paneles grandes con esquinas redondeadas, scroll horizontal enganchado en 2 o 3 sitios como máximo, y una interacción con el puntero en cada sección de la home.
- Interacción: todo lo que depende del ratón solo se activa con (hover:hover) and (pointer:fine); en táctil hay una alternativa estática. Con prefers-reduced-motion se desactiva todo el movimiento (sin intro, CTA abiertos, sin parallax ni balancín).
- CSS: estilos scoped en los componentes Vue o clases con prefijo gw-. En el prototipo hubo un choque entre .sec (sección) y .cta.sec (botón secundario), así que nada de clases genéricas globales.
- Accesibilidad WCAG 2.1 AA obligatoria.
- Seguridad: nunca guardar datos de tarjeta, validar siempre en el servidor, rate limiting en todos los formularios y los secretos solo en .env.
- Convenciones: componentes en resources/js/Components, secciones en resources/js/Sections y páginas en resources/js/Pages. Tests con Pest.
No escribas código todavía: enséñame el CLAUDE.md y espera a que te dé el OK.
```

---

## FASE 2 — Arquitectura del sitio

> Prompt 2 (modo plan)
```
Propón el mapa del sitio y la estructura de URLs (EN y ES, con prefijo /es). Como mínimo:
Home, About (historia y el entrenador Christian Tiniaco, licencia UEFA PRO), Programs (categorías U6–U14, horarios y temporadas), Teams & Achievements, Leagues (con subpáginas para Liga Interamericana y GYSL; en la home solo aparecen como escudos en la franja "Associated leagues and partners"), Gallery (/gallery, con filtros), Register/Pricing, Contact, FAQ, y las páginas legales: Privacy, Terms, Refund Policy, Liability Waiver y Accessibility Statement.
Para cada página, dame las secciones en orden y el CTA principal. Las rutas tienen que ser amigables para SEO. No implementes nada todavía.
```

---

## FASE 3 — Assets y design system

Los SVG ya están vectorizados de verdad en `Desktop\GWS\svg-vectorizados\`. Copia esa carpeta entera a `resources/svg/raw/`:
`wolf-yellow.svg`, `wolf-yellow-black.svg`, `wolf-black.svg`, `wolf-white.svg`, `wordmark-white.svg`, `wordmark-yellow.svg`, `wordmark-black.svg`, `crest.svg`, `liga-interamericana.svg`, `gysl.svg` y `favicon.svg`.
Los monocromos ya traen currentColor. Llevan un bloque de metadatos C2PA que SVGO quitará.

> Prompt 3
```
He dejado los SVG del club y de las ligas en resources/svg/raw/. Para cada uno:
- pásalo por SVGO (quita metadatos e ids sobrantes, conserva el viewBox y no toques la geometría)
- usa currentColor solo en las variantes monocromas (wolf-black, wolf-white y los wordmarks), para poder cambiarles el color con CSS
- guarda la versión optimizada en resources/svg/ y dime el peso antes y después
Crea un componente <BrandLogo variant="..."> que los cargue inline con un aria-label.
Añade a tailwind.config los tokens de color (#FFFFFF, #0A0A0A, #262626, #FFD400, #ECECEC y #161616), las sombras neumórficas claras y oscuras (en relieve y hundidas) como utilidades, y las fuentes Barlow Condensed e Inter autoalojadas (woff2, font-display: swap).
Genera el favicon (ico y apple-touch) a partir de favicon.svg, y una imagen OG de 1200x630 a partir de crest.svg.
Si algún SVG trae imágenes PNG incrustadas o texto sin convertir a trazados, avísame en lugar de arreglarlo.
```

> Prompt 4 — botón CTA (el que ya aprobamos)
```
Implementa el componente <CtaButton> con variantes primary/secondary y surface light/dark, siguiendo exactamente esta especificación: [PEGA LA SECCIÓN "Botón CTA" DE direccion-de-diseno.md].
Resumen: es un círculo con flecha que se alarga hasta formar una píldora con el texto cuando entra en pantalla (IntersectionObserver con rootMargin -10%, apertura de 1,4 s y fundido del texto desde 0,75 s), y vuelve a ser círculo al salir. Efecto 3D hacia dentro (hundido). Al pasar el ratón: carbón #262626 y scale(1.09) con cubic-bezier(.34,1.56,.64,1) en 0,35 s.
Con prefers-reduced-motion, el botón sale siempre abierto.
Crea una página /styleguide (solo visible en local) con todas las variantes del botón para revisarlo.
```

> Prompt 4b — cards neumórficas y composables de interacción
```
Lee las secciones "Cards — estilo neumorphism" de docs/design/direccion-de-diseno.md y "Notas técnicas" de docs/design/home-interacciones.md. Crea:
- <NeuCard surface="light|dark" variant="raised|inset|featured" hover="expand|tilt|none">
  - expand: scale(1.035) con cubic-bezier(.34,1.56,.64,1) en 0,4 s, y la sombra crece a la vez
  - tilt (balancín): perspectiva de 1000 px, 4° máximo, la esquina del puntero se hunde, muelle con rigidez 0,055 y amortiguación 0,86 en requestAnimationFrame
- composables en resources/js/Composables:
  - useFinePointer(): true solo con (hover:hover) and (pointer:fine)
  - useReducedMotion()
  - useSpring()
  - useMagnetic(radius=220, strength=0.35)
  - usePinnedHorizontal(): sección sticky de 100vh, altura calculada, translate3d en rAF y barra de progreso; con reduced motion pasa a overflow-x con scroll-snap
  - useCursorTrail()
Todo limpia sus listeners al desmontar. Añade cada pieza a /styleguide.
```

---

## FASE 4 — Maquetación de páginas

> Prompt 5 — intro y hero
```
Usa el skill frontend-design. Lee docs/design/direccion-de-diseno.md ("Intro de marca y home") y docs/design/home-interacciones.md.
Implementa:
- <BrandIntro>: 2,6 s en total
  - emblema amarillo con entrada con rebote
  - revelado circular con clip-path: circle() y el anillo amarillo de 3 px
  - el emblema rueda en arco con unas 2,5 vueltas antihorarias hasta el escudo del menú
  - pop del escudo (0,7 → 1,14 → 1)
  - entrada escalonada del hero desde 1,25 s
  - punto de encaje: centro en x 50 % / y 47,4 % del escudo y diámetro del 62,4 % de su ancho
  - "Skip intro" (también con Esc, Espacio y Enter), scroll bloqueado mientras dura, solo en la primera visita de la sesión (sessionStorage) y sin intro con reduced motion
- Menú fijo, transparente sobre el negro y sólido al hacer scroll, con el escudo de 72 px, el wordmark y el CTA "Register"
- Hero negro: chip "Registration open", titular "TRAIN LIKE A WOLF." con "wolf." en amarillo, CTAs y chips de datos; a la derecha, un escenario neumórfico oscuro 4:5 para el vídeo o la camiseta 3D, con dos badges
- Interacción "linterna": un círculo de luz sigue al ratón y descubre las líneas del campo en amarillo (SVG con mask radial), con la pista "Move your cursor to light up the pitch"
Para cuando esté hecho y espera a que lo revise.
```

> Prompt 5b — resto de la home, en este orden y una sección cada vez (para después de cada una)
```
Cada sección lleva una etiqueta pequeña con un icono de cursor ("Move over a card", "Open a question"…). Interacciones exactas en docs/design/home-interacciones.md.
1. Cinta de logros (ticker) que se pausa al pasar el ratón.
2. Categorías U6–U14 con usePinnedHorizontal: panel de entrada amarillo, un panel #161616 por categoría con número enorme y parallax (el número sigue al ratón y el número fantasma se mueve al revés), y panel final con el CTA "Book free training".
3. Tarifas (#ECECEC): NeuCard expand en Free trial, Monthly y Annual (destacada en negro con "Best value"). Debajo, el calculador "When do you join?": los días del mes actual, con los días desde el 16 en otro tono; al pasar el ratón, enfocar o tocar un día, explica cuánto se paga hoy y cuándo es el primer cobro. La regla sale de config/billing.php y de la misma lógica que BillingStartCalculator (fase 7), compartida con el frontend vía Inertia; el frontend no la duplica.
4. Entrenador (#161616): NeuCard tilt horizontal, zoom lento de la foto, píldora "UEFA PRO License", mini-cards hundidas, línea de tiempo y CTAs.
5. Franja "Associated leagues and partners": píldoras neumórficas con los escudos (liga-interamericana.svg, gysl.svg) en gris y semitransparentes; al pasar el ratón o enfocarlos recuperan el color, crecen y se hunden. Cada escudo enlaza a su página de liga. Preparada para patrocinadores (Joma, CER…).
6. Galería "Life in the pack": useCursorTrail (tarjetas giradas al azar bajo el cursor, 1,6 s de vida, máximo 14 y una cada 110 px de recorrido); en táctil, rejilla estática. CTA "View full gallery" → /gallery.
7. FAQ: acordeón neumórfico; al pasar el ratón, relleno amarillo de izquierda a derecha; al abrir, el "+" gira. Preguntas desde la base de datos (las mismas que usará el bot de WhatsApp).
8. CTA final "Join the pack." con useMagnetic.
9. Footer con dirección, mapa, redes y enlaces legales.
Los textos van en lang/en y lang/es. Usa imágenes placeholder con las proporciones definitivas.
```

> Prompt 5c — página /gallery
```
Implementa /gallery según la sección "Página /gallery" de docs/design/home-interacciones.md (referencia de comportamiento: React Bits Pro "Gallery 8", pero con código propio en Vue, sin usar ese bloque):
- cabecera "LIFE IN THE PACK." y enlace "← Back to home"
- pestañas de filtro con contadores en una píldora hundida: indicador amarillo deslizante, fijas al hacer scroll y manejables con las flechas del teclado
- masonry por JS (4, 3 o 2 columnas, cada foto en la columna más baja); al filtrar, las fotos que sobran se encogen y se desvanecen y las demás se recolocan con un transform de 0,7 s, con la altura del muro animada
- al pasar el ratón: zoom lento y pie de foto con la etiqueta de la categoría; en táctil, el pie está siempre visible
- visor a pantalla completa con anterior/siguiente, contador, Esc, ← y →, que recorre solo el filtro activo
- modelo Photo (categoría, título, subtítulo, imagen, has_media_release) en la base de datos. Solo se publican las fotos con permiso de imagen de los padres
- imágenes responsive (srcset, WebP/AVIF) y lazy loading
```

---

## FASE 5 — Camiseta 3D

> Prompt 6
```
Integra el modelo 3D de la camiseta (public/models/jersey.glb) dentro del escenario neumórfico 4:5 del hero, con TresJS (Three.js para Vue). Si hay vídeo de entrenamiento, el escenario alterna entre la camiseta y el vídeo, o se queda con uno [DECIDIR]:
- comprímelo con Draco o meshopt con gltf-transform y que pese menos de 2 MB
- carga diferida (lazy) solo cuando el hero sea visible; mientras carga, muestra un póster estático (render WebP)
- rotación lenta automática y rotación con el arrastre del ratón o del dedo, con límites; sin zoom
- iluminación de estudio con los reflejos en amarillo de la marca
- en móviles de gama baja o con prefers-reduced-motion se queda la imagen estática
- que no arranque hasta que termine la intro, para no competir con ella
- que no rompa el LCP: mide con Lighthouse antes y después
```

---

## FASE 6 — Ligas

> Prompt 7
```
Crea la sección Leagues con un modelo League (nombre, slug, escudo, descripción, categorías, fechas de temporada, precio de inscripción por equipo, reglamento en PDF, contacto y enlace externo opcional) y su seeder con Liga Interamericana y GYSL.
En la home las ligas solo salen en la franja de escudos (prompt 5b); aquí se construyen sus páginas, a las que enlazan esos escudos [CONFIRMAR CON EL CLIENTE: ¿página propia o enlace externo a la web o la app de cada liga?].
Cada liga tiene su página con el hero en sus propios colores (azul, rojo y blanco, sacados de su escudo), pero siguiendo la misma estructura que el resto del sitio.
Añade un formulario "Register a team" que envía la solicitud por email (todavía sin pago).
Deja un campo external_url por si más adelante enlazamos a la plataforma de gestión de la liga.
```

---

## FASE 7 — Inscripción y Stripe (regla del día 15)

> Prompt 8 (usa /feature-dev o el modo plan)
```
Implementa la inscripción con pago usando Laravel Cashier y Stripe Checkout, sin formulario de tarjeta propio.

Flujo: el padre o tutor rellena sus datos y los del jugador (o jugadores) → acepta el Liability Waiver, los Terms, la Refund Policy y, de forma opcional, el permiso de fotos → elige plan mensual o anual → Stripe Checkout → webhook → el registro queda activo → email de bienvenida.

Regla del día 15 (zona horaria America/New_York; el día 15 cuenta como "antes"):
- Mensual, si se apunta entre el día 1 y el 15: paga el mes en curso completo en ese momento, y la suscripción se ancla al día 1 del mes siguiente (billing_cycle_anchor = día 1 del mes siguiente, proration_behavior = none, con el mes actual como line item de un solo pago en el mismo Checkout).
- Mensual, si se apunta el día 16 o después: no paga nada ahora, porque se le regala lo que queda de mes (trial_end = día 1 del mes siguiente a las 00:00 ET), y el primer cobro es el día 1 del mes siguiente.
- Anual: aplica la misma lógica para fijar la fecha de inicio [CONFIRMAR CON EL CLIENTE: ¿el anual cubre 12 meses o la temporada?].
- Recargo del uniforme como pago único en el primer Checkout si el jugador no lo tiene.
La regla (día de corte 15, zona horaria y precios) se guarda en config/billing.php, y el calculador "When do you join?" de la home la lee de ahí. Mete la lógica de fechas en una clase pura (BillingStartCalculator) con tests de Pest para: día 1, 15 y 16, último día del mes, febrero, año bisiesto y un cambio de horario de verano.

Más cosas:
- Webhooks con la firma verificada: checkout.session.completed, invoice.paid, invoice.payment_failed y customer.subscription.deleted. Que sean idempotentes.
- Stripe Customer Portal para que los padres cambien la tarjeta o cancelen.
- Un panel de admin sencillo (o Filament) con los jugadores, el estado del pago y una exportación a CSV.
- Usa el MCP de Stripe para crear los Products y Prices en modo TEST y dame los IDs para el .env.
```

---

## FASE 8 — Formulario de contacto a correo

> Prompt 9
```
Formularios de contacto, primer entrenamiento gratis y registro de equipo de liga:
- validación en el servidor con FormRequest, honeypot y Cloudflare Turnstile (sin cookies de terceros invasivas), y rate limit de 5 envíos por minuto por IP
- envío con una cola (queue) por Resend o Postmark al correo del club, con reply-to al remitente, y un email de confirmación en el idioma del usuario
- se guarda una copia en la base de datos (tabla inquiries) por si el correo falla
- casilla opcional de consentimiento para recibir comunicaciones por email o WhatsApp
- explícame qué registros SPF, DKIM y DMARC hay que añadir en el DNS del dominio
```

---

## FASE 9 — WhatsApp

> Prompt 10a — botón (rápido)
```
Añade un botón flotante de WhatsApp (abajo a la derecha, que no tape el CTA en móvil) que abre wa.me/1XXXXXXXXXX con un mensaje predefinido que cambia según la página ("Hi! I'm interested in the U10 program…"). Tiene que ser accesible: aria-label y foco visible.
```

> Prompt 10b — chatbot con flujo (WhatsApp Cloud API de Meta)
```
Implementa un bot de WhatsApp con la WhatsApp Business Cloud API:
- un endpoint de webhook en Laravel con la verificación del token y de la firma X-Hub-Signature-256
- un menú inicial con botones interactivos o list messages en EN/ES: Programs & ages / Schedule & location / Prices & payment / Free trial / Leagues / Talk to a coach
- cada opción responde con la información de la base de datos (la misma que usa la web, para que no haya dos versiones distintas) y ofrece el enlace de inscripción
- "Talk to a coach" o cualquier texto libre que no entienda → avisa al dueño (por email o por su WhatsApp) y le responde al usuario con el horario de atención
- guarda las conversaciones y las preferencias de opt-in; deja las plantillas preparadas para recordatorios de pago (solo a quien haya dado su consentimiento)
- tests del flujo con payloads de ejemplo
```
Requisitos previos (los tiene que hacer el cliente): Meta Business Manager verificado y un número de teléfono para la API. Si quieren seguir usando la app de WhatsApp Business con el mismo número, hay que mirar el modo "coexistence". Las respuestas a mensajes que inicia el usuario dentro de 24 h son gratis; las plantillas que inicia el club se pagan por mensaje.
Alternativa sin código para empezar: la app WhatsApp Business ya trae mensaje de bienvenida, mensaje de ausencia, respuestas rápidas y catálogo.

---

## FASE 10 — Legal y accesibilidad ("que no nos demanden")

> Prompt 11
```
Crea borradores (marcados como DRAFT – pending legal review) de:
- Privacy Policy: datos de menores aportados por los padres, COPPA (no recogemos datos directamente de menores de 13 años; quien rellena es el padre o tutor), Stripe como procesador, analítica, email y WhatsApp.
- Terms of Service.
- Refund/Cancellation Policy (con la regla del día 15).
- Liability Waiver / Assumption of Risk, para aceptar con un checkbox, nombre del padre o tutor, fecha y registro de IP y hora.
- Photo/Media Release opcional (sin ese permiso, no se publican fotos del niño).
- Accessibility Statement.
Banner de cookies mínimo: solo aparece si activamos analítica o píxeles, y no se carga nada antes del consentimiento.
Después, haz una auditoría de accesibilidad con Playwright y axe-core en todas las páginas y corrige todos los fallos serios o críticos.
```

---

## FASE 11 — Seguridad

> Prompt 12
```
Ejecuta /security-review sobre todo el repo y después endurece la configuración:
- headers: CSP estricta (permitiendo Stripe, Turnstile y Google Maps), HSTS, X-Frame-Options y Referrer-Policy
- solo HTTPS, cookies secure, httponly y samesite
- en producción: APP_DEBUG=false, nada de stack traces, logs sin datos personales
- composer audit y npm audit sin vulnerabilidades altas
- backups diarios de la base de datos, cifrados
- admin con 2FA
Devuélveme una checklist de lo que se ha hecho y de lo que queda pendiente.
```

---

## FASE 12 — SEO

> Prompt 13
```
SEO local:
- meta title y description por página en EN y ES, con hreflang y canonical
- sitemap.xml y robots.txt
- JSON-LD de SportsOrganization + LocalBusiness (dirección: South Gwinnett High School, 2288 Main St E, Snellville, GA 30078), FAQPage en el FAQ, y SportsEvent para los torneos y ligas
- imágenes en WebP/AVIF con width y height
- Lighthouse por encima de 90 en las cuatro métricas
```

---

## FASE 13 — QA y despliegue

> Prompt 14
```
Con Playwright, prueba de principio a fin: una inscripción el día 10 y otra el día 20 (simulando la fecha), un pago con la tarjeta de test 4242 y otro con 4000 0000 0000 0341 (fallo), el formulario de contacto, el cambio de idioma, la navegación con teclado, la intro (y el botón "Skip intro"), los filtros y el visor de /gallery, y la página entera con prefers-reduced-motion y en táctil (sin ninguna interacción de ratón). Pruébalo en móvil (390px), tablet y escritorio.
Después prepara el despliegue en [Forge + DigitalOcean / Railway / VPS con Docker]: variables de entorno, cola (worker), scheduler, webhooks de Stripe en producción, SSL y el paso de Stripe de modo test a modo live (checklist).
```

---

## ASSETS Y DATOS QUE FALTAN (además de las fotos)

### Marca y diseño
- [x] Los SVG vectorizados: 4 lobos, 3 wordmarks, escudo, 2 escudos de liga y favicon (en `Desktop\GWS\svg-vectorizados\`)
- [ ] El nombre y el archivo de la tipografía del wordmark (y su licencia). Mientras tanto se usa Barlow Condensed
- [ ] El modelo 3D de la camiseta en .glb o .gltf con las texturas (delante y detrás, logos Joma/CER y número), o el archivo fuente. También un render estático para usarlo de póster
- [ ] Los colores oficiales de las dos ligas
- [ ] Vídeo corto de entrenamiento para el hero (opcional, pero luce mucho)

### Contenido que hay que confirmar con el cliente
- [ ] El email correcto (en la web pone "admi@georgiawolvessc.com"), el teléfono y el número de WhatsApp
- [ ] Categorías y edades reales, horarios por categoría y la sede (Instagram y la web antigua se contradicen)
- [ ] Precios: mensual, anual (¿cubre 12 meses o la temporada?), uniforme, matrícula, descuento por hermanos
- [ ] La política de reembolsos (la web antigua dice "no refunds") y el calendario de temporadas
- [ ] Biografías y fotos de los entrenadores (la card del entrenador tiene ahora un placeholder con las iniciales); textos "About" y misión
- [ ] Fotos para la galería, clasificadas por categoría (Training, Matchday, Champions, Events, Team), con su título y el permiso de imagen
- [ ] Descripciones y rangos de edad reales de cada categoría (los paneles del scroll horizontal tienen texto de ejemplo)
- [ ] Precio del plan anual (los 1.000 $ y "ahorra 200 $" del prototipo son inventados)
- [ ] Lista de logros con fecha y categoría
- [ ] Patrocinadores: logos y permiso de uso
- [ ] Ligas: categorías, fechas, precio por equipo, reglamento en PDF, contacto y si hay clasificaciones o resultados que mostrar
- [ ] Testimonios de padres (con permiso)
- [ ] Preguntas frecuentes reales (sirven también para el bot de WhatsApp)

### Cuentas y accesos
- [ ] Dominio y acceso al DNS. ¿Se quedan con gawolvessnellville.com?
- [ ] Hosting (o decidir cuál)
- [ ] La cuenta de Stripe a nombre del club (EIN, cuenta bancaria de EE. UU.), no a tu nombre
- [ ] Meta Business Manager y el número para la WhatsApp API
- [ ] El correo que recibe los formularios, y un proveedor de envío (Resend o Postmark)
- [ ] Perfil de Google Business (para el SEO local) y Google Analytics, si lo quieren

### Legal
- [ ] Razón social exacta (¿es una LLC o una nonprofit?) y dirección legal
- [ ] Que un abogado de Georgia revise el waiver y los términos
- [ ] Los permisos de imagen de los padres para las fotos de niños que ya tenéis
