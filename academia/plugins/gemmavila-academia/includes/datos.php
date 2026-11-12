<?php
/**
 * DATOS PARA EL CUADRO DE MANDO (v0.11.0)
 *
 * Publica dos listados en CSV para que una hoja de Google los importe sola (IMPORTDATA)
 * y Looker Studio los lea desde esa hoja:
 *
 *   /wp-json/gv/v1/datos/ventas?clave=XXXX     una fila por pedido
 *   /wp-json/gv/v1/datos/ediciones?clave=XXXX  una fila por formación (edición)
 *
 * Sin datos personales: ni nombres, ni correos, ni teléfonos, ni NIF.
 * La clave se genera sola la primera vez (opción gv_datos_clave) y se puede cambiar
 * con WP-CLI:  wp option update gv_datos_clave "$(openssl rand -hex 16)"
 *
 * Pedidos de prueba: se marcan con la meta _gv_pedido_prueba = 1 y salen con prueba = si,
 * para filtrarlos en el cuadro de mando.
 */
defined( 'ABSPATH' ) || exit;

/** Clave de acceso a los listados (se crea la primera vez). */
function gv_datos_clave() {
	$clave = get_option( 'gv_datos_clave' );
	if ( ! $clave ) {
		$clave = wp_generate_password( 32, false, false );
		update_option( 'gv_datos_clave', $clave, false );
	}
	return $clave;
}

add_action( 'rest_api_init', function () {
	foreach ( array( 'ventas', 'ediciones' ) as $listado ) {
		register_rest_route( 'gv/v1', '/datos/' . $listado, array(
			'methods'             => 'GET',
			'permission_callback' => function ( WP_REST_Request $r ) {
				return hash_equals( gv_datos_clave(), (string) $r->get_param( 'clave' ) );
			},
			'callback'            => function () use ( $listado ) {
				$filas = 'ventas' === $listado ? gv_datos_ventas() : gv_datos_ediciones();
				return new WP_REST_Response( array( '_gv_csv' => $filas ), 200 );
			},
		) );
	}
} );

// Las respuestas de estas rutas salen como CSV, no como JSON.
add_filter( 'rest_pre_serve_request', function ( $served, $result, $request ) {
	if ( 0 !== strpos( $request->get_route(), '/gv/v1/datos/' ) ) {
		return $served;
	}
	$data = $result->get_data();
	if ( ! isset( $data['_gv_csv'] ) ) {
		return $served; // error de clave: sale el JSON de error normal
	}
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'X-Robots-Tag: noindex' );
	$out = fopen( 'php://output', 'w' );
	foreach ( $data['_gv_csv'] as $fila ) {
		fputcsv( $out, $fila, ',', '"', '' );
	}
	fclose( $out );
	return true;
}, 10, 3 );

/** Tipo de formación a partir del producto: pack, online o presencial. */
function gv_datos_tipo( $product ) {
	if ( ! $product ) {
		return '';
	}
	$cats = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) );
	$cats = is_wp_error( $cats ) ? array() : array_map( 'strtolower', $cats );
	$nombre = strtolower( $product->get_name() );
	if ( in_array( 'packs', $cats, true ) || false !== strpos( $nombre, 'pack' ) ) {
		return 'pack';
	}
	if ( false !== strpos( $nombre, 'online' ) ) {
		return 'online';
	}
	return 'presencial';
}

/** Módulo (M1, M2…) a partir del nombre. */
function gv_datos_modulo( $nombre ) {
	if ( preg_match( '/m[oó]dulo\s*(\d+)/iu', $nombre, $m ) ) {
		return 'M' . $m[1];
	}
	return stripos( $nombre, 'pack' ) !== false ? 'Pack' : '';
}

/** Listado de ventas: una fila por pedido. */
function gv_datos_ventas() {
	$cab = array( 'pedido', 'fecha', 'mes', 'estado', 'prueba', 'curso_id', 'curso', 'modulo', 'tipo', 'fecha_edicion',
		'importe', 'iva', 'devuelto', 'comision_stripe', 'neto_stripe', 'alumno', 'provincia', 'pais',
		'origen', 'utm_source', 'utm_medium', 'utm_campaign', 'web_procedencia', 'matricula_te' );
	$filas = array( $cab );

	$pedidos = wc_get_orders( array(
		'limit'   => -1,
		'type'    => 'shop_order',
		'status'  => array( 'wc-processing', 'wc-completed', 'wc-on-hold', 'wc-refunded', 'wc-cancelled' ),
		'orderby' => 'date',
		'order'   => 'ASC',
	) );

	$vistos = array(); // correo (en hash) => nº de compras anteriores, para nuevo / repetidor
	$estados_es = array( 'processing' => 'pagado', 'completed' => 'completado', 'on-hold' => 'en espera', 'refunded' => 'devuelto', 'cancelled' => 'cancelado' );

	foreach ( $pedidos as $o ) {
		$items = $o->get_items();
		$item  = $items ? reset( $items ) : null;
		$p     = $item ? $item->get_product() : null;
		$pid   = $item ? (int) $item->get_product_id() : 0;
		$nom   = $item ? $item->get_name() : '';
		if ( count( $items ) > 1 ) {
			$nom .= ' (+' . ( count( $items ) - 1 ) . ')';
		}

		$hash   = md5( strtolower( trim( (string) $o->get_billing_email() ) ) );
		$pagado = in_array( $o->get_status(), array( 'processing', 'completed', 'refunded' ), true );
		$alumno = ! empty( $vistos[ $hash ] ) ? 'repetidor' : 'nuevo';
		if ( $pagado ) {
			$vistos[ $hash ] = ( $vistos[ $hash ] ?? 0 ) + 1;
		}

		$pais      = $o->get_billing_country();
		$prov      = $o->get_billing_state();
		$provincias = $pais ? WC()->countries->get_states( $pais ) : array();
		$fecha     = $o->get_date_created();

		$matricula = '';
		if ( $item && $item->get_meta( '_gv_hermes_booking_id' ) ) {
			$matricula = 'enviada';
		} elseif ( $pid && get_post_meta( $pid, 'amelia_event_id', true ) ) {
			$matricula = 'pendiente';
		}

		$filas[] = array(
			$o->get_id(),
			$fecha ? $fecha->date_i18n( 'Y-m-d H:i' ) : '',
			$fecha ? $fecha->date_i18n( 'Y-m' ) : '',
			$estados_es[ $o->get_status() ] ?? $o->get_status(),
			$o->get_meta( '_gv_pedido_prueba' ) ? 'si' : 'no',
			$pid,
			$nom,
			gv_datos_modulo( $nom ),
			gv_datos_tipo( $p ),
			$pid ? (string) get_post_meta( $pid, '_gv_fecha_inicio', true ) : '',
			wc_format_decimal( $o->get_total(), 2 ),
			wc_format_decimal( $o->get_total_tax(), 2 ),
			wc_format_decimal( $o->get_total_refunded(), 2 ),
			wc_format_decimal( (float) $o->get_meta( '_stripe_fee' ), 2 ),
			wc_format_decimal( (float) $o->get_meta( '_stripe_net' ), 2 ),
			$alumno,
			$prov && isset( $provincias[ $prov ] ) ? html_entity_decode( $provincias[ $prov ] ) : $prov,
			$pais,
			$o->get_meta( '_gv_origen' ),
			$o->get_meta( '_gv_utm_source' ),
			$o->get_meta( '_gv_utm_medium' ),
			$o->get_meta( '_gv_utm_campaign' ),
			$o->get_meta( '_gv_origen_web' ),
			$matricula,
		);
	}
	return $filas;
}

/** Listado de ediciones: una fila por formación (publicada, oculta o privada). */
function gv_datos_ediciones() {
	$cab   = array( 'curso_id', 'curso', 'modulo', 'tipo', 'fecha_edicion', 'dias_para_empezar', 'precio', 'visible',
		'plazas_libres', 'vendidas_academia', 'ingresos_academia', 'plazas_sincronizadas', 'evento_te' );
	$filas = array( $cab );

	$ids = get_posts( array( 'post_type' => 'product', 'post_status' => array( 'publish', 'private' ), 'posts_per_page' => -1, 'fields' => 'ids' ) );

	// Ventas pagadas por producto (sin pedidos de prueba).
	$ventas = array();
	foreach ( wc_get_orders( array( 'limit' => -1, 'type' => 'shop_order', 'status' => array( 'wc-processing', 'wc-completed' ) ) ) as $o ) {
		if ( $o->get_meta( '_gv_pedido_prueba' ) ) {
			continue;
		}
		foreach ( $o->get_items() as $it ) {
			$id = (int) $it->get_product_id();
			$ventas[ $id ]['n']   = ( $ventas[ $id ]['n'] ?? 0 ) + (int) $it->get_quantity();
			$ventas[ $id ]['eur'] = ( $ventas[ $id ]['eur'] ?? 0 ) + (float) $it->get_total() + (float) $it->get_total_tax();
		}
	}

	$hoy = new DateTime( current_time( 'Y-m-d' ), wp_timezone() );
	foreach ( $ids as $id ) {
		$p = wc_get_product( $id );
		if ( ! $p ) {
			continue;
		}
		$fecha = (string) get_post_meta( $id, '_gv_fecha_inicio', true );
		$dias  = '';
		if ( $fecha ) {
			$d    = new DateTime( $fecha, wp_timezone() );
			$dias = (int) $hoy->diff( $d )->format( '%r%a' );
		}
		$filas[] = array(
			$id,
			$p->get_name(),
			gv_datos_modulo( $p->get_name() ),
			gv_datos_tipo( $p ),
			$fecha,
			$dias,
			wc_format_decimal( $p->get_price(), 2 ),
			( 'publish' === $p->get_status() && 'hidden' !== $p->get_catalog_visibility() ) ? 'si' : 'no',
			$p->managing_stock() ? (int) $p->get_stock_quantity() : '',
			(int) ( $ventas[ $id ]['n'] ?? 0 ),
			wc_format_decimal( $ventas[ $id ]['eur'] ?? 0, 2 ),
			(string) get_post_meta( $id, '_gv_plazas_sincronizadas', true ),
			(string) get_post_meta( $id, 'amelia_event_id', true ),
		);
	}
	return $filas;
}
