# Dirección de diseño — web Georgia Wolves (actualizado 22 nov 2026)

## Colores
- Principales: blanco y negro
- Amarillo (#FFD400): color "disruptivo", con energía. Solo para acentos y CTA

## Referencia de diseño general: fraxBIT, "Golf club website design" (TikTok)
https://www.tiktok.com/@fraxbit/video/7702375429876862230
- Estilo editorial y limpio, con mucho blanco. Tarjetas grandes con esquinas redondeadas y fotos
- Paneles negros que se superponen a las secciones blancas al hacer scroll, con titulares grandes en blanco
- Pasos numerados, acordeón junto a una foto, carrusel horizontal de fotos

## Intro de marca y home (propuesta del 22 nov 2026, pendiente de aprobación)
Idea de Luciano, refinada.
Prueba: artifact "Georgia Wolves Home" (https://claude.ai/artifact/BzESgWw8NL8WXqgCGQZha6)

La home empieza en negro (antes era blanca).

Secuencia de la intro (2,6 s en total):
1. 0–0,8 s: pantalla negra. El emblema amarillo (lobo y balón) aparece en el centro, grande: entra pequeño y girado, crece con un ligero rebote y se asienta
2. 0,85–1,8 s: revelado circular. La página aparece a través de un círculo que nace del tamaño del emblema y crece desde el centro hasta cubrir toda la pantalla (clip-path: circle animado en el menú y en el main). A la vez, un anillo amarillo de 3 px se expande por el borde del círculo, como una onda, y se desvanece
   - Se probó un "telón" de dos mitades con filo amarillo y Luciano lo descartó
3. 0,8–2,35 s: el emblema "rueda" como un balón hasta la esquina superior izquierda. Hace una trayectoria en arco, se va encogiendo y gira unas 2,5 vueltas en sentido antihorario (rueda hacia la izquierda). Termina justo encima del círculo central del escudo del menú
4. 2,35–2,6 s: el emblema se funde y aparece el escudo con un "pop" (escala 0,7 → 1,14 → 1)
5. Desde 1,25 s, el contenido del hero entra escalonado (fade y subida de 34 px). Los CTA empiezan su animación de círculo a píldora al terminar la intro

Detalles:
- Botón "Skip intro" (también con Esc, Espacio o Enter). El scroll está bloqueado durante la intro
- Con prefers-reduced-motion no hay intro
- En producción, la intro solo debería salir en la primera visita de la sesión (sessionStorage). En la prueba se repite siempre y hay un botón "Replay intro"
- Punto de encaje calculado sobre el escudo recortado: centro en x 50 % e y 47,4 % de su caja, y diámetro del círculo igual al 62,4 % de su ancho

Hero (escaparate):
- Menú fijo y transparente sobre el negro: el escudo (72 px de alto), el wordmark "GEORGIA WOLVES / Snellville soccer club", los enlaces y el CTA "Register". Al hacer scroll se vuelve sólido
- Izquierda:
  - Chip "Registration open · Fall season"
  - Titular gigante "TRAIN LIKE A WOLF." con "wolf." en amarillo
  - Subtítulo
  - CTAs "Book a free training" y "View programs"
  - Chips de datos: días, dirección, primer entrenamiento gratis
- Derecha: escenario hundido (neumórfico oscuro, proporción 4:5). De momento lleva la camiseta 3D; más adelante, una foto de los niños de la academia (decisión de Luciano: más humana y cercana). Lleva dos badges: "U12 Champions 2026" (amarillo) y "GYSL Fall League · Premier Division"
- Debajo: una cinta de logros en movimiento (ticker) que se pausa al pasar el ratón
- Después, la primera sección blanca, para enseñar el paso de negro a blanco

Assets:
- Los logos ya están vectorizados de verdad (22 nov 2026) en resources/svg/raw/: wolf-yellow, wolf-black, wolf-white, wolf-yellow-black (512×512), wordmark-yellow/white/black (100 de alto), crest (437×512), liga-interamericana, gysl y favicon. Los monocromos usan currentColor
- Los archivos originales de Luciano eran PNG incrustados dentro de un SVG (1,1 MB cada uno); ya no se usan

## Botón CTA — versión final aprobada por Luciano
Referencia de forma: Daniyal Alam, "Padel club website" (TikTok) — píldora con flecha.
Prueba: artifact "Georgia Wolves CTA" (https://claude.ai/artifact/A2FZ9DsAeeWe3mneZXtydf)

Comportamiento con el scroll:
- La altura del botón es la misma que la del círculo de la flecha (círculo = botón)
- Cada vez que el botón aparece al hacer scroll, empieza como un círculo con la flecha y se alarga hasta ser una píldora con el texto
- Al seguir bajando y salir de pantalla, vuelve a convertirse en círculo. Al volver a subir, se abre otra vez
- Animación lenta: la apertura dura unos 1,4 s y el texto aparece con un fundido a partir de los 0,75 s

Botón principal sobre fondo blanco:
- Fondo amarillo, texto negro, flecha negra
- Al pasar el ratón: fondo negro carbón, texto blanco, flecha blanca, borde negro

Botón principal sobre fondo negro:
- Fondo amarillo, texto negro, flecha negra
- Al pasar el ratón: fondo negro carbón, texto y flecha amarillos, borde amarillo fino

Botón secundario sobre fondo blanco:
- Transparente con borde negro, texto y flecha negros
- Al pasar el ratón: fondo negro carbón, texto y flecha blancos

Botón secundario sobre fondo negro:
- Transparente con borde blanco, texto y flecha blancos
- Al pasar el ratón: fondo blanco, texto negro

Efectos aprobados:
- 3D hacia dentro (botón hundido):
  - Sombra interior oscura arriba y brillo interior abajo
  - Línea de luz de 1 px bajo el borde
  - Texto con un ligero relieve grabado
  - Al hacer clic, el botón se oscurece un poco
- Cuando el botón pasa a negro, se usa #262626 (carbón) en lugar de negro puro, para que se vea el hundido. Las sombras interiores van reforzadas
- Expansión al pasar el ratón: scale(1.09) con un ligero rebote, cubic-bezier(.34,1.56,.64,1) en 0,35 s. Al hacer clic, scale(1.03)

Descartados tras probarlos:
- 3D hacia fuera (relieve, inclinación hacia el cursor)
- Halo de luz alrededor del botón
- Glassmorphismo

Notas técnicas:
- IntersectionObserver con rootMargin del -10% que pone o quita la clase .open
- Se anima el width de la altura (círculo) al ancho medido del texto. El texto aparece con un fade retrasado
- Con prefers-reduced-motion los botones salen siempre abiertos y sin expansión al pasar el ratón
- Reserva de hueco: el botón va dentro de un contenedor (.gw-cta-slot) que siempre mide lo que la píldora abierta. Abrir o cerrar no cambia el layout: no salta de fila ni produce desplazamientos (CLS). La flecha se mueve con transform, no con right
- El IntersectionObserver vigila ese contenedor, no el botón, y tiene histéresis: abre cuando el contenedor está entero dentro del viewport con el margen del -10 % y solo se cierra cuando sale del viewport por completo. Así no parpadea en el límite
- El botón no se cierra al pasar el ratón ni al tener el foco: con el foco del teclado se abre aunque esté fuera de la zona de apertura

## Tipografía
- Titulares, nombres y precios: Barlow Condensed 700/800 (provisional, hasta tener la fuente del wordmark)
- Texto, botones y etiquetas: Inter 400/500/600. El botón va en Inter 600, 16 px (15 px en móvil)

## Scroll horizontal en algunas secciones (propuesta del 22 nov 2026, pendiente de aprobación)
Petición de Luciano: que el scroll sea más divertido y que algunas secciones, en vez de bajar, avancen hacia la derecha.
Prueba: sección "Find your category" en el artifact "Georgia Wolves CTA", entre la sección de logros y las tarifas.

Cómo funciona:
- Al llegar a la sección, esta se queda fija ("pinned", position: sticky, 100vh) y el scroll vertical de la rueda mueve una fila de paneles hacia la izquierda. Al llegar al último panel, el scroll vertical continúa con normalidad
- La altura de la sección se calcula con JS (altura visible + distancia horizontal que hay que recorrer). La posición se aplica con translate3d en requestAnimationFrame
- Barra de progreso amarilla, hundida, debajo de los paneles
- Indicación "Keep scrolling →" con una flecha amarilla que se mueve
- Los CTA que entran desde la derecha también hacen la animación de círculo a píldora
- Con prefers-reduced-motion se convierte en un carrusel horizontal normal (overflow-x con scroll-snap), sin enganchar la página

Contenido de ejemplo:
- Panel de entrada amarillo: "U6–U14, boys and girls, 5 to 14"
- Un panel por categoría (U6, U8, U10, U12, U14): número enorme en amarillo, título, descripción y chips de edad y días. Paneles neumórficos oscuros (#161616)
- Panel final negro con el CTA "Book free training"
- Las descripciones y los rangos de edad por categoría son de ejemplo: hay que confirmarlos con el cliente

Dónde usarlo (sugerencia, sin decidir): categorías o programas, logros/temporadas, galería y ligas. Conviene no usarlo más de 2 o 3 veces en la web, para que no canse.

## Cards — estilo neumorphism (propuesta del 22 nov 2026, pendiente de aprobación)
Prueba: secciones "Card — Tarifas" y "Card — Entrenador" en el artifact "Georgia Wolves CTA".

Regla base: la card es del mismo color que el fondo de su sección y se distingue solo por dos sombras exteriores, una clara arriba a la izquierda y otra oscura abajo a la derecha. Los elementos interiores van hundidos o en relieve con la misma técnica. El neumorphism no funciona sobre blanco puro ni sobre negro puro, así que las secciones de cards usan:
- Claro: fondo y card #ECECEC, con sombras #C8C8C8 (oscura) y #FFFFFF (clara), de 14 px y 30 px de desenfoque
- Oscuro: fondo y card #161616, con sombras #090909 (oscura) y #232323 (clara), de 18 px y 36 px de desenfoque

### Movimiento al pasar el ratón
Cards de tarifas: expansión.
- scale(1.035) con un ligero rebote, cubic-bezier(.34,1.56,.64,1) en 0,4 s; la sombra crece a la vez
- Se probó el balancín en estas cards y Luciano lo descartó

Card del entrenador: "balancín".
- La card se inclina en 3D hacia el puntero y la esquina donde está el ratón se hunde, como si el puntero tuviera peso
- Física de muelle (spring) con requestAnimationFrame: al sacar el ratón, la card vuelve al centro balanceándose un poco
- Valores:
  - Perspectiva: 1000 px
  - Inclinación máxima: 4°
  - Muelle: rigidez 0,055 y amortiguación 0,86
- Solo con ratón (no en táctil). Con prefers-reduced-motion se desactiva

### Card de tarifas (sección clara #ECECEC)
- Tres cards: Free trial ($0), Monthly y Annual (destacada)
- Cards normales: en relieve, del mismo gris que el fondo, con esquinas de 28 px
- Card destacada (Annual), pedida por Luciano para que destaque: fondo negro (#0B0B0B)
  - Precio y línea de ahorro en amarillo; texto blanco y gris claro
  - Etiqueta amarilla "Best value"
  - Checks en círculos amarillos
  - Hundido interior sutil
  - Mantiene las sombras neumórficas exteriores (blanca arriba a la izquierda, gris abajo a la derecha), así sigue integrada en la sección
- Los checks de las cards normales son círculos hundidos con el check negro
- Separador "grabado": dos líneas finas (gris y blanca en las cards claras; negra y gris oscura en la negra)
- Estructura de la card:
  - Nombre del plan
  - Precio grande
  - Ahorro
  - Descripción
  - Ventajas
  - Nota de la regla del día 15
  - CTA (secundario en las normales, principal en la destacada)
- Precios de ejemplo: 100 $/mes (de la web antigua). El anual (1.000 $, "ahorra 200 $") es inventado y está pendiente de confirmar con el cliente
- Uniforme: 80 $, pago único, en una nota debajo de las cards

#### Tarifas en móvil: efecto "baraja" (propuesta del 23 nov 2026, pendiente de aprobación)
Solo por debajo de 640 px. En escritorio se mantienen en fila (de 640 a 1023 px, en una columna).
Prueba: sección "Pricing" de /styleguide a 390 px. Componente PricingDeck con el composable useDeckScroll.
- Las tres cards apiladas como una baraja: Annual (la destacada) arriba y, debajo, Monthly y Free trial asomando un poco, desplazadas 20 px por nivel y giradas entre 2,5° y 3,5°
- Orden en móvil: Annual → Monthly → Free trial. En escritorio: Free trial · Monthly · Annual. El orden del DOM cambia con el ancho, así el orden del teclado y del lector de pantalla coincide con el visual
- La sección se engancha al scroll (position: sticky, 100svh; altura = 100svh + 70svh por cada cambio de card). Al avanzar, la card de arriba sale hacia arriba girando (-12°) y se mete al fondo del mazo, y queda arriba la siguiente. Cada paso tiene una zona de reposo al principio y al final para que la card se quede quieta antes de que salga la siguiente. Después de la última, el scroll sigue normal
- También se cambia de card con un deslizamiento horizontal (swipe) sobre el mazo: a la izquierda, la siguiente; a la derecha, la anterior. El deslizamiento vertical es el scroll normal
- Puntos indicadores debajo (botones con aria-label "Plan 1 of 3" y aria-current en el activo) y la pista "Scroll or swipe to see the next plan"
- Animación solo con transform en requestAnimationFrame, sin saltos de layout. La opacidad no se anima: una card semitransparente volando sobre la siguiente mezcla los textos de las dos
- El scroll es la única fuente de verdad: el swipe, los puntos y el foco desplazan la página hasta la posición de esa card
- Accesibilidad: las tres cards están siempre en el DOM y se llega a todas con el teclado; al recibir el foco, esa card pasa arriba
- Con prefers-reduced-motion se muestran apiladas en vertical (Annual primero), sin animación ni enganche
- Cuenta como uno de los 2 o 3 scroll enganchados de la web

### Card de entrenador (sección oscura #161616)
- Card horizontal en relieve, con esquinas de 32 px: foto a la izquierda (5/12) y contenido a la derecha (7/12). En móvil se apila
- La foto va en un hueco hundido, con un margen de 16 px y esquinas de 24 px. Al pasar el ratón, zoom lento. Encima lleva la píldora amarilla "UEFA PRO License"
- Contenido:
  - Cargo en amarillo
  - Nombre enorme en mayúsculas condensadas
  - Bio corta
  - Tres datos en mini-cards hundidas: UEFA PRO / 3 países / 4 academias
  - Trayectoria como entrenador en una línea de tiempo, con separadores grabados
  - Trayectoria como jugador en chips en relieve
  - CTAs: "Train with Christian" (principal) y "Read full bio" (secundario)
- Pendiente: la foto real del entrenador (por ahora, un placeholder con las iniciales)
