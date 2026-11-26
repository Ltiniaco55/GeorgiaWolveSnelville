# Mapa del sitio y URLs — Georgia Wolves (aprobado el 22 nov 2026)

Complementa a `direccion-de-diseno.md` y `home-interacciones.md`. Si hay alguna contradicción de diseño, mandan esos dos documentos.

## 1. Criterios de las URLs

- **Inglés sin prefijo** (`/programs`) y **español con `/es`** y el slug traducido (`/es/programas`). El inglés es la versión por defecto (`x-default` en hreflang).
- El selector de idioma lleva a la página equivalente en el otro idioma, no a su home. Cada página tiene su `canonical` y sus `hreflang` en/es.
- Los nombres propios no se traducen: `/es/ligas/gysl` y `/es/ligas/liga-interamericana`.
- Los slugs van en minúscula, con guiones y sin barra final.
- Los filtros de la galería van en el query string (`?filter=training`) y el canonical apunta a la URL sin filtro.
- Las páginas de agradecimiento, las del flujo de inscripción y `/styleguide` llevan `noindex` y no entran en `sitemap.xml`.

## 2. Rutas

| Página | EN | ES | Indexable |
|---|---|---|---|
| Home | `/` | `/es` | ✓ |
| About | `/about` | `/es/nosotros` | ✓ |
| Programs | `/programs` | `/es/programas` | ✓ |
| Teams & Achievements | `/teams` | `/es/equipos` | ✓ |
| Leagues | `/leagues` | `/es/ligas` | ✓ |
| ↳ Liga Interamericana | `/leagues/liga-interamericana` | `/es/ligas/liga-interamericana` | ✓ |
| ↳ GYSL | `/leagues/gysl` | `/es/ligas/gysl` | ✓ |
| Gallery | `/gallery` | `/es/galeria` | ✓ |
| Pricing | `/pricing` | `/es/precios` | ✓ |
| Register (formulario de inscripción) | `/register` | `/es/inscripcion` | ✗ |
| Free training | `/free-training` | `/es/entrenamiento-gratis` | ✓ |
| Contact | `/contact` | `/es/contacto` | ✓ |
| FAQ | `/faq` | `/es/preguntas-frecuentes` | ✓ |
| Privacy Policy | `/privacy` | `/es/privacidad` | ✓ |
| Terms of Service | `/terms` | `/es/terminos` | ✓ |
| Refund Policy | `/refund-policy` | `/es/politica-de-reembolso` | ✓ |
| Liability Waiver | `/liability-waiver` | `/es/exencion-de-responsabilidad` | ✓ |
| Media Release | `/media-release` | `/es/autorizacion-de-imagen` | ✓ |
| Accessibility Statement | `/accessibility` | `/es/accesibilidad` | ✓ |
| Gracias: contacto | `/contact/thanks` | `/es/contacto/gracias` | ✗ |
| Gracias: entrenamiento gratis | `/free-training/thanks` | `/es/entrenamiento-gratis/gracias` | ✗ |
| Gracias: registro de equipo de liga | `/leagues/{league}/thanks` | `/es/ligas/{liga}/gracias` | ✗ |
| Pago completado | `/register/success` | `/es/inscripcion/completada` | ✗ |
| Pago cancelado | `/register/cancelled` | `/es/inscripcion/cancelada` | ✗ |
| Styleguide (solo `local`) | `/styleguide` | — | ✗ |
| Errores 403, 404, 419, 429, 500 y 503 | sin ruta, una página Inertia `Error` | se elige el idioma por el prefijo de la URL | ✗ |
| `sitemap.xml`, `robots.txt` | `/sitemap.xml`, `/robots.txt` | — | — |

- **Pricing y Register van separados.** `/pricing` es informativa e indexable (tarifas y calculador del día 15). `/register` es el formulario de inscripción por pasos, con `noindex`. Los botones de las cards de tarifas llevan a `/register?plan=monthly` o `?plan=annual`, con el plan ya elegido.
- **Las páginas de agradecimiento solo se abren después de enviar el formulario.** Si alguien entra directamente, se le redirige. `/register/success` comprueba el `session_id` de Stripe, pero quien activa la inscripción es el webhook, no esta página.

## 3. Secciones y CTA principal por página

### Home
Orden de `home-interacciones.md`. CTA principal: **Book a free training** → `/free-training`
1. Intro de marca
2. Hero: "TRAIN LIKE A WOLF.", linterna sobre el campo y CTAs "Book a free training" y "View programs"
3. Cinta de logros, con enlace a `/teams`
4. Categorías U6–U14: scroll horizontal enganchado **nº 1**. El panel final lleva "Book free training"
5. Tarifas: cards y calculador "When do you join?" (enlace a `/pricing`)
6. Entrenador: card con balancín y CTAs "Train with Christian" y "Read full bio" (a `/about#coach`)
7. Franja "Associated leagues and partners": los escudos llevan a `/leagues/{slug}`
8. Galería "Life in the pack": rastro de fotos y CTA "View full gallery"
9. FAQ: las 5 o 6 preguntas principales y enlace a `/faq`
10. CTA final magnético "Join the pack." → `/register`
11. Footer

### About
CTA principal: **Train with Christian** → `/free-training`
1. Hero negro: titular de historia
2. Nuestra historia: línea de tiempo del club
3. Misión y valores: 3 o 4 cards neumórficas claras
4. `#coach` Christian Tiniaco: card horizontal con balancín, píldora "UEFA PRO License", mini-cards (UEFA PRO, 3 países, 4 academias), trayectoria como entrenador y como jugador
5. Staff técnico, si hay más entrenadores (pendiente del cliente)
6. Adelanto de logros → `/teams`
7. CTA final

### Programs
CTA principal: **Book a free training**
1. Hero: "U6–U14 · Boys & girls · 5 to 14"
2. Categorías: rejilla de cards U6, U8, U10, U12 y U14 con edad, días, hora y enfoque. Cada una tiene su ancla (`#u6` … `#u14`). No hay subpáginas por categoría
3. Horario semanal: una tabla HTML accesible, sin imagen
4. Temporadas: salen de la base de datos (nombre, fechas, descripción). No se da por hecho ningún calendario: la web antigua tenía Spring, Summer, Fall y Winter Futsal. Pendiente del cliente
5. Sede: South Gwinnett High School, con un mapa que se carga al hacer clic (por privacidad) y cómo llegar
6. Qué traer y uniforme
7. Enlace a tarifas → `/pricing`
8. FAQ de programas: preguntas filtradas y enlace a `/faq`
9. CTA final

### Teams & Achievements
No está en el menú principal. Se llega desde el footer, el menú móvil, el adelanto de logros de About y la cinta de logros de la home. CTA principal: **Register** → `/register`
1. Hero: "THE PACK WINS TOGETHER."
2. Contadores animados: títulos, temporadas y jugadores
3. Logros: línea de tiempo por temporada, con scroll horizontal enganchado **nº 2**
4. Equipos por categoría: card de cada equipo con entrenador, liga y división
5. Competiciones, con enlace a la liga
6. Adelanto de galería → `/gallery?filter=champions`
7. CTA final

### Leagues (hub)
CTA principal: **Register a team**
1. Hero: "Two leagues, one community"
2. Dos cards grandes, una por liga: escudo, resumen y enlace a la subpágina
3. Comparativa: categorías, temporada y precio por equipo
4. Cómo inscribir un equipo: 3 pasos numerados
5. FAQ para equipos y delegados
6. CTA final

### Página de cada liga (`/leagues/{slug}`)
Cada liga tiene página propia, con un campo `external_url` opcional para enlazar a su plataforma (pendiente del cliente). CTA principal: **Register a team**
1. Hero con los colores de la liga (sacados de su escudo) y la misma estructura que el resto
2. Sobre la liga
3. Categorías y formato
4. Calendario de la temporada
5. Precio por equipo
6. Reglamento: descarga en PDF
7. Clasificaciones y resultados (solo si el cliente los tiene)
8. Formulario "Register a team" → página de gracias
9. Contacto de la liga y enlace externo, si lo hay

### Gallery
CTA principal: **Book a free training** (al final)
1. Cabecera "LIFE IN THE PACK." con "← Back to home"
2. Pestañas de filtro con contador (All, Training, Matchday, Champions, Events, Team), fijas al hacer scroll
3. Muro masonry
4. Visor a pantalla completa (un modal, no una página)
5. CTA final

### Pricing
CTA principal: **Register** → `/register`
1. Hero: "Simple pricing. First training free."
2. Cards: Free trial, Monthly y Annual (destacada, con "Best value")
3. Calculador "When do you join?" (regla del día 15)
4. Qué incluye y nota del uniforme (pago único)
5. Descuento por hermanos (si se confirma)
6. Resumen de la política de cancelación → `/refund-policy`
7. FAQ de pagos
8. CTA final

### Register
Formulario por pasos, con una barra de progreso hundida. CTA principal: **Continue to secure payment**
1. Padre, madre o tutor
2. Jugadores (uno o varios): la categoría se calcula con la fecha de nacimiento
3. Plan, con el que llegó por el query string ya marcado
4. Acuerdos: Liability Waiver, Terms y Refund Policy (obligatorios) y Media Release (opcional)
5. Resumen: cuánto se paga hoy y cuándo es el primer cobro (calculado con BillingStartCalculator), más el uniforme
6. Stripe Checkout

### Free training
CTA principal: **Book my free training**
1. Hero: "Your first training is on us."
2. Qué esperar: 3 pasos numerados
3. Formulario: datos del tutor, edad del niño (que asigna la categoría) y día preferido
4. Sede y mapa
5. Mini FAQ

### Contact
CTA principal: **Send message**
1. Hero
2. Formulario, con cards al lado: email, teléfono, WhatsApp y dirección
3. Horario de atención
4. Mapa (se carga al hacer clic)

### FAQ
CTA principal: **Contact us** o WhatsApp
1. Hero
2. Pestañas por tema: Programs, Registration & payments, Leagues, Safety, General
3. Acordeón neumórfico (con JSON-LD de FAQPage)
4. "Still have questions?" con enlace a contacto y a WhatsApp

### Legales (Privacy, Terms, Refund Policy, Liability Waiver, Media Release, Accessibility)
Una plantilla común. Sin CTA, solo un enlace de contacto.
1. Título y fecha de última actualización
2. Aviso "DRAFT – pending legal review" mientras no lo revise un abogado
3. Índice con anclas
4. Contenido
5. Contacto para dudas legales

En `/liability-waiver` el texto solo se puede leer. Se acepta dentro de `/register`, donde se guardan el nombre, la fecha, la IP y la hora.

### Páginas de agradecimiento y pago
- `/contact/thanks`: "We'll get back to you within 24 h", con un resumen de lo enviado. CTA: **Explore programs**
- `/free-training/thanks`: día, hora y sede, qué traer y que se ha enviado un email de confirmación. CTA: **Add to calendar** (.ics)
- `/leagues/{league}/thanks`: los siguientes pasos de la inscripción del equipo. CTA: **Download rules** (PDF)
- `/register/success`: "Welcome to the pack." Cuánto se ha pagado, la fecha del primer cobro y el primer entrenamiento. CTA: **Add first training to calendar**. Como CTA secundario, Customer Portal
- `/register/cancelled`: "No charge was made." CTA: **Try again** → `/register`, con los datos que ya había escrito. Como CTA secundario, Contact

### Errores
Fondo negro con el emblema. CTA: **Back to home**
- 404 "Lost from the pack.", con enlaces a Programs, Pricing y Contact
- 419: la página ha caducado, con un botón para recargar
- 429: demasiados envíos, prueba en un minuto
- 403, 500 y 503 (mantenimiento): mensajes en el idioma de la URL

### Styleguide
Tokens, tipografía, `CtaButton` en sus 4 variantes, `NeuCard` en sus variantes, composables y estados de formulario. Su ruta solo existe si `app()->environment('local')`.

## 4. Navegación

**Decisión de Luciano (26 nov 2026), manda sobre lo que sigue:**
- Las ligas no tienen páginas en la web: solo salen como escudos en la home ("Associated leagues and partners") y en el footer, y enlazan a sus Instagram (config/club.php, "leagues"). No se va a hacer nada más con ellas
- Pricing, About (la card del entrenador), Leagues y FAQ del menú y del footer llevan a su sección de la home (/#pricing, /#coach, /#leagues, /#faq). En la home el salto es directo, sin recargar
- Programs es una página "Coming soon" (sin indexar)
- Teams y Contact se quitan del menú móvil y del footer hasta que existan
- Los CTA de las tarifas llevan a las plantillas de inscripción: Free trial → /free-training, Monthly → /register?plan=monthly, Annual → /register?plan=annual
- WhatsApp no se muestra mientras no haya número

**Plantillas de inscripción (26 nov 2026)**: /free-training (entrenamiento gratis) y /register?plan=monthly|annual, con la misma página (Pages/Registration.vue) y el mismo FormRequest (StoreRegistrationRequest):
- Plan (con lo que se paga hoy según la regla del día 15, calculado en el servidor) o día de entrenamiento (los próximos martes y jueves, config/club.php "training_days")
- Jugadores (hasta 4): nombre, apellidos, fecha de nacimiento (la categoría U6–U14 se calcula en el servidor con AgeGroup: edad que cumple en el año en que acaba la temporada, redondeada al grupo; pendiente de confirmar con el club), sexo opcional, uniforme y talla (planes de pago) y notas médicas opcionales
- Padre, madre o tutor (con dirección) y un segundo opcional; contacto de emergencia
- Fotos y vídeos: el padre o la madre elige sí o no de forma explícita (obligatorio), con enlace a la Media Release
- Liability Waiver, Terms y, en los planes de pago, Refund Policy; firma con el nombre completo; consentimiento opcional de comunicaciones
- Al enviar: validación en el servidor, campo trampa contra bots y 5 envíos por minuto por IP. Se guarda como "pending" con teléfonos, dirección, fechas de nacimiento y notas médicas cifrados; email al club (sin datos sensibles, reply-to a la familia) y confirmación a la familia en su idioma. Página de gracias (/free-training/thanks, /register/success) con el resumen solo justo después de enviar
- El pago con Stripe Checkout se engancha en la fase 7

### Menú principal (escritorio)
Escudo y wordmark → `/` · **Programs · Pricing · About · Leagues · Gallery** · selector EN/ES · CTA **Register**
- Teams no va en el menú principal (ver la sección de Teams & Achievements).
- El menú es transparente sobre el negro y sólido al hacer scroll. El enlace activo lleva `aria-current="page"` y un subrayado amarillo.

### Menú móvil
Botón hamburguesa con `aria-expanded` que abre un panel negro a pantalla completa:
- Enlaces grandes en Barlow: Programs, Pricing, About, Teams, Leagues, Gallery, FAQ y Contact
- CTAs: **Register** (principal) y **Book a free training** (secundario)
- Teléfono, WhatsApp y redes sociales
- Selector EN/ES
- Foco atrapado dentro del panel, se cierra con Esc y el foco vuelve al botón

### Barra fija inferior en móvil ("sticky mobile CTA")
Sustituye al botón flotante de WhatsApp en móvil.
- Contenido: **Register** (principal), **Free training** (secundario) y el icono de WhatsApp (con `aria-label`).
- Aparece cuando el hero sale de pantalla (IntersectionObserver).
- Respeta el safe-area de iOS: `padding-bottom: env(safe-area-inset-bottom)`.
- Se esconde mientras el formulario de inscripción está en pantalla.
- La página reserva espacio abajo para que la barra no tape el contenido ni el footer.
- Con `prefers-reduced-motion` aparece y desaparece sin deslizarse.

### Botón flotante de WhatsApp (solo escritorio)
Abajo a la derecha, abre `wa.me` con un mensaje predefinido según la página. Lleva `aria-label` y foco visible. No se muestra en móvil, donde está la barra fija inferior.

### Footer (negro)
- **Club**: About, Teams & Achievements, Gallery, FAQ
- **Train**: Programs, Pricing, Free training, Register
- **Leagues**: Leagues, Liga Interamericana, GYSL
- **Legal**: Privacy, Terms, Refund Policy, Liability Waiver, Media Release, Accessibility
- **Contacto**: dirección de la South Gwinnett HS, email, teléfono, WhatsApp, redes, mapa estático (enlace a Google Maps) y selector EN/ES
- Franja inferior: © razón social (pendiente) y escudo pequeño

## 5. Pendiente del cliente
- Temporadas reales (nombres y fechas).
- Si cada liga tiene una plataforma externa a la que enlazar (`external_url`).
- Clasificaciones y resultados de las ligas.
- Staff técnico, además de Christian.
- Descuento por hermanos.
- Razón social para el footer y los textos legales.
