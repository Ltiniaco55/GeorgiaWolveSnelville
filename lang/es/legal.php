<?php

/*
 * Páginas legales. BORRADORES redactados para construir la web: los tiene que
 * revisar un abogado antes de publicarse. Lo que va entre [corchetes] está
 * pendiente del club. La versión en inglés es la de referencia.
 */
return [
    'draft' => 'BORRADOR – pendiente de revisión legal. Este texto es un borrador de trabajo y todavía no está en vigor.',
    'updated' => 'Última actualización: :date',
    'updated_date' => '26 de noviembre de 2026',
    'contents' => 'Contenido',
    'contact_title' => 'Dudas sobre esta página',
    'contact_body' => 'Escríbenos o visítanos:',
    'meta_title' => ':title · Georgia Wolves Soccer Club Snellville',

    'documents' => [
        'privacy' => [
            'title' => 'Política de privacidad',
            'intro' => 'Cómo Georgia Wolves Soccer Club recoge, usa y protege los datos personales de las familias y los jugadores.',
            'sections' => [
                ['id' => 'who', 'title' => 'Quiénes somos', 'body' => [
                    'Georgia Wolves Soccer Club ("el club") dirige una academia de fútbol juvenil para niños y niñas de 5 a 14 años en Snellville, Georgia. [Razón social y dirección de la entidad, pendientes.]',
                ]],
                ['id' => 'data', 'title' => 'Qué datos recogemos', 'body' => [
                    'De los padres, madres o tutores, cuando reservan un entrenamiento gratis o inscriben a un jugador: su nombre, su relación con el jugador, email, teléfono y dirección; un segundo padre, madre o tutor y un contacto de emergencia, si los dan.',
                    'De cada jugador, aportados por el padre, la madre o el tutor: nombre, fecha de nacimiento, sexo (opcional), talla del uniforme y las notas médicas que el entrenador deba conocer (opcional).',
                    'No recogemos datos directamente de los niños. Los pagos los procesa Stripe: nunca vemos ni guardamos números de tarjeta.',
                ]],
                ['id' => 'use', 'title' => 'Para qué los usamos', 'body' => [
                    'Para organizar los entrenamientos y los equipos, poner a cada niño en su categoría, contactar con las familias, cuidar de la seguridad de los jugadores (contactos de emergencia y notas médicas) y gestionar las inscripciones y los pagos.',
                    'Solo enviamos noticias o promociones si marcaste la casilla opcional para recibirlas, y puedes darte de baja cuando quieras.',
                ]],
                ['id' => 'photos', 'title' => 'Fotos y vídeos', 'body' => [
                    'Solo publicamos imágenes de un niño si el padre, la madre o el tutor dijo que sí en el formulario de inscripción. Consulta la autorización de imagen para ver los detalles y cómo retirar el permiso.',
                ]],
                ['id' => 'security', 'title' => 'Cómo los protegemos', 'body' => [
                    'Los teléfonos, las direcciones, las fechas de nacimiento y las notas médicas se guardan cifrados. Solo accede a ellos el personal del club que los necesita. La web usa HTTPS.',
                ]],
                ['id' => 'sharing', 'title' => 'Con quién los compartimos', 'body' => [
                    'Solo con los proveedores que nos ayudan a gestionar el club (pagos con Stripe, envío de emails, alojamiento de la web) y, cuando hace falta para una competición, con la liga en la que juega un equipo. No vendemos datos personales.',
                ]],
                ['id' => 'rights', 'title' => 'Tus opciones y derechos', 'body' => [
                    'Puedes pedirnos ver, corregir o borrar los datos de tu familia, o que dejemos de usarlos para enviarte noticias, contactando con el club. Guardamos los registros de inscripción durante [plazo pendiente] y después los borramos.',
                ]],
            ],
        ],

        'terms' => [
            'title' => 'Términos del servicio',
            'intro' => 'Las condiciones que se aplican al usar esta web e inscribir a un jugador en el club.',
            'sections' => [
                ['id' => 'acceptance', 'title' => 'Aceptación', 'body' => [
                    'Al reservar un entrenamiento gratis o inscribir a un jugador aceptas estos términos, la exención de responsabilidad y, en los planes de pago, la política de reembolsos.',
                ]],
                ['id' => 'registration', 'title' => 'Inscripción', 'body' => [
                    'El padre, la madre o el tutor legal debe ser mayor de 18 años y dar datos verdaderos. El club pone a cada niño en una categoría (U6–U14) y puede cambiarla después del entrenamiento gratis o por motivos de seguridad.',
                ]],
                ['id' => 'plans', 'title' => 'Planes y pagos', 'body' => [
                    'Plan mensual: si te apuntas del día 1 al 15, pagas el mes en curso al apuntarte; si te apuntas después del 15, el resto del mes es gratis y el primer cobro es el día 1. Plan anual: un solo pago al año, con la misma regla para la fecha de inicio. El uniforme es un pago único.',
                    'Los precios se muestran en la web y pueden cambiar para periodos futuros con aviso previo. Los pagos los procesa Stripe.',
                ]],
                ['id' => 'conduct', 'title' => 'Conducta', 'body' => [
                    'Jugadores, familias e invitados deben respetar a los entrenadores, compañeros, rivales y árbitros. El club puede suspender la participación en caso de conducta grave o repetida.',
                ]],
                ['id' => 'changes', 'title' => 'Cambios de horario', 'body' => [
                    'Los entrenamientos pueden moverse o cancelarse por el tiempo, la disponibilidad del campo o la seguridad. Avisaremos a las familias lo antes posible.',
                ]],
                ['id' => 'law', 'title' => 'Ley aplicable', 'body' => [
                    'Estos términos se rigen por las leyes del estado de Georgia, Estados Unidos.',
                ]],
            ],
        ],

        'refund_policy' => [
            'title' => 'Política de reembolsos',
            'intro' => 'Cuándo y cómo se pueden devolver los pagos hechos al club.',
            'sections' => [
                ['id' => 'monthly', 'title' => 'Plan mensual', 'body' => [
                    'Puedes cancelar cuando quieras desde el portal de padres. La cancelación tiene efecto al terminar el mes ya pagado; los meses ya empezados no se devuelven.',
                ]],
                ['id' => 'annual', 'title' => 'Plan anual', 'body' => [
                    '[Pendiente del club: si se devuelve una parte del pago anual al cancelar y cómo se calcula.]',
                ]],
                ['id' => 'uniform', 'title' => 'Uniforme', 'body' => [
                    'El uniforme se hace para el jugador y no se devuelve una vez entregado, salvo que tenga un defecto de fabricación.',
                ]],
                ['id' => 'club', 'title' => 'Cancelaciones del club', 'body' => [
                    'Si el club cancela un programa o una gran parte de él, las familias recibirán un reembolso o un crédito por la parte no impartida.',
                ]],
                ['id' => 'how', 'title' => 'Cómo pedir un reembolso', 'body' => [
                    'Contacta con el club indicando la referencia de inscripción que recibiste por email. Los reembolsos aprobados se devuelven al mismo medio de pago en [número] días hábiles.',
                ]],
            ],
        ],

        'liability_waiver' => [
            'title' => 'Exención de responsabilidad',
            'intro' => 'Asunción de riesgos, exención y autorización médica que acepta el padre, la madre o el tutor al inscribir a un jugador. Se puede leer aquí y se acepta dentro del formulario de inscripción, donde se guardan el nombre, la fecha y la hora.',
            'sections' => [
                ['id' => 'risk', 'title' => 'Asunción de riesgos', 'body' => [
                    'El fútbol es un deporte de contacto. Entiendo que participar en entrenamientos, partidos y eventos del club conlleva riesgos, como caídas, choques y lesiones, y los acepto en nombre de mi hijo.',
                ]],
                ['id' => 'health', 'title' => 'Salud', 'body' => [
                    'Confirmo que, según mi conocimiento, mi hijo está en condiciones de participar y he informado al club de cualquier condición, alergia o medicación que el entrenador deba conocer.',
                ]],
                ['id' => 'medical', 'title' => 'Autorización médica', 'body' => [
                    'Si mi hijo necesita atención urgente y no se me puede localizar, autorizo al personal del club a buscar tratamiento médico de emergencia para él. Me haré cargo de los costes de esa atención.',
                ]],
                ['id' => 'release', 'title' => 'Exención', 'body' => [
                    'En la medida en que lo permita la ley de Georgia, eximo a Georgia Wolves Soccer Club, a sus entrenadores, voluntarios e instalaciones de las reclamaciones por lesiones derivadas de la participación normal, salvo las causadas por negligencia grave o conducta intencionada.',
                ]],
                ['id' => 'rules', 'title' => 'Normas', 'body' => [
                    'Mi hijo seguirá las indicaciones de los entrenadores y las normas del club, y llevará espinilleras en todas las sesiones.',
                ]],
            ],
        ],

        'media_release' => [
            'title' => 'Autorización de imagen',
            'intro' => 'Cómo puede usar el club las fotos y los vídeos en los que aparecen los jugadores, y cómo cambiar tu decisión.',
            'sections' => [
                ['id' => 'choice', 'title' => 'Tu decisión', 'body' => [
                    'En el formulario de inscripción el padre, la madre o el tutor responde sí o no. Sin un sí, el club no publica imágenes en las que se pueda identificar al niño.',
                ]],
                ['id' => 'use', 'title' => 'Dónde pueden aparecer', 'body' => [
                    'En esta web (galería, páginas), en las redes sociales del club y en material impreso sobre el club. Las imágenes se usan para mostrar la vida del club, nunca para venderlas a terceros.',
                ]],
                ['id' => 'privacy', 'title' => 'Privacidad', 'body' => [
                    'Nunca publicamos junto a una imagen el nombre completo, el colegio, la dirección u otros datos personales de un niño.',
                ]],
                ['id' => 'withdraw', 'title' => 'Si cambias de opinión', 'body' => [
                    'Puedes retirar el permiso cuando quieras contactando con el club. Quitaremos las imágenes de la web y de nuestras redes en un plazo de [número] días; el material impreso ya repartido no se puede recuperar.',
                ]],
            ],
        ],

        'accessibility' => [
            'title' => 'Declaración de accesibilidad',
            'intro' => 'Nuestro compromiso para que todo el mundo pueda usar esta web.',
            'sections' => [
                ['id' => 'standard', 'title' => 'Nuestro objetivo', 'body' => [
                    'Queremos cumplir las Pautas de Accesibilidad para el Contenido Web (WCAG) 2.1, nivel AA.',
                ]],
                ['id' => 'features', 'title' => 'Qué hacemos', 'body' => [
                    'HTML semántico, navegación con teclado y foco visible, contraste de color suficiente, textos alternativos en las imágenes, formularios con etiquetas y mensajes de error claros, y movimiento reducido para quien lo prefiera (sin intro, animaciones ni efectos en movimiento).',
                ]],
                ['id' => 'limits', 'title' => 'Limitaciones conocidas', 'body' => [
                    'Algunas fotos de la galería son de ejemplo y pueden cambiar. Si encuentras una barrera, cuéntanoslo.',
                ]],
                ['id' => 'feedback', 'title' => 'Contacto', 'body' => [
                    'Si tienes problemas para usar alguna parte de la web, contacta con el club: te ayudaremos y lo arreglaremos lo antes posible.',
                ]],
            ],
        ],
    ],
];
