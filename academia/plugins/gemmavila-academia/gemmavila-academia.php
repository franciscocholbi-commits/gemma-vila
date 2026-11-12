<?php
/**
 * Plugin Name: Gemma Vila Academia
 * Description: Funciones propias de la academia: menú según tipo de usuario (público, alumno, gestión) y ajustes de Mi cuenta. Aquí irá también la integración con Hermes.
 * Version: 0.12.0
 * Author: Francisco Cholbi
 * Requires Plugins: woocommerce
 */
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/includes/formaciones.php';
require_once __DIR__ . '/includes/plazas.php';
require_once __DIR__ . '/includes/plazas-visibles.php';
require_once __DIR__ . '/includes/origen.php';
require_once __DIR__ . '/includes/datos.php';
require_once __DIR__ . '/includes/transferencia.php';
// require_once __DIR__ . '/includes/nif.php'; // DESACTIVADO 11/11: diagnóstico del 403 en el pago.

/**
 * La cabecera usa siempre el menú "academia-publico".
 * Si el usuario es gestor (manage_woocommerce) o alumno, se sustituye por su menú.
 */
add_filter( 'wp_nav_menu_args', function ( $args ) {
	$menu = $args['menu'] ?? '';
	if ( $menu instanceof WP_Term ) {
		$slug = $menu->slug;
	} elseif ( is_numeric( $menu ) ) {
		$term = get_term( (int) $menu, 'nav_menu' );
		$slug = $term && ! is_wp_error( $term ) ? $term->slug : '';
	} else {
		$slug = (string) $menu;
	}
	if ( 'academia-publico' !== $slug ) {
		return $args;
	}
	if ( current_user_can( 'manage_woocommerce' ) ) {
		$args['menu'] = 'academia-gestion';
	} elseif ( is_user_logged_in() ) {
		$args['menu'] = 'academia-alumno';
	}
	return $args;
} );

/** Elementos con la clase gv-logout cierran sesión de verdad (con nonce) y vuelven a la home. */
add_filter( 'wp_nav_menu_objects', function ( $items ) {
	foreach ( $items as $item ) {
		if ( in_array( 'gv-logout', (array) $item->classes, true ) && function_exists( 'wc_logout_url' ) ) {
			$item->url = wc_logout_url( home_url( '/' ) );
		}
	}
	return $items;
} );

/** Mi cuenta: sin "Descargas" (no vendemos descargables). */
add_filter( 'woocommerce_account_menu_items', function ( $items ) {
	unset( $items['downloads'] );
	return $items;
} );
/** Home sin cursos publicados: mensaje en lugar de un hueco vacío. */
add_action( 'woocommerce_shortcode_products_loop_no_results', function () {
	echo '<p class="gv-sin-cursos" style="text-align:center">' . esc_html__( 'Muy pronto publicaremos nuevas formaciones.', 'gemmavila-academia' ) . '</p>';
} );

/**
 * COMPRA DIRECTA (sin carrito a la vista).
 * Aquí se compra un curso cada vez: al pulsar "Inscribirme" se vacía el carrito,
 * se añade solo ese curso y se va directo al pago. El carrito existe por dentro,
 * pero el alumno no lo ve nunca.
 */
add_filter( 'woocommerce_add_to_cart_validation', function ( $passed ) {
	if ( $passed && WC()->cart && ! WC()->cart->is_empty() ) {
		WC()->cart->empty_cart();
	}
	return $passed;
}, 1 );

// Después de añadir, directo al pago.
add_filter( 'woocommerce_add_to_cart_redirect', function () {
	return wc_get_checkout_url();
} );

// Sin el aviso "se ha añadido a tu carrito / Ver carrito".
add_filter( 'wc_add_to_cart_message_html', '__return_empty_string' );

// Botones: "Inscribirme" en lugar de "Añadir al carrito".
add_filter( 'woocommerce_product_add_to_cart_text', function ( $text, $product ) {
	return ( $product && $product->is_purchasable() && $product->is_in_stock() ) ? __( 'Inscribirme', 'gemmavila-academia' ) : $text;
}, 10, 2 );
add_filter( 'woocommerce_product_single_add_to_cart_text', function () {
	return __( 'Inscribirme', 'gemmavila-academia' );
} );

// La página del carrito no se usa: con curso, al pago; sin curso, a la home.
add_action( 'template_redirect', function () {
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		wp_safe_redirect( WC()->cart && ! WC()->cart->is_empty() ? wc_get_checkout_url() : home_url( '/' ) );
		exit;
	}
} );

// En el pago, sin enlaces de "volver al carrito" ni de editar cantidades.
add_filter( 'woocommerce_checkout_cart_item_quantity', '__return_empty_string' );

// En el pago, sin el recuadro "Notas del pedido" (texto de tienda online: aquí no hay entregas).
add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );

/**
 * Enlaces antiguos de la web principal (?add-to-cart=<ID antiguo>): se traducen al producto equivalente de la academia.
 * El mapa (ID antiguo => ID nuevo) está en la opción gv_mapa_productos_web_principal.
 */
add_action( 'wp_loaded', function () {
	if ( empty( $_REQUEST['add-to-cart'] ) || ! is_numeric( $_REQUEST['add-to-cart'] ) ) {
		return;
	}
	$old = (int) $_REQUEST['add-to-cart'];
	$map = (array) get_option( 'gv_mapa_productos_web_principal', array() );
	if ( isset( $map[ $old ] ) && 'product' !== get_post_type( $old ) ) {
		$_REQUEST['add-to-cart'] = (string) $map[ $old ];
		$_GET['add-to-cart']     = (string) $map[ $old ];
	}
}, 1 );
/**
 * SEO de la academia: solo la home (catálogo público) se indexa.
 * El resto (fichas, pago, Mi cuenta, legales, área de alumnos…) queda fuera de Google.
 * La captación SEO se hace en gemmavilaaccion.es, tecnicaestructural.es y lecturadeaura.es.
 */
add_filter( 'wp_robots', function ( $robots ) {
	if ( is_front_page() ) {
		$robots['index']  = true;
		$robots['follow'] = true;
		unset( $robots['noindex'], $robots['nofollow'] );
		return $robots;
	}
	$robots['noindex'] = true;
	$robots['follow']  = true;
	unset( $robots['index'] );
	return $robots;
}, 99 );

// El sitemap de la academia solo incluye la home.
add_filter( 'wp_sitemaps_post_types', function ( $types ) {
	return array_intersect_key( $types, array( 'page' => true ) );
} );
add_filter( 'wp_sitemaps_taxonomies', '__return_empty_array' );
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}, 10, 2 );
add_filter( 'wp_sitemaps_posts_pre_url_list', function ( $list, $post_type ) {
	return 'page' === $post_type ? array( array( 'loc' => home_url( '/' ) ) ) : $list;
}, 10, 2 );
add_filter( 'wp_sitemaps_posts_pre_max_num_pages', function ( $n, $post_type ) {
	return 'page' === $post_type ? 1 : $n;
}, 10, 2 );

/*
 * Cursos con fecha (v0.4.0)
 * Cada curso guarda su fecha de inicio en la meta _gv_fecha_inicio (AAAA-MM-DD).
 * - Desde ese día ya no se puede comprar (aunque alguien tenga el enlace directo).
 * - Una tarea diaria lo oculta del catálogo y de la búsqueda (no se borra: los pedidos siguen enlazados).
 */
function gv_curso_ha_empezado( $product_id ) {
	$fecha = get_post_meta( $product_id, '_gv_fecha_inicio', true );
	if ( ! $fecha ) {
		return false;
	}
	return current_time( 'Y-m-d' ) >= $fecha;
}

add_filter( 'woocommerce_is_purchasable', function ( $purchasable, $product ) {
	if ( $purchasable && gv_curso_ha_empezado( $product->get_id() ) ) {
		return false;
	}
	return $purchasable;
}, 20, 2 );

function gv_ocultar_cursos_pasados() {
	$ids = get_posts( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array( array(
			'key'     => '_gv_fecha_inicio',
			'value'   => current_time( 'Y-m-d' ),
			'compare' => '<=',
			'type'    => 'CHAR',
		) ),
	) );
	$ocultados = array();
	foreach ( $ids as $id ) {
		$p = wc_get_product( $id );
		if ( $p && 'hidden' !== $p->get_catalog_visibility() ) {
			$p->set_catalog_visibility( 'hidden' );
			$p->save();
			update_post_meta( $id, '_gv_ocultado_por_fecha', current_time( 'mysql' ) );
			$ocultados[] = $id;
		}
	}
	return $ocultados;
}
add_action( 'gv_ocultar_cursos_pasados', 'gv_ocultar_cursos_pasados' );

// Programar la tarea diaria a las 00:05 hora de Madrid.
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'gv_ocultar_cursos_pasados' ) ) {
		$manana = new DateTime( 'tomorrow 00:05', wp_timezone() );
		wp_schedule_event( $manana->getTimestamp(), 'daily', 'gv_ocultar_cursos_pasados' );
	}
} );
register_deactivation_hook( __FILE__, function () {
	wp_clear_scheduled_hook( 'gv_ocultar_cursos_pasados' );
} );
