<?php
/**
 * Pago en cuotas (WooCommerce Subscriptions con planes en cada formación).
 *
 * Decisiones de Francisco (19/11/2026):
 * - Módulo 1: 2 cuotas de 225 €; Módulo 2: 3 cuotas de 210 €; packs: 3 cuotas de 333 €. Mensuales.
 *   La primera se paga al inscribirse; las demás se cobran solas a la tarjeta guardada.
 * - Acceso al curso desde la primera cuota; si una cuota queda sin pagar, se retira hasta que se pague.
 * - Puede quedar alguna cuota después de terminar el curso.
 * - La matrícula en Técnica Estructural se envía una sola vez, con la primera cuota.
 *
 * Los planes se guardan en cada formación (WooCommerce > producto > «Suscripciones»).
 * Este archivo añade lo que no se puede configurar:
 * - Enlace y botón de «pagar en cuotas» ([gv_boton_cuotas]) para fichas y landings.
 * - La alumna no puede cancelar ni suspender sus cuotas desde Mi cuenta.
 * - Utilidades para saber si un pedido es una cuota (lo usan plazas, matrículas y el cuadro de mando).
 */
defined( 'ABSPATH' ) || exit;

/** ¿Es un pedido de una cuota posterior a la primera (renovación)? */
function gv_es_cuota_renovacion( $order ) {
	return function_exists( 'wcs_order_contains_renewal' ) && $order && wcs_order_contains_renewal( $order );
}

/**
 * Plan de cuotas de una formación.
 *
 * @return array|null [ 'clave' => '1_month_2', 'cuotas' => 2, 'importe' => 225.0 ] o null si no tiene.
 */
function gv_plan_cuotas( $product ) {
	if ( ! class_exists( 'WCS_ATT_Product_Schemes' ) ) {
		return null;
	}
	$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;
	if ( ! $product instanceof WC_Product ) {
		return null;
	}
	$planes = WCS_ATT_Product_Schemes::get_subscription_schemes( $product );
	if ( empty( $planes ) ) {
		return null;
	}
	$clave = array_key_first( $planes );
	return array(
		'clave'   => $clave,
		'cuotas'  => (int) $planes[ $clave ]->get_length(),
		'importe' => (float) WCS_ATT_Product_Prices::get_price( $product, $clave ),
	);
}

/** Importe en formato «225 €» (o «225,50 €» si lleva céntimos). */
function gv_importe_corto( $importe ) {
	$decimales = ( floor( $importe ) == $importe ) ? 0 : 2; // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
	return html_entity_decode( wp_strip_all_tags( wc_price( $importe, array( 'decimals' => $decimales ) ) ) );
}

/**
 * Enlace para inscribirse en cuotas: va directo al pago con el plan ya elegido.
 * Para las landings: https://academia.gemmavilaaccion.es/finalizar-compra/?add-to-cart=ID&convert_to_sub_ID=1_month_2&origen=...
 */
function gv_url_cuotas( $product_id, $origen = '' ) {
	$plan = gv_plan_cuotas( $product_id );
	if ( ! $plan ) {
		return '';
	}
	$args = array(
		'add-to-cart'                         => (int) $product_id,
		'convert_to_sub_' . (int) $product_id => $plan['clave'],
	);
	if ( $origen ) {
		$args['origen'] = $origen;
	}
	return add_query_arg( $args, wc_get_checkout_url() );
}

/**
 * [gv_boton_cuotas] — botón «O en 2 cuotas de 225 €» para la ficha de la formación.
 * Atributos opcionales: id (formación; por defecto, la de la página), texto, clase, origen.
 * No muestra nada si la formación no tiene plan o no se puede comprar (cerrada, sin plazas).
 */
add_shortcode( 'gv_boton_cuotas', function ( $atts ) {
	$atts = shortcode_atts( array( 'id' => 0, 'texto' => '', 'clase' => 'gv-boton-cuotas', 'origen' => '' ), $atts );
	$id   = $atts['id'] ? (int) $atts['id'] : get_the_ID();
	$p    = wc_get_product( $id );
	if ( ! $p || ! $p->is_purchasable() || ! $p->is_in_stock() ) {
		return '';
	}
	$plan = gv_plan_cuotas( $p );
	if ( ! $plan ) {
		return '';
	}
	$texto = $atts['texto'] ? $atts['texto'] : sprintf( 'O en %d cuotas de %s', $plan['cuotas'], gv_importe_corto( $plan['importe'] ) );
	return sprintf(
		'<a class="%s" href="%s" rel="nofollow">%s</a>',
		esc_attr( $atts['clase'] ),
		esc_url( gv_url_cuotas( $id, $atts['origen'] ) ),
		esc_html( $texto )
	);
} );

/*
 * La alumna no puede cancelar, suspender ni renovar antes de tiempo desde Mi cuenta.
 * Sí puede cambiar la tarjeta (necesario si una cuota falla). Las bajas las gestiona el equipo.
 */
add_filter( 'wcs_view_subscription_actions', function ( $acciones ) {
	unset( $acciones['cancel'], $acciones['suspend'], $acciones['resubscribe'], $acciones['subscription_renewal_early'] );
	return $acciones;
}, 99 );

add_filter( 'woocommerce_can_subscription_be_updated_to_cancelled', function ( $puede, $subscription ) {
	// Solo bloquea la petición de la alumna desde Mi cuenta (?change_subscription_to=cancelled).
	if ( isset( $_GET['change_subscription_to'] ) && ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return false;
	}
	return $puede;
}, 10, 2 );

/** Texto de la cuota de un pedido para el cuadro de mando: «única», «1/2», «2/2»… */
function gv_cuota_texto( $order ) {
	if ( ! function_exists( 'wcs_get_subscriptions_for_order' ) ) {
		return 'única';
	}
	$subs = wcs_get_subscriptions_for_order( $order, array( 'order_type' => array( 'parent', 'renewal' ) ) );
	if ( empty( $subs ) ) {
		return 'única';
	}
	$sub = reset( $subs );
	$ids = $sub->get_related_orders( 'ids', array( 'parent', 'renewal' ) );
	sort( $ids );
	$pos   = array_search( $order->get_id(), $ids, true );
	$total = 0;
	foreach ( $sub->get_items() as $item ) {
		$plan  = gv_plan_cuotas( $item->get_product_id() );
		$total = $plan ? $plan['cuotas'] : 0;
		break;
	}
	return ( false === $pos ? '?' : $pos + 1 ) . '/' . ( $total ? $total : '?' );
}
