<?php
/**
 * Plazas sincronizadas con tecnica-estructural.com (API Hermes).
 *
 * Cada formación lleva el ID de evento (amelia_event_id) y de entrada (amelia_ticket_id) de Técnica Estructural.
 * - Cada hora se consultan las plazas libres y se ponen como existencias de la formación.
 * - Justo antes de cobrar se vuelve a consultar: si ya no queda plaza, no se cobra.
 *
 * Plazas libres = la cifra MÁS BAJA entre:
 *   a) las plazas que quedan en la entrada (ticket) de primera inscripción, y
 *   b) la capacidad del evento menos todo lo vendido en él (solo si la capacidad es real, > 1).
 * Y a eso se le restan nuestras ventas que todavía no constan allí (pedidos pagados sin matrícula enviada).
 *
 * La clave va en wp-config.php como constante GV_HERMES_API_KEY. Nunca en la base de datos ni en el código.
 */
defined( 'ABSPATH' ) || exit;

const GV_HERMES_URL = 'https://tecnica-estructural.com/wp-json/hermes/v1';

/** Consulta GET a la API Hermes. Devuelve el array "data" o null si falla. */
function gv_hermes_get( $path ) {
	if ( ! defined( 'GV_HERMES_API_KEY' ) || ! GV_HERMES_API_KEY ) {
		return null;
	}
	$r = wp_remote_get( GV_HERMES_URL . $path, array(
		'headers' => array( 'X-API-Key' => GV_HERMES_API_KEY ),
		'timeout' => 10,
	) );
	if ( is_wp_error( $r ) || 200 !== (int) wp_remote_retrieve_response_code( $r ) ) {
		return null;
	}
	$body = json_decode( wp_remote_retrieve_body( $r ), true );
	return ( ! empty( $body['success'] ) && isset( $body['data'] ) ) ? $body['data'] : null;
}

/** Pedidos pagados de esta formación que aún no se han enviado a Técnica Estructural. */
function gv_ventas_pendientes_de_enviar( $product_id ) {
	$orders = wc_get_orders( array(
		'status' => array( 'processing', 'completed', 'on-hold' ),
		'limit'  => -1,
		'return' => 'objects',
	) );
	$n = 0;
	foreach ( $orders as $o ) {
		foreach ( $o->get_items() as $item ) {
			if ( (int) $item->get_product_id() === (int) $product_id && ! $item->get_meta( '_gv_hermes_booking_id' ) ) {
				$n += (int) $item->get_quantity();
			}
		}
	}
	return $n;
}

/** Plazas libres de una formación según Técnica Estructural. null = no se ha podido saber. */
function gv_plazas_libres( $product_id ) {
	$event  = (int) get_post_meta( $product_id, 'amelia_event_id', true );
	$ticket = (int) get_post_meta( $product_id, 'amelia_ticket_id', true );
	if ( ! $event || ! $ticket ) {
		return null;
	}
	$tickets = gv_hermes_get( '/events/' . $event . '/tickets' );
	$evento  = gv_hermes_get( '/events/' . $event );
	if ( ! is_array( $tickets ) || ! is_array( $evento ) ) {
		return null;
	}
	$libres_ticket = null;
	$vendidas      = 0;
	foreach ( $tickets as $t ) {
		$vendidas += (int) ( $t['spots_sold'] ?? 0 );
		if ( (int) ( $t['id'] ?? 0 ) === $ticket ) {
			$libres_ticket = (int) ( $t['spots_remaining'] ?? 0 );
		}
	}
	if ( null === $libres_ticket ) {
		return null;
	}
	$libres = $libres_ticket;
	$capacidad = (int) ( $evento['max_capacity'] ?? 0 );
	if ( $capacidad > 1 ) {
		$libres = min( $libres, $capacidad - $vendidas );
	}
	$libres -= gv_ventas_pendientes_de_enviar( $product_id );
	return max( 0, $libres );
}

/** Sincroniza las existencias de todas las formaciones con evento que aún no han empezado. */
function gv_sincronizar_plazas() {
	$ids = get_posts( array(
		'post_type'      => 'product',
		'post_status'    => array( 'publish', 'private', 'draft' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => 'amelia_event_id',
		'meta_compare'   => '!=',
		'meta_value'     => '',
	) );
	$log = array();
	foreach ( $ids as $id ) {
		if ( function_exists( 'gv_curso_ha_empezado' ) && gv_curso_ha_empezado( $id ) ) {
			continue;
		}
		$libres = gv_plazas_libres( $id );
		if ( null === $libres ) {
			$log[ $id ] = 'sin respuesta';
			continue; // Si la API no responde, se deja lo que había.
		}
		$p = wc_get_product( $id );
		if ( ! $p ) {
			continue;
		}
		if ( ! $p->get_manage_stock() || (int) $p->get_stock_quantity() !== $libres ) {
			$p->set_manage_stock( true );
			$p->set_backorders( 'no' );
			$p->set_stock_quantity( $libres );
			$p->save();
		}
		update_post_meta( $id, '_gv_plazas_sincronizadas', current_time( 'mysql' ) );
		$log[ $id ] = $libres;
	}
	update_option( 'gv_plazas_ultima_sincronizacion', array( 'cuando' => current_time( 'mysql' ), 'resultado' => $log ), false );
	return $log;
}
add_action( 'gv_sincronizar_plazas', 'gv_sincronizar_plazas' );

add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'gv_sincronizar_plazas' ) ) {
		wp_schedule_event( time() + 300, 'hourly', 'gv_sincronizar_plazas' );
	}
} );

// Justo antes de cobrar: comprobar plaza en ese momento.
add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
	foreach ( WC()->cart->get_cart() as $item ) {
		$id = (int) $item['product_id'];
		if ( ! get_post_meta( $id, 'amelia_event_id', true ) ) {
			continue;
		}
		$libres = gv_plazas_libres( $id );
		if ( null === $libres ) {
			continue; // Si la API no responde no bloqueamos la venta; la sincronización horaria lo corrige.
		}
		if ( $libres < 1 ) {
			$errors->add( 'gv_sin_plazas', sprintf( 'Lo sentimos, «%s» se acaba de quedar sin plazas. No se te ha cobrado nada. Escríbenos y te avisamos si se libera alguna.', get_the_title( $id ) ) );
			$p = wc_get_product( $id );
			$p->set_manage_stock( true );
			$p->set_stock_quantity( 0 );
			$p->save();
		}
	}
}, 10, 2 );

// Los textos de plazas (Completo / Quedan N) están en includes/plazas-visibles.php.

register_deactivation_hook( dirname( __DIR__ ) . '/gemmavila-academia.php', function () {
	wp_clear_scheduled_hook( 'gv_sincronizar_plazas' );
} );
