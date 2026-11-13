<?php
// Constructor de páginas Elementor (widgets clásicos) para lecturadeaura.
// Uso: include este fichero desde execute-php en la web nueva.

if (!defined('LDA_WA')) {
	define('LDA_WA', 'https://wa.me/34622082930?text=');
}

function lda_id() { return substr(md5(uniqid('', true) . mt_rand()), 0, 7); }

function lda_wa($msg) { return LDA_WA . rawurlencode($msg); }

/** Sección a ancho completo con contenedor interior centrado. */
function lda_sec($cls, array $children, $inner_cls = 'lda-wrap') {
	return [
		'id' => lda_id(), 'elType' => 'container', 'isInner' => false,
		'settings' => ['content_width' => 'full', 'flex_direction' => 'column', 'css_classes' => 'lda-sec ' . $cls,
			'padding' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true]],
		'elements' => [lda_box($children, $inner_cls)],
	];
}

function lda_box(array $children, $cls = '', $dir = 'column') {
	return [
		'id' => lda_id(), 'elType' => 'container', 'isInner' => true,
		'settings' => ['content_width' => 'full', 'flex_direction' => $dir, 'css_classes' => $cls,
			'padding' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
			'flex_gap' => ['unit' => 'px', 'size' => 0, 'column' => '0', 'row' => '0', 'isLinked' => true]],
		'elements' => array_values(array_filter($children)),
	];
}

function lda_row(array $children, $cls = 'lda-grid') {
	$b = lda_box($children, $cls, 'row');
	$b['settings']['flex_wrap'] = 'wrap';
	return $b;
}

function lda_w($type, array $settings, $cls = '') {
	if ($cls) { $settings['_css_classes'] = $cls; }
	return ['id' => lda_id(), 'elType' => 'widget', 'widgetType' => $type, 'settings' => $settings, 'elements' => []];
}

function lda_h($text, $tag = 'h2', $cls = '') { return lda_w('heading', ['title' => $text, 'header_size' => $tag], trim('lda-h lda-' . $tag . ' ' . $cls)); }
function lda_eyebrow($text) { return lda_w('heading', ['title' => $text, 'header_size' => 'p'], 'lda-eyebrow'); }
function lda_t($html, $cls = '') { return lda_w('text-editor', ['editor' => $html], trim('lda-text ' . $cls)); }

function lda_btn($text, $url, $cls = '', $ext = null) {
	if ($ext === null) { $ext = (strpos($url, 'http') === 0 && strpos($url, home_url()) !== 0); }
	return lda_w('button', ['text' => $text, 'link' => ['url' => $url, 'is_external' => $ext ? 'on' : '', 'nofollow' => '', 'custom_attributes' => '']], trim('lda-btn ' . $cls));
}

function lda_img($id, $cls = '') {
	return lda_w('image', ['image' => ['id' => $id, 'url' => wp_get_attachment_url($id), 'alt' => get_post_meta($id, '_wp_attachment_image_alt', true)], 'image_size' => 'large'], trim('lda-img ' . $cls));
}

function lda_video($url) {
	$s = ['lightbox' => '', 'aspect_ratio' => '169'];
	if (strpos($url, 'vimeo') !== false) { $s['video_type'] = 'vimeo'; $s['vimeo_url'] = $url; }
	else { $s['video_type'] = 'youtube'; $s['youtube_url'] = $url; $s['yt_privacy'] = 'yes'; }
	return lda_w('video', $s, 'lda-video');
}

function lda_toggle(array $items) {
	$tabs = [];
	foreach ($items as $it) { $tabs[] = ['_id' => lda_id(), 'tab_title' => $it[0], 'tab_content' => $it[1]]; }
	return lda_w('toggle', ['tabs' => $tabs], 'lda-toggle');
}

/** Tarjeta: título + texto (+ botón opcional). */
function lda_card($title, $html, $btn = null, $eyebrow = null) {
	$c = [];
	if ($eyebrow) { $c[] = lda_eyebrow($eyebrow); }
	$c[] = lda_h($title, 'h3');
	if ($html) { $c[] = lda_t($html); }
	if ($btn) { $c[] = lda_btn($btn[0], $btn[1], 'lda-btn--small'); }
	return lda_box($c, 'lda-card');
}

function lda_quote($html, $name) {
	return lda_box([lda_t($html, 'lda-quote-text'), lda_t('<p>' . esc_html($name) . '</p>', 'lda-quote-name')], 'lda-card lda-quote');
}

/** Cabecera de sección: antetítulo, título y entradilla. */
function lda_head($eyebrow, $title, $intro = '', $tag = 'h2') {
	$c = [];
	if ($eyebrow) { $c[] = lda_eyebrow($eyebrow); }
	$c[] = lda_h($title, $tag);
	if ($intro) { $c[] = lda_t($intro, 'lda-lead'); }
	return lda_box($c, 'lda-head');
}

/** Cabecera de página (hero) a dos columnas con imagen opcional. */
function lda_hero($eyebrow, $title, $intro, array $btns = [], $img = null) {
	$txt = [lda_eyebrow($eyebrow), lda_h($title, 'h1'), lda_t($intro, 'lda-lead')];
	if ($btns) {
		$bb = [];
		foreach ($btns as $i => $b) { $bb[] = lda_btn($b[0], $b[1], $i ? 'lda-btn--ghost' : ''); }
		$txt[] = lda_box($bb, 'lda-btns', 'row');
	}
	$cols = [lda_box($txt, 'lda-hero-text')];
	if ($img) { $cols[] = lda_box([lda_img($img, 'lda-img--portrait')], 'lda-hero-img'); }
	return lda_sec('lda-hero' . ($img ? '' : ' lda-hero--solo'), [lda_box($cols, 'lda-hero-cols', 'row')]);
}

/** Bloque final de llamada a la acción (degradado dorado como en gemmavilaaccion.es). */
function lda_cta($title = '¿Hablamos?', $text = '<p>Cuéntame qué necesitas y te respondo personalmente.</p>', $btn = 'Escríbeme por WhatsApp', $msg = 'Hola Gemma, quiero información sobre la lectura de aura.') {
	return lda_sec('lda-sec--oro lda-center', [lda_h($title, 'h2'), lda_t($text, 'lda-lead'), lda_btn($btn, lda_wa($msg), 'lda-btn--dark')]);
}

/** Crea o actualiza una página por slug con datos Elementor y SEO de Yoast. */
function lda_page($slug, $title, array $sections, $seo_title = '', $seo_desc = '', $parent = 0) {
	$p = get_page_by_path($slug, OBJECT, 'page');
	$arr = ['post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug, 'post_parent' => $parent, 'post_content' => ''];
	if ($p) { $arr['ID'] = $p->ID; $id = wp_update_post($arr); } else { $id = wp_insert_post($arr); }
	update_post_meta($id, '_wp_page_template', 'elementor_header_footer');
	update_post_meta($id, '_elementor_edit_mode', 'builder');
	update_post_meta($id, '_elementor_template_type', 'wp-page');
	update_post_meta($id, '_elementor_version', ELEMENTOR_VERSION);
	update_post_meta($id, '_elementor_data', wp_slash(wp_json_encode($sections)));
	delete_post_meta($id, '_elementor_css');
	if ($seo_title) { update_post_meta($id, '_yoast_wpseo_title', $seo_title); }
	if ($seo_desc) { update_post_meta($id, '_yoast_wpseo_metadesc', $seo_desc); }
	// Texto plano en post_content para buscadores internos y por si se desactiva Elementor.
	$doc = \Elementor\Plugin::$instance->documents->get($id, false);
	if ($doc && is_callable([$doc, 'save_plain_text'])) { $doc->save_plain_text(); }
	return $id;
}
