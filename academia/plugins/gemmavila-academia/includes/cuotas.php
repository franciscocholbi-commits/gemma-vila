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

/*
 * Elegir «pago único» o «en cuotas» en Finalizar compra.
 * Los botones «Inscribirme» llevan directos al pago (sin carrito) con el pago único, así que la alumna
 * tiene que poder cambiarlo aquí. Al cambiar la opción se recalcula el pedido (update_checkout).
 */
add_action( 'woocommerce_checkout_before_order_review_heading', function () {
	if ( ! WC()->cart || ! class_exists( 'WCS_ATT_Cart' ) ) {
		return;
	}
	$filas = '';
	foreach ( WC()->cart->get_cart() as $clave => $item ) {
		if ( isset( $item['subscription_renewal'] ) || ! isset( $item['wcsatt_data'] ) ) {
			continue;
		}
		$plan = gv_plan_cuotas( $item['product_id'] );
		if ( ! $plan || $plan['cuotas'] < 2 ) {
			continue;
		}
		$producto = wc_get_product( $item['product_id'] );
		$activo   = WCS_ATT_Cart::get_subscription_scheme( $item );
		$nombre   = 'gv_plan[' . esc_attr( $clave ) . ']';
		$filas   .= '<div class="gv-forma-pago">';
		if ( WC()->cart->get_cart_contents_count() > 1 ) {
			$filas .= '<p class="gv-forma-pago__curso">' . esc_html( $producto->get_name() ) . '</p>';
		}
		$filas .= sprintf(
			'<label><input type="radio" name="%1$s" value="unico" %2$s> Pago único de %3$s</label>',
			$nombre,
			checked( empty( $activo ), true, false ),
			esc_html( gv_importe_corto( (float) wc_get_price_to_display( $producto ) ) )
		);
		$filas .= sprintf(
			'<label><input type="radio" name="%1$s" value="%2$s" %3$s> En %4$d cuotas mensuales de %5$s <small>(la primera hoy; las demás se cobran solas a la misma tarjeta)</small></label>',
			$nombre,
			esc_attr( $plan['clave'] ),
			checked( $activo, $plan['clave'], false ),
			$plan['cuotas'],
			esc_html( gv_importe_corto( $plan['importe'] ) )
		);
		$filas .= '</div>';
	}
	if ( ! $filas ) {
		return;
	}
	echo '<div id="gv-formas-pago"><h3>¿Cómo quieres pagar?</h3>' . $filas . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
	<style>
		#gv-formas-pago{margin:0 0 1.5em}
		#gv-formas-pago label{display:block;padding:.75em 1em;margin:0 0 .5em;border:1px solid #ddd;border-radius:6px;cursor:pointer}
		#gv-formas-pago label:has(input:checked){border-color:currentColor;font-weight:600}
		#gv-formas-pago small{font-weight:400;opacity:.75}
		#gv-formas-pago .gv-forma-pago__curso{margin:.5em 0 .25em;font-weight:600}
	</style>
	<script>
		jQuery( function ( $ ) {
			$( document.body ).on( 'change', '#gv-formas-pago input', function () {
				$( document.body ).trigger( 'update_checkout' );
			} );
		} );
	</script>
	<?php
} );

/** Aplica la opción elegida al recalcular el pedido en Finalizar compra. */
add_action( 'woocommerce_checkout_update_order_review', function ( $post_data ) {
	if ( ! WC()->cart || ! class_exists( 'WCS_ATT_Cart' ) ) {
		return;
	}
	parse_str( (string) $post_data, $datos );
	if ( empty( $datos['gv_plan'] ) || ! is_array( $datos['gv_plan'] ) ) {
		return;
	}
	$cambio = false;
	foreach ( $datos['gv_plan'] as $clave => $valor ) {
		$clave = wc_clean( $clave );
		$item  = WC()->cart->get_cart_item( $clave );
		if ( ! $item || ! isset( $item['wcsatt_data'] ) || isset( $item['subscription_renewal'] ) ) {
			continue;
		}
		$plan  = gv_plan_cuotas( $item['product_id'] );
		$nuevo = ( $plan && $valor === $plan['clave'] ) ? $plan['clave'] : false;
		if ( WCS_ATT_Cart::get_subscription_scheme( $item ) !== $nuevo ) {
			WC()->cart->cart_contents[ $clave ]['wcsatt_data']['active_subscription_scheme'] = $nuevo;
			$cambio = true;
		}
	}
	if ( $cambio ) {
		WCS_ATT_Cart::apply_subscription_schemes( WC()->cart );
		WC()->cart->set_session();
	}
} );

/*
 * En la home, el listado y las fichas solo se ve el precio total (decisión de Francisco, 19/11/2026).
 * Las cuotas se eligen en Finalizar compra. Quita el «— o 225 € / mes durante 2 meses» que añade el plugin.
 */
add_filter( 'woocommerce_get_price_html', function ( $html ) {
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return $html;
	}
	$pos = strpos( (string) $html, '<small class="wcsatt-sub-options">' );
	return false === $pos ? $html : rtrim( substr( $html, 0, $pos ) );
}, 10000 );

/*
 * Resumen del pedido en Finalizar compra, en lenguaje de cuotas (decisión de Francisco, 19/11/2026):
 * - Línea de la formación: «225 € hoy (cuota 1 de 2)» en vez de «225 € / mes durante 2 meses».
 * - «Cuotas siguientes»: «1 cuota más de 225 € el 19/12/2026» (o «2 cuotas más de 210 €: 19/12/2026 y 19/01/2027»).
 * - Se ocultan la cabecera y el subtotal repetidos del bloque de cuotas.
 */
add_filter( 'woocommerce_cart_item_subtotal', function ( $html, $item ) {
	if ( ! class_exists( 'WCS_ATT_Cart' ) || isset( $item['subscription_renewal'] ) || ! WCS_ATT_Cart::get_subscription_scheme( $item ) ) {
		return $html;
	}
	$plan = gv_plan_cuotas( $item['product_id'] );
	if ( ! $plan ) {
		return $html;
	}
	$importe = (float) $item['line_subtotal'] + ( 'incl' === get_option( 'woocommerce_tax_display_cart' ) ? (float) $item['line_subtotal_tax'] : 0 );
	return wc_price( $importe ) . sprintf( ' <small>hoy (cuota 1 de %d)</small>', $plan['cuotas'] );
}, 10000, 2 );

add_filter( 'wcs_cart_totals_order_total_html', function ( $html, $cart ) {
	if ( empty( $cart->next_payment_date ) ) {
		return $html;
	}
	$plan = null;
	foreach ( $cart->get_cart() as $item ) {
		$plan = gv_plan_cuotas( $item['product_id'] );
		break;
	}
	if ( ! $plan || $plan['cuotas'] < 2 ) {
		return $html;
	}
	$quedan = $plan['cuotas'] - 1;
	$fechas = array();
	$inicio = wcs_date_to_time( get_date_from_gmt( $cart->next_payment_date ) );
	for ( $i = 0; $i < $quedan; $i++ ) {
		$fechas[] = date_i18n( wc_date_format(), strtotime( '+' . $i . ' month', $inicio ) );
	}
	$importe = wc_price( (float) $cart->get_total( 'edit' ) );
	if ( 1 === $quedan ) {
		return sprintf( '1 cuota más de %s el %s', $importe, $fechas[0] );
	}
	$ultima = array_pop( $fechas );
	return sprintf( '%d cuotas más de %s: %s y %s', $quedan, $importe, implode( ', ', $fechas ), $ultima );
}, 20, 2 );

add_action( 'wp_head', function () {
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		echo '<style>#order_review tr.recurring-totals,#order_review tr.cart-subtotal.recurring-total{display:none}</style>';
	}
} );
