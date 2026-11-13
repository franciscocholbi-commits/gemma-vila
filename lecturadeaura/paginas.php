<?php
// Contenido de las páginas de lecturadeaura.es en el diseño de Gemma Vila.
// Requiere lib.php. Devuelve un array slug => id.

$IMG = ['retrato' => 13, 'charla' => 14, 'grupo' => 15, 'foto' => 18, 'firma' => 19];
$WA_CONSULTA = lda_wa('Hola Gemma, quiero reservar una consulta de lectura de aura.');
$WA_CURSOS   = lda_wa('Hola Gemma, quiero información sobre los cursos de lectura de aura.');
$WA_MASTER   = lda_wa('Hola Gemma, quiero información sobre el Máster de Sanadores.');
$U = function ($s) { return home_url('/' . $s . '/'); };
$out = [];

// Testimonios completos sacados de la web antigua.
$tj = json_decode(file_get_contents('/home/hosting177539eu/tmp-lda/content/testimonios.json'), true);
$testis = []; $cur = null;
foreach ($tj['blocks'] as $b) {
	if ($b[0] !== 'p') { continue; }
	$t = trim(html_entity_decode(strip_tags($b[1]), ENT_QUOTES, 'UTF-8'));
	if ($t === '' || $t === 'María José Lotus Iris Gibbons') { continue; }
	$words = str_word_count($t, 0, 'áéíóúüñÁÉÍÓÚÑ');
	$es_nombre = (strpos($t, 'Josy Gibbons') === 0) || ($words <= 7 && !preg_match('/[.!?:]$/u', $t) && stripos($t, 'Agradezco') !== 0);
	if ($es_nombre) {
		if ($cur && !$cur['txt']) { continue; }
		if ($cur) { $testis[] = $cur; }
		$nombre = $t;
		if (strpos($t, 'Josy Gibbons') === 0) { $nombre = 'Josy Gibbons · Terapeuta holística'; }
		if (strpos($t, 'Luis Moulin') === 0) { $nombre = 'Luis Moulin · Corrientes (Argentina)'; }
		if ($t === 'Alberto Italia') { $nombre = 'Alberto Bertols · Italia'; }
		if ($t === 'Belén Castro') { $nombre = 'Belén Belcastro'; }
		$cur = ['nombre' => $nombre, 'txt' => []];
		continue;
	}
	if (!$cur) { continue; }
	if (preg_match('/^[A-ZÁÉÍÓÚ ]+:$/u', $t)) { $cur['txt'][] = '<strong>' . esc_html(mb_convert_case(rtrim($t, ':'), MB_CASE_TITLE, 'UTF-8')) . '</strong>'; continue; }
	if (stripos($t, 'Agradezco') === 0) { $t = 'Agradezco, agradezco, agradezco.'; }
	$cur['txt'][] = esc_html($t);
}
if ($cur) { $testis[] = $cur; }
$testi_cards = [];
foreach ($testis as $x) {
	if (!$x['txt']) { continue; }
	$testi_cards[] = lda_quote('<p>' . implode('</p><p>', $x['txt']) . '</p>', $x['nombre']);
}
$testi_cortos = [
	lda_quote('<p>Yo quería saber si mi camino espiritual eran las armonizaciones y esa lectura fue fantástica: no solo me dio la respuesta que buscaba, también me mostró virtudes que no estaba desarrollando.</p>', 'Belén Leal'),
	lda_quote('<p>Poder leer el aura a otra persona y verla sentir que lo que se veía le resonaba, hasta llegar a las lágrimas, fue una experiencia que nunca había imaginado poder realizar.</p>', 'Alcides Kilgelmann'),
	lda_quote('<p>Fue conectar con la verdad de mi espíritu de manera simple y tomar consciencia de pensamientos que me tenían repitiendo patrones de comportamiento nocivos.</p>', 'Belén Belcastro'),
];

$tipos_lectura = [
	['Lectura de Aura General', '<p>Da información general de tu situación, tus habilidades y tus bloqueos. Se entrega un dossier con las imágenes recibidas, como un mapa de lo trabajado y de lo que puede seguir trabajándose.</p>'],
	['Lectura de Aura Vidas Pasadas', '<p>Tomar consciencia de lo realizado o aprendido, o de lo que quedó pendiente, para terminar ese aprendizaje en el presente y mejorar así tu vida. Trae entendimiento y paz a tu vida de hoy.</p>'],
	['Lectura de Aura Relacionamiento', '<p>Trae claridad para ver qué pactos espirituales te unen a ciertas personas y por qué están en tu vida. Hace que las relaciones, sean amorosas, familiares o laborales, sean más fluidas y transparentes.</p>'],
	['Lectura de Aura Prosperidad y Manifestación', '<p>Para personas bloqueadas o con vibraciones de escasez en el plano económico o de creación. La lectura reconoce la causa y conecta al ser con su habilidad manifestadora.</p>'],
	['Lectura de Aura Camino de Vida Espiritual', '<p>¿En qué punto de tu camino espiritual estás? ¿Qué te impulsa y qué te frena a seguirlo?</p>'],
	['Lectura de Aura Energía Femenina', '<p>La mujer conecta con su propia belleza y creación, observando energías que quizá no le pertenecen pero que están en su linaje. Al soltarlas, conecta con su poder y su vida se vuelve más armoniosa y creativa.</p>'],
	['Lectura de Aura Viajes y Cambios', '<p>Ayuda a destrabar dudas sobre situaciones vinculadas con viajes, ya sea a lugares geográficos o en tu vida misma.</p>'],
];
$sesion_incluye = '<ul><li>Cada lectura dura entre hora y media y dos horas.</li><li>Va acompañada de una sanación y un reequilibrio de los chakras.</li><li>Hay un espacio para preguntas y dudas que surjan durante la consulta.</li><li>Se puede grabar, y recibes un dossier: todo lo que vemos y leemos queda dibujado en colores.</li><li>Puede ser presencial o a distancia.</li></ul>';

// ---------------------------------------------------------------- INICIO
$out['inicio'] = lda_page('inicio', 'Lectura de Aura', [
	lda_hero('Escuela de Lectura y Sanación de Aura · Gemma Vila', 'Aprende a ver con más claridad, desde el alma',
		'<p>La lectura de aura es una lectura al ser, al alma de la persona. Te acompaño a conocer tu energía, liberar los bloqueos que no te dejan avanzar y conectar con tu verdadero potencial, con más de 25 años enseñando el método FSF de visión aural.</p>',
		[['Reservar una consulta', $WA_CONSULTA], ['Qué es la lectura de aura', $U('que-es-la-lectura-del-aura')]], $IMG['retrato']),
	lda_sec('lda-sec--blanco', [
		lda_head('Lectura de aura', 'Todo ser vivo tiene aura', '<p>El aura es el campo electromagnético que nos rodea. Presenta diferentes frecuencias de vibración y refleja el estado de nuestro cuerpo, mente y ser espiritual. Todos podemos aprender a verla y a leerla: es una habilidad natural que se entrena.</p>'),
		lda_row([
			lda_card('¿Qué es el aura?', '<p>Qué es el campo áurico, sus colores y qué significa cada uno según el chakra en el que aparece.</p>', ['Leer más →', $U('que-es-la-lectura-del-aura')]),
			lda_card('¿Qué te aporta?', '<p>Una herramienta para ver tu verdad, reconocer tus dones y limpiar lo que no te deja ser tú.</p>', ['Leer más →', $U('que-te-aporta-la-lectura-del-aura')]),
			lda_card('¿Para quién?', '<p>Para cualquier persona que quiera ser más consciente de su vida, y para terapeutas que quieran potenciar su trabajo.</p>', ['Leer más →', $U('para-quien')]),
		], 'lda-grid'),
	]),
	lda_sec('', [
		lda_head('Lo que te ofrezco', 'Consultas, cursos y formación de sanadores'),
		lda_row([
			lda_card('Consulta privada de lectura de aura', '<p>Una experiencia de autoconocimiento gratificante y sanadora. A través de imágenes, colores y sensaciones vemos tus habilidades, tus bloqueos y las conductas que te estancan.</p>', ['Ver consultas →', $U('lectura-del-aura')], 'Sesión individual'),
			lda_card('Cursos de lectura y sanación del aura', '<p>Cuatro niveles de desarrollo espiritual: meditación y trabajo con tu aura y chakras, sanación aural, subir tu vibración y sanación de espacios.</p>', ['Ver cursos →', $U('cursos')], 'Formación'),
			lda_card('Máster de Sanadores', '<p>Un viaje de autoconocimiento de 10 meses en el que te formas como terapeuta: lectura de aura, registros akáshicos, numerología, cábala aplicada y más.</p>', ['Ver el máster →', $U('presentacion-curso-de-sanadores-del-aura')], 'Formación completa'),
		], 'lda-grid'),
	]),
	lda_sec('lda-sec--blanco', [
		lda_box([
			lda_box([lda_img($IMG['charla'], 'lda-img--portrait')]),
			lda_box([
				lda_eyebrow('Quién soy'),
				lda_h('Gemma Vila Pagès', 'h2'),
				lda_t('<p>Soy psicóloga clínica por la Universidad Ramon Llull. Al acabar la carrera me fui a estudiar a la Foundation for Spiritual Freedom (FSF) de Berkeley, California, donde aprendí todo lo que hoy enseño sobre el aura, los chakras, la visión aural y el crecimiento espiritual.</p><p>Allí conocí a mi maestro, Michael C. Fikaris, con quien colaboro desde entonces en proyectos y cursos por todo el mundo: España, Argentina, Uruguay, Portugal, Suecia… Más de 25 años enseñando, compartiendo y aprendiendo en este camino.</p>'),
				lda_btn('Conóceme', $U('quien-soy-yo'), 'lda-btn--ghost'),
			]),
		], 'lda-split', 'row'),
	]),
	lda_sec('', [
		lda_head('Testimonios', 'Lo que dicen quienes han pasado por la escuela'),
		lda_row($testi_cortos, 'lda-grid'),
		lda_btn('Leer todos los testimonios', $U('testimonios'), 'lda-btn--ghost'),
	]),
	lda_cta(),
], 'Lectura de Aura · Escuela de Lectura y Sanación de Aura de Gemma Vila', 'Consultas de lectura de aura, cursos de lectura y sanación del aura y Máster de Sanadores con Gemma Vila, psicóloga y formadora del método FSF de visión aural.');

// ---------------------------------------------------------------- MÁSTER
$mes = function ($titulo, $sub, $items) { return [$titulo . ' · ' . $sub, '<ul><li>' . implode('</li><li>', $items) . '</li></ul>']; };
$out['presentacion-curso-de-sanadores-del-aura'] = lda_page('presentacion-curso-de-sanadores-del-aura', 'Máster de Sanadores del Aura', [
	lda_hero('Máster de Sanadores · Gemma Vila', 'Cambia tu vida, vive tu propósito y transfórmate en terapeuta',
		'<p>«Dame 10 meses y te cambio la vida». Un máster completo de desarrollo personal que trabaja el plano físico, mental y espiritual desde diferentes asignaturas. Un viaje de autoconocimiento y expansión para desarrollar tus habilidades innatas y despertar tu potencial interior.</p>',
		[['Quiero información', $WA_MASTER]], $IMG['grupo']),
	lda_sec('lda-sec--blanco', [
		lda_head('Asignaturas', 'Lo que estudiarás'),
		lda_row(array_map(fn($a) => lda_box([lda_t('<p>' . $a . '</p>')], 'lda-chip'), ['Lectura y sanación del aura', 'Registros akáshicos', 'Numerología alquímica', 'Avatar 1 y 2', 'Técnica Estructural (opcional)', 'Descodificación y desprogramación básica', 'Geometría sagrada', 'Cábala aplicada']), 'lda-grid lda-grid--4'),
	]),
	lda_sec('', [
		lda_box([
			lda_box([
				lda_eyebrow('Qué aprenderás'),
				lda_h('Diez meses de trabajo contigo y con los demás', 'h2'),
				lda_t('<ul><li>Una nueva perspectiva de la vida y un crecimiento espiritual.</li><li>Trabajo consciente para gestionar tus emociones.</li><li>Sanarte a ti y a los demás a través del aura, los chakras y los cordones energéticos.</li><li>Limpieza y armonización de tu campo áurico.</li><li>Técnicas para elevar tu vibración y protegerte energéticamente.</li><li>Desarrollo de tu visión interna y activación de la glándula pineal o tercer ojo.</li><li>Trabajo con la vibración del amor y la lectura del alma.</li><li>Formación como terapeuta en visión cuántica, lectura de aura y numerología, en los cuatro planos: espiritual, mental, emocional y físico.</li><li>Lectura del aura: interpretación de sus colores y significados.</li><li>El mundo de los sueños, técnicas avanzadas de sanación y registros akáshicos.</li><li>Trabajo profundo con el árbol genealógico, la familia y tu clan.</li></ul>'),
			]),
			lda_box([
				lda_eyebrow('Temas que trabajarás'),
				lda_t('<ul><li>Autoconocimiento, el sendero interior</li><li>Abundancia y prosperidad</li><li>Crear la vida que quieres</li><li>Subir de vibración</li><li>Gestionar mejor tus pensamientos</li><li>Ser consciente de cómo hablas</li><li>Hacer que las cosas sucedan fácilmente</li><li>Conocer y aceptar tu sombra</li><li>Conocer tus habilidades y tus bloqueos</li><li>Pasar a la acción y encontrar tu propósito de vida</li><li>Ser terapeuta del aura y numerólogo</li><li>Trabajo consciente con el cuerpo físico</li></ul>'),
			], 'lda-card'),
		], 'lda-split', 'row'),
	]),
	lda_sec('lda-sec--blanco', [
		lda_head('Plan de estudios', 'El recorrido, mes a mes'),
		lda_toggle([
			$mes('Meses 1 y 2', 'Trabajo interno para uno mismo', ['Técnicas de meditación (nivel 1): tomar conciencia de tu vida y de tu punto de partida', 'Subir la vibración: trabajo con chakras', 'Limpieza personal del aura', 'Glándula pineal / tercer ojo', 'Avatar 1: todo es energía', 'El poder de la palabra y del pensamiento; alimentación consciente, silencio y ayuno', 'Tipos de almas y principios herméticos', 'Cómo afecta la programación a tu vida']),
			$mes('Meses 3 y 4', 'Trabajo de sanación contigo y con los demás', ['Sanación aural: técnicas para sanarte a ti y a los demás', 'Técnicas de meditación (nivel 2)', 'Principios de numerología: qué número eres', 'Conocer tu firma y tu destino', 'Avatar: meditación y rituales. Cábala aplicada', 'Abundancia: técnicas y ejercicios para manifestar', 'Planos astrales y viaje astral']),
			$mes('Meses 5 y 6', 'Empoderamiento y visión', ['Lectura de aura: técnicas y práctica', 'Meditaciones avanzadas', 'Manifestación (nivel 2)', 'Geometría sagrada', 'Victimismo frente a poder', 'Gestión de emociones', 'Tu propósito en esta vida']),
			$mes('Meses 7 y 8', 'Tomar acción', ['Trabajo con tu subconsciente, tus limitaciones y tus miedos', 'Imágenes mentales y patrones familiares; fidelidad a tu clan', 'Práctica de lectura de aura', 'Meditaciones y sanaciones de nivel avanzado', 'Registros akáshicos', 'Avatar 2: entidades, origen de la humanidad, el juego de la vida']),
			$mes('Meses 9 y 10', 'Ver tu propósito y vivirlo', ['Sanación, meditación y lectura de aura', 'Práctica, nuevas técnicas y repaso', 'Ver tus límites; ver de dónde vienes, honrarlo y aceptarlo', 'Crear un plan y una estrategia', 'Numerología (nivel 2)']),
		]),
	]),
	lda_sec('', [
		lda_head('Cómo es el máster', 'Online, acompañada y con certificado'),
		lda_row([
			lda_card('Clases por Zoom', '<p>Clases en directo y grabadas, para que puedas revisarlas cuando quieras. Contenido multimedia en una plataforma virtual.</p>'),
			lda_card('Acompañamiento', '<p>Grupo de WhatsApp para resolver dudas y compartir experiencias, y un libro de ejercicios para complementar el aprendizaje.</p>'),
			lda_card('Certificado', '<p>Al terminar recibes el certificado de la Escuela Europea de Lectura de Aura que avala los conocimientos y habilidades adquiridos.</p>'),
			lda_card('Extras opcionales', '<p>Retiro presencial de fin de curso, formación en Técnica Estructural (módulos 1 y 2) y mentoría individual conmigo una vez al mes.</p>'),
		], 'lda-grid lda-grid--4'),
		lda_t('<p>Fechas, precio y plazas de la próxima edición: escríbeme y te lo envío.</p>', 'lda-muted'),
	]),
	lda_cta('¿Empezamos?', '<p>Cuéntame en qué momento estás y te explico cómo es la próxima edición del Máster de Sanadores.</p>', 'Pedir información del máster', 'Hola Gemma, quiero información sobre el Máster de Sanadores.'),
], 'Máster de Sanadores del Aura · Gemma Vila', 'Máster de 10 meses para formarte como sanador y terapeuta del aura: lectura y sanación del aura, registros akáshicos, numerología, cábala y más, con Gemma Vila.');

// ---------------------------------------------------------------- CURSOS
$nivel = fn($n, $t, $d, $lecciones) => [$n . ' · ' . $t, '<p>' . $d . '</p>' . ($lecciones ? '<ul><li>' . implode('</li><li>', $lecciones) . '</li></ul>' : '')];
$out['cursos'] = lda_page('cursos', 'Cursos de lectura y sanación del aura', [
	lda_hero('Escuela de Lectura y Sanación de Aura', 'Cursos de la Escuela de Lectura y Sanación de Aura Gemma Vila',
		'<p>Cuatro cursos de desarrollo espiritual, enfocados al trabajo personal: aprender a ver desde la perspectiva de tu ser y potenciar tu capacidad sanadora. Haciendo los cuatro niveles puedes acceder a la certificación Máster internacional de la FSF (Foundation for Spiritual Freedom) de California.</p>',
		[['Quiero información', $WA_CURSOS]], $IMG['charla']),
	lda_sec('lda-sec--blanco', [
		lda_head('Qué conseguirás', 'Herramientas para elevar tu conciencia y crear tu vida'),
		lda_row([
			lda_card('Liberarte', '<p>Aprenderás a liberarte de energías tóxicas que te bloquean y que no son tuyas.</p>'),
			lda_card('Recuperar tu energía', '<p>Recobrarás y conectarás con tu propia energía sanando tu aura, sanándote y conociéndote.</p>'),
			lda_card('Ser tú', '<p>Expresarás tu esencia verdadera y te mantendrás conectada a tu fuente.</p>'),
			lda_card('Ayudar a otros', '<p>Podrás ayudar a las personas que quieres o a tus pacientes con una comunicación de alma a alma.</p>'),
		], 'lda-grid lda-grid--4'),
	]),
	lda_sec('', [
		lda_head('Programa', 'Los cuatro niveles', '<p>Te recomiendo seguir el orden de los niveles. Si ya tienes experiencia, puedes empezar por otro y hacer el primero más adelante.</p>'),
		lda_toggle([
			$nivel('Nivel 1', '«Tú y tu aura»', 'Curso de iniciación en 12 lecciones (vídeos, lecturas y ejercicios): meditación y trabajo con tu aura y tus chakras.', ['Introducción', '¿Qué es el aura?', 'Anclaje y sol dorado', 'Chakras y colores', 'Limpieza de chakras con rosas', 'Técnica de la rosa (aplicaciones)', 'Neutralidad y estado centrado', 'Protección de energías', 'La energía de la Tierra (meditación)', 'Energía cósmica (meditación)', 'Limpieza del aura: proceso completo', 'Conclusiones y próximos pasos']),
			$nivel('Nivel 2', '«Sanación del aura»', 'Curso intermedio en 14 lecciones: sanación aural para ti y para hacerla a los demás.', ['El aura', 'Práctica de anclaje (meditación)', 'Límites del aura (meditación)', 'Técnica de la rosa', 'Técnicas de separación y protección', 'El guía de sanación', 'Los chakras', 'Sanación del aura: claves, preparación, técnica, recuperación y práctica', 'Conclusiones y próximos pasos']),
			$nivel('Nivel 3', '«Aumenta tu vibración»', 'Gestiona tu aura y aprende a limpiar entidades.', ['Consciencia de tu aura y tu energía: controlar tus emociones y pensamientos', 'Frecuencia vibratoria: cómo subir tu vibración con tus acciones y conectar con la frecuencia del amor', 'Trabajo con entidades: por qué se crean, cómo se atraen y cómo disolverlas', 'Test de autoconocimiento: qué energía y qué entidades atraes']),
			$nivel('Nivel 4', '«Limpieza de casas»', 'Sanación del aura de tu casa y de tus espacios, y ritual de sal.', ['Trabajar en ti misma y ser consciente de tu energía: tu casa es la expansión de tu aura', 'Técnicas de protección de tu aura y de un espacio físico', 'Plano de tu casa o lugar de trabajo; proceso de sanación y limpieza', 'Ritual de sal: teoría y práctica']),
		]),
	]),
	lda_sec('lda-sec--blanco', [
		lda_head('Incluye', 'Cómo son los cursos'),
		lda_row([
			lda_card('Acceso de por vida', '<p>Repite el curso tantas veces como quieras y recibe las actualizaciones.</p>'),
			lda_card('Soporte a la alumna', '<p>Te acompaño durante el curso para resolver tus dudas.</p>'),
			lda_card('Certificado FSF', '<p>Con los cuatro niveles, certificado de la Foundation for Spiritual Freedom de California.</p>'),
		], 'lda-grid'),
	]),
	lda_sec('', [
		lda_head('Testimonios', 'Lo que dicen las alumnas y alumnos'),
		lda_row($testi_cortos, 'lda-grid'),
	]),
	lda_cta('¿Por dónde empiezo?', '<p>Escríbeme y te digo qué nivel encaja contigo y cuándo empieza el próximo grupo.</p>', 'Pedir información de los cursos', 'Hola Gemma, quiero información sobre los cursos de lectura de aura.'),
], 'Cursos de Lectura y Sanación del Aura · Gemma Vila', 'Cuatro cursos online para aprender a ver y sanar el aura: tú y tu aura, sanación del aura, aumenta tu vibración y limpieza de casas. Certificación FSF.');

// ---------------------------------------------------------------- REGALO
$out['regalo'] = lda_page('regalo', 'Masterclass gratuita: limpia tu aura', [
	lda_hero('Regalo de la escuela', 'Masterclass gratuita: limpia tu aura y libérate de entidades',
		'<p>Una masterclass online de lectura de aura de 40 minutos para aprender a expresar tu esencia verdadera y a ser tú.</p>'),
	lda_sec('lda-sec--blanco', [lda_box([lda_video('https://vimeo.com/385275520')], 'lda-wrap--estrecho')]),
	lda_sec('', [
		lda_box([
			lda_box([
				lda_eyebrow('¿Te pasa esto?'),
				lda_t('<ul><li>¿Te encuentras triste sin saber la razón?</li><li>¿Te sientes apenada, sin ganas de hacer nada, y no sabes por qué?</li><li>¿Hiperreaccionas y te dejas llevar por una emoción negativa?</li><li>¿Sientes que no valoras lo que tienes, o eres infeliz sin motivo?</li><li>¿Tienes gente que te quiere y aun así te sientes infeliz?</li><li>¿Crees que cargas con cosas que no son tuyas, o con conexiones negativas con entidades o personas?</li></ul>'),
			]),
			lda_box([
				lda_eyebrow('En esta masterclass'),
				lda_t('<ul><li>Aprenderás varios ejercicios para liberarte de energías que hay en tu aura y que no son tuyas.</li><li>Recobrarás tu propia energía limpiando tu aura.</li><li>Expresarás tu esencia verdadera y aprenderás a ser tú misma.</li></ul>'),
				lda_btn('Ver los cursos completos', $U('cursos')),
			], 'lda-card'),
		], 'lda-split', 'row'),
	]),
	lda_cta(),
], 'Masterclass gratuita de Lectura de Aura · Gemma Vila', 'Masterclass online gratuita de 40 minutos: limpia tu aura, libérate de energías que no son tuyas y aprende a expresar tu esencia verdadera.');

// ---------------------------------------------------------------- QUIÉN SOY
$out['quien-soy-yo'] = lda_page('quien-soy-yo', 'Quién soy yo', [
	lda_hero('Quién soy', 'Gemma Vila Pagès',
		'<p>Psicóloga clínica, terapeuta y formadora en lectura y sanación del aura con el método FSF de visión aural. Más de 25 años enseñando, compartiendo y aprendiendo en este camino.</p>',
		[['Escríbeme', $WA_CONSULTA]], $IMG['retrato']),
	lda_sec('lda-sec--blanco', [
		lda_box([
			lda_t('<p>Soy psicóloga clínica por la Universidad Ramon Llull de Barcelona. Al acabar la carrera me di cuenta de que me faltaban muchas cosas por aprender, y me surgió la oportunidad de ir a estudiar a la Foundation for Spiritual Freedom (FSF) en Berkeley, California.</p><p>Durante varios años aprendí todo lo que sé y que ahora enseño sobre el aura, los chakras, la visión aural y el crecimiento espiritual. Allí conocí a mi maestro, Michael C. Fikaris, mi maestro espiritual desde 1992, y desde entonces colaboro con la fundación a nivel internacional, en proyectos y cursos en España, Argentina, Uruguay, Portugal o Suecia. También he compartido camino con otras disciplinas como la filosofía budista zen, el yoga, la tradición del Camino Rojo y tradiciones indígenas.</p><p>Soy una persona buscadora y optimista, y me gusta aprender constantemente de la vida. Sé que la vida es mucho más de lo que nos enseñan, que tenemos un gran potencial y que, si se sabe enfocar bien, se pueden crear muchas cosas lindas.</p><p>Yo, como todos, también me he bloqueado alguna vez o he tenido épocas de desconexión de mí misma. Todo lo aprendido, las técnicas para ver el aura, las meditaciones y las sanaciones con el aura y los chakras, me ha servido para ver y transformar más fácilmente esas dificultades: tomando conciencia del bloqueo y cambiando la realidad para poder hacer algo al respecto.</p><p>Me siento muy agradecida de haber aprendido este conocimiento y por eso tengo ganas de compartirlo contigo. Es un conocimiento verdadero y simple, efectivo y directo, pero sobre todo transformador. Y cambiando la realidad, cambias tu vida.</p>', 'lda-lead'),
			lda_img($IMG['firma'], 'lda-firma'),
		], 'lda-wrap--estrecho'),
	]),
	lda_sec('', [
		lda_head('Mis vídeos', 'Escúchame hablar del aura'),
		lda_row([lda_video('https://www.youtube.com/watch?v=BBySp6Tht7E'), lda_video('https://www.youtube.com/watch?v=P6qQklh1CHk'), lda_video('https://www.youtube.com/watch?v=GhITX-88Rvc')], 'lda-grid'),
	]),
	lda_cta(),
], 'Quién soy · Gemma Vila Pagès, psicóloga y formadora en lectura de aura', 'Gemma Vila Pagès, psicóloga clínica formada en la Foundation for Spiritual Freedom de Berkeley. Más de 25 años enseñando lectura y sanación del aura.');

// ---------------------------------------------------------------- QUÉ ES
$out['que-es-la-lectura-del-aura'] = lda_page('que-es-la-lectura-del-aura', '¿Qué es el aura? ¿Qué es la lectura del aura?', [
	lda_hero('Lectura de aura', '¿Qué es el aura? ¿Qué es la lectura del aura?',
		'<p>Todo ser vivo está rodeado por un campo de energía electromagnética llamado aura. Aprender a verla y leerla es una habilidad natural que todos tenemos.</p>', [], $IMG['foto']),
	lda_sec('lda-sec--blanco', [
		lda_box([
			lda_box([lda_eyebrow('El aura'), lda_h('¿Qué es el aura?', 'h2'), lda_t('<p>Todo ser viviente está rodeado por un campo de energía electromagnética llamado aura. Este campo presenta diferentes frecuencias de vibración y refleja el estado de nuestro cuerpo, nuestra mente y nuestro ser espiritual.</p><p>Todos tenemos aura; incluso la Tierra la tiene: es la atmósfera. Todo ser vivo tiene aura.</p><p>El aura tiene diferentes colores. Cada color representa una longitud de onda y, según el color, su tonalidad y su claridad, se interpreta de una manera u otra. También importa dónde está situado el color: en cada chakra tiene un significado diferente.</p>')]),
			lda_box([lda_eyebrow('La lectura'), lda_h('¿Qué es la lectura de aura?', 'h2'), lda_t('<p>Todo el mundo puede ver el aura. <strong>La habilidad está en todos nosotros</strong>, pero no todo el mundo sabe interpretarla y leerla.</p><p><strong>La lectura de aura es una lectura al ser, al alma de la persona.</strong> Cuando leemos, miramos qué tipo de alma eres y por qué has venido aquí, cuál es tu verdadera misión y cómo conectar con ella. Nos centramos en qué bloqueos te impiden ser tú misma, para poder transformarlos y liberarte.</p>')]),
		], 'lda-split', 'row'),
	]),
	lda_sec('', [
		lda_head('El método', '¿Cómo se hace?', '<p>Con el <strong>método FSF</strong> se profundiza a través de la meditación y se alcanza la capacidad de ver los colores reales del aura y sentir los chakras. A través de imágenes, colores y sensaciones se va leyendo y explicando, y todo cobra sentido y se transforma en información muy valiosa para la persona. Nos enfocamos en el alma de la persona que leemos, hasta ver los colores de su aura e interpretarlos.</p>'),
		lda_box([lda_btn('Reservar una lectura', $WA_CONSULTA), lda_btn('Aprender a leer el aura', $U('cursos'), 'lda-btn--ghost')], 'lda-btns', 'row'),
	]),
	lda_cta(),
], '¿Qué es el aura y qué es la lectura del aura? · Lectura de Aura', 'El aura es el campo electromagnético que rodea a todo ser vivo. Descubre qué significan sus colores y en qué consiste una lectura de aura con el método FSF.');

// ---------------------------------------------------------------- QUÉ APORTA
$out['que-te-aporta-la-lectura-del-aura'] = lda_page('que-te-aporta-la-lectura-del-aura', '¿Qué te aporta la lectura del aura?', [
	lda_hero('Lectura de aura', '¿Qué te aporta la lectura del aura?', '<p>Una herramienta para ver tu verdad y aproximarte a tu interior de una forma clara.</p>'),
	lda_sec('lda-sec--blanco', [
		lda_row([
			lda_card('Ver tu verdad', '<p>Es un camino de autoconocimiento que limpia las interferencias que no te dejan reconocer tus dones y habilidades, y trae a tu conciencia tu verdad interior. Es un proceso transformador tanto para quien hace la lectura como para quien la recibe.</p>'),
			lda_card('Conectar con tu ser', '<p>Te dará más claridad mental sobre tu vida y, de una manera natural, irás viendo tus propias creencias y limitaciones y cómo afectan a tu alma.</p>'),
			lda_card('Expresarlo en tu vida', '<p>El propósito de la lectura es conectar con tu ser y enfocarnos en tu propia verdad, para que la puedas expresar conscientemente en tu vida.</p>'),
		], 'lda-grid'),
	]),
	lda_cta(),
], '¿Qué te aporta la lectura del aura? · Lectura de Aura', 'Para qué sirve una lectura de aura: ver tu verdad, conectar con tu ser, reconocer tus dones y liberarte de las creencias y bloqueos que te limitan.');

// ---------------------------------------------------------------- PARA QUIÉN
$out['para-quien'] = lda_page('para-quien', '¿Para quién?', [
	lda_hero('Lectura de aura', '¿Para quién es la lectura de aura?', '<p>Para buscadores de la verdad, la paz interior y la armonía. Y una herramienta fantástica para terapeutas.</p>'),
	lda_sec('lda-sec--blanco', [
		lda_row([
			lda_card('Particulares', '<p>Para deportistas y artistas, soñadores y buscadores, poetas o matemáticos, empresarias y amas de casa, madres o abuelos…</p><p>Si quieres ser más consciente de tu vida, estar más en contacto con tu poder personal, despertar las habilidades de tu alma y sacar tu mejor versión, el método FSF de lectura de aura y los cursos y talleres te acompañarán en tu camino de desarrollo espiritual.</p><p>Elige lo que mejor se adapta a ti: consulta privada o cursos.</p>', ['Ver consultas →', $U('lectura-del-aura')]),
			lda_card('Terapeutas', '<p>Si eres coach, terapeuta, psicóloga o masajista, o te dedicas a acompañar a personas con tu propio método, aquí tienes un buen complemento para mejorar la eficacia de tu trabajo.</p><p><strong>Podrás conectar con el cliente.</strong> Ante una persona bloqueada emocionalmente, la lectura del aura puede dar claridad a tu terapia. Y si como terapeuta has tenido momentos de bloqueo, descubrir nuevas herramientas puede abrirte puertas para potenciar tu trabajo.</p>', ['Ver formación →', $U('cursos')]),
		], 'lda-grid lda-grid--2'),
	]),
	lda_cta(),
], '¿Para quién es la lectura de aura? Particulares y terapeutas', 'La lectura de aura es para cualquier persona que quiera conocerse y despertar su potencial, y una herramienta muy valiosa para terapeutas, coaches y psicólogos.');

// ---------------------------------------------------------------- LECTURA DEL AURA (consultas)
$out['lectura-del-aura'] = lda_page('lectura-del-aura', 'Lectura del Aura', [
	lda_hero('Consulta personal', 'Lectura del aura: consulta privada con Gemma Vila',
		'<p>Para quien desee vivir una experiencia de autoconocimiento gratificante y sanadora. A través de la lectura se van viendo imágenes, colores y sensaciones que se interpretan y se traducen en habilidades, bloqueos e incluso experiencias pasadas.</p>',
		[['Reservar consulta', $WA_CONSULTA]], $IMG['foto']),
	lda_sec('lda-sec--blanco', [
		lda_box([
			lda_box([lda_eyebrow('Para qué sirve'), lda_h('Poner luz en lo que está bloqueado', 'h2'), lda_t('<p>Vas tomando conciencia de tus bloqueos, tus limitaciones y las conductas repetitivas que te producen estancamiento. La lectura permite poner luz y claridad en esos conflictos para avanzar en tu camino de vida.</p><p>Conectar con tu propio ser te da más claridad mental y una actitud neutral. Todo va cobrando sentido y se transforma en información sobre ti.</p>')]),
			lda_box([lda_eyebrow('Qué incluye cada sesión'), lda_t($sesion_incluye)], 'lda-card'),
		], 'lda-split', 'row'),
	]),
	lda_sec('', [
		lda_head('Tipos de lectura', 'Elige el enfoque de tu lectura'),
		lda_toggle($tipos_lectura),
	]),
	lda_sec('lda-sec--blanco', [
		lda_head('Formación', '¿Quieres aprender a leer el aura?', '<p>La formación es un método en el que se profundiza a través de la meditación hasta poder ver los colores del aura y los chakras. Todo el mundo puede ver el aura: es una habilidad natural, pero hay que entrenarla.</p>'),
		lda_box([lda_btn('Ver los cursos', $U('cursos')), lda_btn('Máster de Sanadores', $U('presentacion-curso-de-sanadores-del-aura'), 'lda-btn--ghost')], 'lda-btns', 'row'),
	]),
	lda_cta('Reserva tu lectura', '<p>Presencial o a distancia. Escríbeme y buscamos el momento.</p>', 'Reservar por WhatsApp', 'Hola Gemma, quiero reservar una consulta de lectura de aura.'),
], 'Lectura del Aura · Consulta privada con Gemma Vila', 'Consulta privada de lectura de aura, presencial u online: general, vidas pasadas, relaciones, prosperidad, camino espiritual, energía femenina y cambios.');

// ---------------------------------------------------------------- QUÉ TE OFREZCO
$colores = [
	'Rojo profundo' => 'realista, afianzado, activo, gran fuerza de voluntad, orientado a la supervivencia.',
	'Rojo' => 'activo, vigoroso, energético, vital, competitivo, apasionado.',
	'Naranja' => 'creativo y sanador; sanador nato o en pleno proceso de sanación propia. Emocional, empático.',
	'Amarillo' => 'poder personal; inteligente, optimista, comunicativo, analítico, lógico, detallista.',
	'Verde' => 'equilibrio entre cuerpo y alma; sociable, comunicativo, amante de la naturaleza, responsable, expansivo.',
	'Azul' => 'sensible, cariñoso, servicial, intuitivo, compasivo, pacífico; seguridad, creatividad y comunicación.',
	'Índigo' => 'calmado, intuitivo, emociones profundas, valores elevados, artístico.',
	'Violeta' => 'intuitivo, idealista, mágico, visionario, carismático, innovador.',
	'Lavanda' => 'visionario, místico, sensitivo, soñador.',
	'Blanco' => 'espiritual, trascendente; transformación y curación, cualidades etéreas.',
];
$col_html = '<ul>'; foreach ($colores as $c => $d) { $col_html .= '<li><strong>' . $c . ':</strong> ' . $d . '</li>'; } $col_html .= '</ul>';
$out['que-te-ofrezco'] = lda_page('que-te-ofrezco', '¿Qué te ofrezco?', [
	lda_hero('Lo que te ofrezco', '¿Qué te ofrezco?',
		'<p>He preparado una serie de propuestas para que puedas aprender a leer el aura sin ningún conocimiento previo ni aptitud especial: solo siendo tú, desde el corazón y con la tranquilidad de que lograrás tu paz y tu armonía.</p>',
		[['Escríbeme', $WA_CONSULTA]], $IMG['retrato']),
	lda_sec('lda-sec--blanco', [
		lda_head('Consultas', 'Consultas privadas', '<p>Cada consulta dura entre hora y media y dos horas. Puede ser presencial o a distancia, se puede grabar y hay espacio para preguntas. Puede girar en torno a cualquier tema: trabajo, pareja, familia, salud, espiritualidad, relaciones o prosperidad.</p>'),
		lda_row([
			lda_card('Lectura y sanación de aura', '<p>Se basa en un diálogo con tu alma, que tiene todas las respuestas de tu vida actual. Tras una pequeña meditación para conectar, se establece el diálogo y vamos leyendo. La sesión se graba y te la llevas: se convierte en una meditación guiada personal.</p>', ['Ver tipos de lectura →', $U('lectura-del-aura')]),
			lda_card('Lectura numerológica', '<p>El cuadro numerológico completo: estudio profundo de tu personalidad, tus habilidades y los bloqueos que puedes encontrar, a partir de tu fecha de nacimiento, tu nombre, tus metas, tu firma y tu destino. Incluye ciclos de vida y año numerológico.</p>', ['Reservar →', lda_wa('Hola Gemma, quiero reservar una lectura numerológica.')]),
			lda_card('Desprogramación y descodificación biológica', '<p>Ante situaciones difíciles generamos estrés que el cerebro proyecta en el cuerpo. La desprogramación deshace ese programa y reprograma una nueva emoción.</p>', ['Reservar →', lda_wa('Hola Gemma, quiero reservar una consulta de descodificación.')]),
			lda_card('Supersanación', '<p>Cuatro sesiones en un mes, aproximadamente, en los planos espiritual, emocional, mental y físico: programas y bloqueos aprendidos, cargas externas, entidades y también la columna vertebral. Para quien está colapsado y no sabe cómo avanzar.</p>', ['Reservar →', lda_wa('Hola Gemma, quiero información sobre la supersanación.')]),
		], 'lda-grid lda-grid--2'),
		lda_t('<p>Si crees que tu tema es muy especial, ya lo has probado todo o te sientes a la deriva, escríbeme, cuéntame tu situación y te ofrezco una valoración gratuita.</p>', 'lda-muted'),
	]),
	lda_sec('', [
		lda_head('Cursos y talleres', '¿Cómo aprender a ver el aura?', '<p>Todos podemos ver el aura y todos podemos aprender a leerla, solo que no somos conscientes de ello. No hace falta ningún poder especial: es una habilidad natural que hay que entrenar y desarrollar. No se necesita experiencia previa, solo estar abierta a descubrir lo que hay en tu interior.</p><p>Los objetivos: conocer el aura y los chakras, experimentar la relación entre tu ser y espíritu, tu mente y tus emociones, y entender que el aura es el espacio donde está la información de tu pasado y tu presente.</p>'),
		lda_box([lda_btn('Ver los cursos', $U('cursos')), lda_btn('Máster de Sanadores', $U('presentacion-curso-de-sanadores-del-aura'), 'lda-btn--ghost')], 'lda-btns', 'row'),
	]),
	lda_sec('lda-sec--blanco', [
		lda_box([
			lda_box([lda_img($IMG['foto'], 'lda-img--cuadrada')]),
			lda_box([
				lda_eyebrow('Fotografía del aura'),
				lda_h('¿Cómo se capta la fotografía del aura?', 'h2'),
				lda_t('<p>La estación de vídeo del aura es un sistema interactivo que recoge la biorrespuesta, la electricidad, el magnetismo, la energía y la temperatura de los meridianos reflejos de la mano, y crea una imagen en tiempo real del aura y de los chakras principales.</p><p>El aura es la parte no manifestada del cuerpo: se mueve y cambia según nuestro ser interior, y nos dice qué necesitamos para evolucionar. Los chakras redondos muestran un sistema de energía equilibrado; los que no lo son, un desequilibrio. El diagnóstico incluye la interpretación, para que puedas aplicar después la terapia que elijas.</p>'),
				lda_btn('Pedir mi foto del aura', lda_wa('Hola Gemma, quiero hacerme la fotografía del aura.'), 'lda-btn--small'),
			]),
		], 'lda-split', 'row'),
	]),
	lda_sec('', [
		lda_head('Colores del aura', '¿Qué significan los colores del aura?', '<p>Cada color tiene una frecuencia de vibración y se asocia a un chakra. Los colores dependen del estado físico, mental, emocional y espiritual. Ningún color es mejor ni peor que otro: lo importante es que estén equilibrados y luminosos.</p>'),
		lda_box([lda_t($col_html)], 'lda-card'),
	]),
	lda_cta(),
], '¿Qué te ofrezco? Consultas, cursos y fotografía del aura · Gemma Vila', 'Consultas de lectura y sanación de aura, numerología, descodificación y supersanación; cursos para aprender a ver el aura y fotografía del aura con interpretación.');

// ---------------------------------------------------------------- FOTOGRAFÍA
$out['fotografia-del-aura'] = lda_page('fotografia-del-aura', 'Fotografía del Aura', [
	lda_hero('Fotografía del aura', 'Así se ve un aura', '<p>Lo que dibujo en cada consulta de lectura de aura y lo que muestra la fotografía del aura.</p>', [['Pedir mi foto del aura', lda_wa('Hola Gemma, quiero hacerme la fotografía del aura.')]], $IMG['foto']),
	lda_sec('lda-sec--blanco', [
		lda_box([
			lda_t('<p>Estas imágenes son lo que dibujo cuando hago la consulta de lectura de aura. La rosa representa siempre el alma de la persona. El sol representa el ser supremo, o Dios, según la cultura. La lectura del aura es una lectura al ser, al alma, para ver cómo se relaciona y qué bloqueos tiene en el momento en que se hace la consulta.</p><p>En el dibujo se ven claramente las diferentes líneas del aura y sus colores. Cada persona tiene colores diferentes, porque cada una tiene experiencias de vida distintas que van quedando en el aura como información.</p>', 'lda-lead'),
			lda_box([lda_btn('Qué significan los colores', $U('que-te-ofrezco')), lda_btn('Reservar una lectura', $WA_CONSULTA, 'lda-btn--ghost')], 'lda-btns', 'row'),
		], 'lda-wrap--estrecho'),
	]),
	lda_cta(),
], 'Fotografía del Aura · Lectura de Aura con Gemma Vila', 'Qué muestra la fotografía del aura y los dibujos de una lectura de aura: las líneas, los colores y los chakras de cada persona.');

// ---------------------------------------------------------------- TESTIMONIOS
$out['testimonios'] = lda_page('testimonios', 'Testimonios', [
	lda_hero('Testimonios', 'Lo que cuentan quienes han pasado por la escuela', '<p>Alumnas, alumnos y consultantes de la Escuela de Lectura y Sanación de Aura de Gemma Vila.</p>'),
	lda_sec('lda-sec--blanco', [lda_row($testi_cards, 'lda-grid lda-grid--2')]),
	lda_cta(),
], 'Testimonios · Escuela de Lectura y Sanación de Aura Gemma Vila', 'Experiencias de alumnas, alumnos y consultantes con la lectura de aura, la meditación y los cursos de Gemma Vila.');

// ---------------------------------------------------------------- CONTACTO
$out['contacto'] = lda_page('contacto', 'Contacto', [
	lda_hero('Contacto', 'Hablemos', '<p>Escríbeme por WhatsApp o por correo y te respondo personalmente.</p>'),
	lda_sec('lda-sec--blanco', [
		lda_row([
			lda_card('WhatsApp', '<p>(+34) 622 082 930</p>', ['Escribir por WhatsApp →', lda_wa('Hola Gemma, quiero información.')]),
			lda_card('Correo', '<p><a href="mailto:info@gemmavilaaccion.es">info@gemmavilaaccion.es</a></p>'),
			lda_card('Dónde estoy', '<p>Centro Alegría, Mataró (Barcelona). Consultas presenciales y online.</p>'),
		], 'lda-grid'),
	]),
], 'Contacto · Lectura de Aura con Gemma Vila', 'Contacta con Gemma Vila para reservar una consulta de lectura de aura o pedir información de los cursos y del Máster de Sanadores.');

return $out;
