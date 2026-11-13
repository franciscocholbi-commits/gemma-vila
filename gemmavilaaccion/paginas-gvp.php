<?php
// Páginas de gemmavilaaccion.es creadas a partir del contenido de gemmavilapages.com (13/11/2026).
// Requiere lecturadeaura/lib.php (funciones lda_*). Devuelve slug => id.

$IMG = ['retrato' => 171, 'retrato2' => 170, 'charla' => 173, 'grupo' => 158, 'practica' => 172, 'manos' => 168];
$W = fn($m) => lda_wa($m);
$out = [];

// Los borradores vacíos 12-16 no tienen slug: se lo ponemos para que lda_page los encuentre.
foreach ([15 => 'sesiones', 13 => 'quien-soy', 14 => 'contacto'] as $id => $slug) {
	if (get_post_status($id) && !get_post($id)->post_name) { wp_update_post(['ID' => $id, 'post_name' => $slug]); }
}

$sesion = function ($titulo, $texto, $detalle, $msg, $eyebrow = null) use ($W) {
	return lda_card($titulo, $texto . ($detalle ? '<p class="lda-detalle"><strong>' . $detalle . '</strong></p>' : ''), ['Reservar →', $W($msg)], $eyebrow);
};

// ---------------------------------------------------------------- SESIONES
$out['sesiones'] = lda_page('sesiones', 'Sesiones', [
	lda_hero('Sesiones individuales · Gemma Vila', 'Sesiones para sanar en todos los planos',
		'<p>Consultas individuales, presenciales en Mataró u online, para trabajar lo que hoy te bloquea: en el plano espiritual, emocional, mental y físico. Si no sabes cuál necesitas, escríbeme, cuéntame tu situación y te aconsejo.</p>',
		[['Reservar una sesión', $W('Hola Gemma, quiero reservar una sesión.')], ['Quién soy', home_url('/quien-soy/')]], $IMG['retrato']),
	lda_sec('lda-sec--blanco', [
		lda_head('Para ti', 'Sesiones personales'),
		lda_row([
			$sesion('Lectura de aura y consulta privada', '<p>Se basa en un diálogo con tu alma, con tu esencia, que tiene las respuestas y los bloqueos de tu vida actual. En el espacio al que accedemos a través de la lectura no hay presente, pasado ni futuro. Se puede tratar cualquier tema: general, trabajo, pareja, familia, salud, camino espiritual, relaciones, abundancia y prosperidad.</p><p>La lectura se graba y al final recibes una sanación completa y personal.</p>', 'Sanación incluida · espacio para preguntas · 60 a 80 minutos', 'Hola Gemma, quiero reservar una lectura de aura.'),
			$sesion('Estudio numerológico personal', '<p>El mapa vibracional de tu fecha de nacimiento y tu nombre: tu destino, tu firma y tu tendencia anual. Un estudio profundo de tu personalidad, tus habilidades y los bloqueos que puedes encontrar, para guiarte hacia tu propósito.</p>', '120 minutos', 'Hola Gemma, quiero reservar un estudio numerológico.'),
			$sesion('Técnica Estructural', '<p>Trabajo energético en el plano espiritual, emocional, mental y físico, una sesión por semana durante un mes. Se trabajan la columna vertebral y la recolocación de vértebras, los brazos, las piernas y la cadera; y en la misma sesión, lo emocional: programas familiares heredados, bloqueos, traumas, cargas, pactos y fidelidades.</p><p>Se puede hacer a distancia: necesito tu nombre completo, tu fecha de nacimiento y dónde vives. Cada semana te envío por WhatsApp el resumen del trabajo realizado.</p>', '4 sesiones en un mes', 'Hola Gemma, quiero empezar sesiones de Técnica Estructural.'),
			$sesion('Supersanación', '<p>Una sanación muy completa en todos los planos: desde el alma y la liberación de amarres y entidades, los programas y bloqueos aprendidos y las cargas externas, hasta la columna vertebral y el sistema nervioso. Vas recobrando la energía y tu objetivo de vida.</p><p>Recomendada si te sientes colapsada y no sabes cómo avanzar.</p>', 'Lectura del aura + sanación aural + Técnica Estructural', 'Hola Gemma, quiero información sobre la supersanación.'),
			$sesion('Trabajo con el árbol familiar', '<p>Todas las familias tienen programas, creencias y cargas. En esta sesión desprogramamos los patrones familiares que te limitan, sanamos creencias y acciones de tu árbol y programamos una nueva información y una nueva realidad. Tomas conciencia de tu origen familiar y gestionas tus emociones para vivir con más salud y plenitud.</p>', 'Una sesión', 'Hola Gemma, quiero reservar una sesión de árbol familiar.'),
			$sesion('Psicología holística', '<p>Somos seres espirituales viviendo una experiencia humana. En esta consulta aprendemos a gestionar emociones y pensamientos para liberar lo que no somos (tristeza, depresión, cargas, miedos, culpas y traumas) y expresar quién somos.</p>', '', 'Hola Gemma, quiero reservar una consulta de psicología holística.'),
		], 'lda-grid lda-grid--2'),
	]),
	lda_sec('', [
		lda_head('Para tus espacios', 'Casas, locales y negocios'),
		lda_row([
			$sesion('Casas y locales: limpieza, venta y alquiler', '<p>Muevo la energía para que alquiles o vendas tu casa o tu local. Incluye la limpieza energética del espacio a nivel espiritual, emocional, estructural y mental, eliminando los programas, las creencias, las cargas externas y las entidades que encontramos. El resultado es un ambiente armónico y en paz.</p>', '4 sesiones en un mes aproximadamente', 'Hola Gemma, quiero información sobre la limpieza energética de una casa o local.'),
			$sesion('Apertura de negocio y éxito empresarial', '<p>Para empezar de la mejor manera un proyecto, un negocio o un comercio. Incluye la limpieza energética del lugar y el trabajo a nivel espiritual, mental, emocional y estructural. También podemos buscar el mejor día para firmar acuerdos y el nombre de empresa cuya vibración atraiga clientes y beneficios.</p>', 'Pack de 4 sesiones', 'Hola Gemma, quiero información sobre la apertura de negocio.'),
		], 'lda-grid lda-grid--2'),
	]),
	lda_sec('lda-sec--blanco', [
		lda_box([
			lda_box([lda_img($IMG['practica'], 'lda-img--portrait')]),
			lda_box([
				lda_eyebrow('Cómo funcionan'),
				lda_h('Una sesión pensada para ti', 'h2'),
				lda_t('<ul><li>Presenciales en el Centro Alegría de Mataró u online.</li><li>Puedes grabar la sesión y hacer todas las preguntas que necesites.</li><li>Después hago un seguimiento, según lo necesites, para asegurarnos de que lo integras en tu día a día.</li><li>Según el bloqueo que tengas, te aconsejo qué sesión necesitas en este momento.</li></ul><p>Si crees que tu caso es muy especial, que ya lo has probado todo o que estás un poco a la deriva, escríbeme y cuéntame tu situación.</p>'),
				lda_btn('Escribirme por WhatsApp', $W('Hola Gemma, quiero contarte mi situación.')),
			]),
		], 'lda-split', 'row'),
	]),
	lda_cta('¿Empezamos?', '<p>Cuéntame qué te pasa y te digo qué sesión te conviene.</p>', 'Reservar por WhatsApp', 'Hola Gemma, quiero reservar una sesión.'),
], 'Sesiones con Gemma Vila · Lectura de aura, Técnica Estructural y numerología', 'Sesiones individuales en Mataró u online: lectura de aura, Técnica Estructural, numerología, supersanación, árbol familiar, psicología holística y limpieza de espacios.');

// ---------------------------------------------------------------- QUIÉN SOY
$out['quien-soy'] = lda_page('quien-soy', 'Quién soy', [
	lda_hero('Quién soy', 'Gemma Vila Pagès',
		'<p>Psicóloga clínica, terapeuta holística y formadora. Más de 30 años acompañando procesos de sanación en el cuerpo, la mente y el espíritu.</p>',
		[['Reservar una sesión', $W('Hola Gemma, quiero reservar una sesión.')]], $IMG['retrato2']),
	lda_sec('lda-sec--blanco', [
		lda_box([
			lda_t('<p>Soy licenciada en Psicología Clínica por la Universidad Ramon Llull de Barcelona, con un máster en Mediación y Resolución de Conflictos Familiares y Comunitarios.</p><p>Desde 1992 soy alumna de Michael C. Fikaris, mi maestro espiritual, y de la Foundation for Spiritual Freedom de Berkeley (California). Allí aprendí a conectar con mi poder personal, a comprender los sueños y a desarrollar herramientas para ponerlos en práctica: enseñanzas que nacen de la búsqueda de la verdad interior, la autorrealización y la felicidad. He colaborado con él en proyectos y cursos por todo el mundo: España, Argentina, Uruguay, Portugal, Suecia…</p><p>A lo largo de estos años he integrado otras disciplinas, como la filosofía budista zen, el yoga, la tradición del Camino Rojo y las tradiciones indígenas, junto con la lectura del aura, la Técnica Estructural y la numerología. Todo ello ha dejado en mí una enseñanza y una huella muy amorosa que quiero compartir contigo.</p><p>Hoy acompaño a personas en sesiones individuales y formo a nuevas terapeutas en lectura y sanación del aura y en Técnica Estructural.</p>', 'lda-lead'),
		], 'lda-wrap--estrecho'),
	]),
	lda_sec('', [
		lda_head('Lo que dicen de mí', 'Testimonios'),
		lda_row([
			lda_quote('<p>Esa lectura fue fantástica: no solo me dio la respuesta que estaba buscando, también me mostró virtudes que no estaba desarrollando y me abrió el camino.</p>', 'Belén Leal'),
			lda_quote('<p>Mi experiencia con la lectura del aura fue tan transformadora que automáticamente decidí tomar el curso para incorporarla a mi vida.</p>', 'Belén Belcastro'),
			lda_quote('<p>Comprendí con el paso del tiempo cuán movilizadora había resultado la experiencia, tanto en lo que uno pretende entender como en lo sanadora que fue en el plano inconsciente.</p>', 'Luis Moulin'),
		], 'lda-grid'),
	]),
	lda_cta(),
], 'Quién soy · Gemma Vila Pagès, psicóloga clínica y terapeuta holística', 'Gemma Vila Pagès, psicóloga clínica por la Universidad Ramon Llull, alumna de Michael C. Fikaris y formadora en lectura de aura y Técnica Estructural.');

// ---------------------------------------------------------------- CONTACTO
$out['contacto'] = lda_page('contacto', 'Contacto', [
	lda_hero('Contacto', 'Estaré encantada de saber más de ti', '<p>Escríbeme por WhatsApp o por correo y te respondo personalmente.</p>'),
	lda_sec('lda-sec--blanco', [
		lda_row([
			lda_card('WhatsApp', '<p>(+34) 622 082 930</p>', ['Escribir por WhatsApp →', $W('Hola Gemma, quiero información.')]),
			lda_card('Correo', '<p><a href="mailto:info@gemmavilaaccion.es">info@gemmavilaaccion.es</a></p>'),
			lda_card('Dónde estoy', '<p>Centro Alegría, Mataró (Barcelona). Sesiones presenciales y online.</p>'),
		], 'lda-grid'),
	]),
], 'Contacto · Gemma Vila', 'Contacta con Gemma Vila por WhatsApp o por correo para reservar una sesión o pedir información de las formaciones.');

return $out;
