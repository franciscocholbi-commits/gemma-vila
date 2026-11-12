<?php
/**
 * ORIGEN DE CADA VENTA (v0.10.0)
 *
 * Para saber qué landing, web o campaña trae cada venta, con o sin cookies aceptadas.
 *
 * Cómo funciona:
 * - Los botones de compra de las landings llevan una marca en el enlace:
 *   https://academia.gemmavilaaccion.es/finalizar-compra/?add-to-cart=128&origen=m1-online-nov26
 *   (opcionalmente también utm_source, utm_medium, utm_campaign, utm_content).
 * - Al entrar en la academia con ese enlace, la marca se guarda en la sesión de compra
 *   (la misma que ya usa el carrito: no añade cookies nuevas).
 * - Al crear el pedido se copia al pedido: metas _gv_origen, _gv_origen_web y _gv_utm_*.
 * - Se ve en el listado de pedidos (columna "Origen"), dentro de cada pedido
 *   y en el correo de "Nuevo pedido" que recibe el equipo.
 *
 * Si un enlace no lleva marca, se guarda al menos la web de la que venía la persona
 * (el navegador solo envía el dominio, no la página exacta).
 */
defined( 'ABSPATH' ) || exit;

const GV_ORIGEN_SESION = 'gv_origen';
const GV_ORIGEN_UTM    = array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content' );

/** Limpia un valor de marca: minúsculas, sin espacios ni caracteres raros, máx. 80. */
function gv_origen_limpiar( $valor ) {
	$valor = sanitize_title( wp_unslash( (string) $valor ) );
	return substr( $valor, 0, 80 );
}

/**
 * Captura la marca al entrar en la academia.
 * Prioridad 2: después de traducir IDs antiguos (1) y antes de que WooCommerce añada el curso (20).
 */
add_action( 'wp_loaded', function () {
	if ( is_admin() || wp_doing_ajax() || ! function_exists( 'WC' ) || ! WC()->session ) {
		return;
	}

	$datos = array();

	if ( ! empty( $_GET['origen'] ) ) {
		$datos['origen'] = gv_origen_limpiar( $_GET['origen'] );
	}
	foreach ( GV_ORIGEN_UTM as $clave ) {
		if ( ! empty( $_GET[ $clave ] ) ) {
			$datos[ $clave ] = gv_origen_limpiar( $_GET[ $clave ] );
		}
	}

	// Web de procedencia (solo si viene de fuera de la academia).
	$referer = isset( $_SERVER['HTTP_REFERER'] ) ? (string) $_SERVER['HTTP_REFERER'] : '';
	$host    = $referer ? (string) wp_parse_url( $referer, PHP_URL_HOST ) : '';
	$propio  = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	if ( $host && $host !== $propio ) {
		$datos['web'] = substr( preg_replace( '/^www\./', '', strtolower( $host ) ), 0, 80 );
	}

	// Solo guardamos cuando la persona llega con algo nuevo (un enlace de compra o una marca).
	$es_entrada = ! empty( $_REQUEST['add-to-cart'] ) || isset( $datos['origen'] ) || isset( $datos['utm_source'] );
	if ( ! $es_entrada || empty( $datos ) ) {
		return;
	}

	if ( ! WC()->session->has_session() ) {
		WC()->session->set_customer_session_cookie( true );
	}
	$datos['fecha'] = current_time( 'mysql' );
	WC()->session->set( GV_ORIGEN_SESION, $datos );
}, 2 );

/** Copia la marca de la sesión al pedido. */
function gv_origen_guardar_en_pedido( $order ) {
	if ( ! $order instanceof WC_Order || ! function_exists( 'WC' ) || ! WC()->session ) {
		return;
	}
	$datos = WC()->session->get( GV_ORIGEN_SESION );
	if ( empty( $datos ) || ! is_array( $datos ) ) {
		return;
	}
	if ( ! empty( $datos['origen'] ) ) {
		$order->update_meta_data( '_gv_origen', $datos['origen'] );
	}
	if ( ! empty( $datos['web'] ) ) {
		$order->update_meta_data( '_gv_origen_web', $datos['web'] );
	}
	foreach ( GV_ORIGEN_UTM as $clave ) {
		if ( ! empty( $datos[ $clave ] ) ) {
			$order->update_meta_data( '_gv_' . $clave, $datos[ $clave ] );
		}
	}
}
// Pago clásico (el que usa la academia) y pago por bloques, por si algún día se cambia.
add_action( 'woocommerce_checkout_create_order', 'gv_origen_guardar_en_pedido', 20 );
add_action( 'woocommerce_store_api_checkout_update_order_from_request', 'gv_origen_guardar_en_pedido', 20 );

// Una vez creado el pedido, la marca se borra de la sesión para no contaminar la siguiente compra.
add_action( 'woocommerce_checkout_order_created', function () {
	if ( function_exists( 'WC' ) && WC()->session ) {
		WC()->session->set( GV_ORIGEN_SESION, null );
	}
} );

/** Texto corto del origen de un pedido, para listados y correos. */
function gv_origen_texto( $order ) {
	if ( ! $order instanceof WC_Order ) {
		return '';
	}
	$partes = array();
	if ( $o = $order->get_meta( '_gv_origen' ) ) {
		$partes[] = $o;
	}
	$utm = array_filter( array( $order->get_meta( '_gv_utm_source' ), $order->get_meta( '_gv_utm_medium' ), $order->get_meta( '_gv_utm_campaign' ) ) );
	if ( $utm ) {
		$partes[] = implode( ' / ', $utm );
	}
	if ( ! $partes && ( $w = $order->get_meta( '_gv_origen_web' ) ) ) {
		$partes[] = $w;
	}
	return implode( ' · ', $partes );
}

/* ---------- Escritorio: columna "Landing / campaña" en Pedidos (HPOS y modo antiguo).
   WooCommerce ya tiene su propia columna "Origen" (atribución por cookies); esta es la nuestra, sin cookies. ---------- */
$gv_origen_columna = function ( $columnas ) {
	$nuevas = array();
	foreach ( $columnas as $clave => $titulo ) {
		$nuevas[ $clave ] = $titulo;
		if ( 'order_status' === $clave ) {
			$nuevas['gv_origen'] = __( 'Landing / campaña', 'gemmavila-academia' );
		}
	}
	if ( ! isset( $nuevas['gv_origen'] ) ) {
		$nuevas['gv_origen'] = __( 'Landing / campaña', 'gemmavila-academia' );
	}
	return $nuevas;
};
add_filter( 'manage_woocommerce_page_wc-orders_columns', $gv_origen_columna );
add_filter( 'manage_edit-shop_order_columns', $gv_origen_columna );

add_action( 'manage_woocommerce_page_wc-orders_custom_column', function ( $columna, $order ) {
	if ( 'gv_origen' === $columna ) {
		$t = gv_origen_texto( $order );
		echo $t ? esc_html( $t ) : '<span style="color:#999">—</span>';
	}
}, 10, 2 );
add_action( 'manage_shop_order_posts_custom_column', function ( $columna, $post_id ) {
	if ( 'gv_origen' === $columna ) {
		$t = gv_origen_texto( wc_get_order( $post_id ) );
		echo $t ? esc_html( $t ) : '<span style="color:#999">—</span>';
	}
}, 10, 2 );

/* ---------- Dentro de cada pedido ---------- */
add_action( 'woocommerce_admin_order_data_after_order_details', function ( $order ) {
	$filas = array(
		__( 'Origen (marca)', 'gemmavila-academia' ) => $order->get_meta( '_gv_origen' ),
		__( 'Web de procedencia', 'gemmavila-academia' ) => $order->get_meta( '_gv_origen_web' ),
		'utm_source'   => $order->get_meta( '_gv_utm_source' ),
		'utm_medium'   => $order->get_meta( '_gv_utm_medium' ),
		'utm_campaign' => $order->get_meta( '_gv_utm_campaign' ),
		'utm_content'  => $order->get_meta( '_gv_utm_content' ),
	);
	$filas = array_filter( $filas );
	echo '<div class="form-field form-field-wide" style="margin-top:12px"><h3>' . esc_html__( 'Origen de la venta', 'gemmavila-academia' ) . '</h3>';
	if ( ! $filas ) {
		echo '<p style="color:#777">' . esc_html__( 'Sin datos de origen (venta directa o enlace sin marca).', 'gemmavila-academia' ) . '</p>';
	} else {
		echo '<p>';
		foreach ( $filas as $k => $v ) {
			echo '<strong>' . esc_html( $k ) . ':</strong> ' . esc_html( $v ) . '<br>';
		}
		echo '</p>';
	}
	echo '</div>';
} );

/* ---------- Correo "Nuevo pedido" que recibe el equipo ---------- */
add_action( 'woocommerce_email_order_meta', function ( $order, $sent_to_admin, $plain_text ) {
	if ( ! $sent_to_admin ) {
		return;
	}
	$t = gv_origen_texto( $order );
	if ( ! $t ) {
		return;
	}
	if ( $plain_text ) {
		echo "\n" . esc_html__( 'Origen de la venta', 'gemmavila-academia' ) . ': ' . esc_html( $t ) . "\n";
	} else {
		echo '<p><strong>' . esc_html__( 'Origen de la venta', 'gemmavila-academia' ) . ':</strong> ' . esc_html( $t ) . '</p>';
	}
}, 20, 3 );
