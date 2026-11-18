<?php
/**
 * Plugin Name: Gemma Vila · IVA por país y facturas
 * Description: Mismo precio final para todos. En España y la UE el precio lleva el 21 % de IVA incluido; fuera de la UE se cobra el mismo importe sin IVA. Las facturas PDF desglosan base e IVA y, si la venta va sin IVA, lo indican.
 * Version: 1.0.0
 * Author: Francisco Cholbi
 *
 * Decisión de Francisco (18/11/2026): todas las facturas se hacen con el
 * sistema interno (PDF Invoices & Packing Slips). Clientes de fuera de la UE:
 * factura sin IVA por el importe final (no por el importe reducido).
 *
 * Las tasas de IVA (21 % para España y los países de la UE) y el cálculo
 * según la dirección de facturación están en WooCommerce > Ajustes > Impuestos.
 * Este archivo solo añade lo que WooCommerce no permite configurar.
 */

defined( 'ABSPATH' ) || exit;

/*
 * 1. Mismo precio final en todos los países.
 * Por defecto, con precios con IVA incluido, WooCommerce le quita el IVA a
 * quien no lo paga (440 € → 363,64 €). Con esto el precio se mantiene:
 * fuera de la UE se cobran 440 € sin IVA.
 */
add_filter( 'woocommerce_adjust_non_base_location_prices', '__return_false' );

/*
 * 2. Factura PDF con base imponible + IVA desglosados.
 * La web muestra los precios con IVA incluido; solo mientras se genera la
 * factura se muestran sin IVA, con la línea de IVA aparte.
 */
function gv_factura_mostrar_sin_iva() {
	return 'excl';
}

add_action(
	'wpo_wcpdf_before_html',
	function () {
		add_filter( 'pre_option_woocommerce_tax_display_cart', 'gv_factura_mostrar_sin_iva' );
	}
);

add_action(
	'wpo_wcpdf_after_html',
	function () {
		remove_filter( 'pre_option_woocommerce_tax_display_cart', 'gv_factura_mostrar_sin_iva' );
	}
);

/*
 * 3. Nota en la factura cuando la venta va sin IVA (cliente fuera de la UE).
 * Texto pendiente de que lo valide el gestor.
 */
add_action(
	'wpo_wcpdf_after_order_details',
	function ( $document_type, $order ) {
		if ( 'invoice' !== $document_type || ! $order instanceof WC_Order ) {
			return;
		}
		$pais = $order->get_billing_country();
		if ( ! $pais || 'ES' === $pais || (float) $order->get_total_tax() > 0 ) {
			return;
		}
		if ( in_array( $pais, WC()->countries->get_european_union_countries(), true ) ) {
			return;
		}
		echo '<p class="gv-nota-iva" style="margin-top:10mm;font-size:9pt;">'
			. esc_html__( 'Operación no sujeta al IVA español: cliente con domicilio fuera de la Unión Europea.', 'gemmavila' )
			. '</p>';
	},
	10,
	2
);
