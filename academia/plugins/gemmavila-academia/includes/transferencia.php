<?php
/**
 * PAGO POR TRANSFERENCIA BANCARIA (método "bacs" de WooCommerce).
 *
 * Un pedido por transferencia queda "En espera" y ocupa plaza (ver plazas.php)
 * hasta que el equipo comprueba el banco y lo pasa a Procesando/Completado.
 * Para que una reserva sin pagar no bloquee la plaza indefinidamente:
 *  - a los 2 días sin pago se envía un recordatorio a la alumna con los datos bancarios;
 *  - a los 3 días sin pago el pedido se cancela solo (WooCommerce libera la plaza)
 *    y se avisa a la alumna.
 * Los plazos se pueden cambiar con los filtros gv_transferencia_dias_aviso y
 * gv_transferencia_dias_cancelar.
 */
defined( 'ABSPATH' ) || exit;

const GV_TRANSF_HOOK = 'gv_transferencia_revisar';

add_action( 'init', function () {
	if ( ! wp_next_scheduled( GV_TRANSF_HOOK ) ) {
		wp_schedule_event( time() + 300, 'hourly', GV_TRANSF_HOOK );
	}
} );

add_action( GV_TRANSF_HOOK, 'gv_transferencia_revisar' );

/**
 * Revisa los pedidos por transferencia en espera. Devuelve un resumen (útil para pruebas).
 */
function gv_transferencia_revisar() {
	if ( ! function_exists( 'wc_get_orders' ) ) {
		return array();
	}
	$dias_aviso    = (int) apply_filters( 'gv_transferencia_dias_aviso', 2 );
	$dias_cancelar = (int) apply_filters( 'gv_transferencia_dias_cancelar', 3 );
	$ahora         = time();
	$resumen       = array( 'avisados' => array(), 'cancelados' => array() );

	$pedidos = wc_get_orders( array(
		'status'         => array( 'on-hold' ),
		'payment_method' => 'bacs',
		'date_created'   => '<' . ( $ahora - $dias_aviso * DAY_IN_SECONDS ),
		'limit'          => 100,
	) );

	foreach ( $pedidos as $order ) {
		$creado = $order->get_date_created();
		if ( ! $creado ) {
			continue;
		}
		$edad = $ahora - $creado->getTimestamp();

		if ( $edad >= $dias_cancelar * DAY_IN_SECONDS ) {
			$order->update_status(
				'cancelled',
				sprintf( 'Cancelado automáticamente: transferencia no recibida en %d días. La plaza queda libre.', $dias_cancelar )
			);
			gv_transferencia_correo( $order, 'cancelado' );
			$resumen['cancelados'][] = $order->get_id();
		} elseif ( ! $order->get_meta( '_gv_aviso_transferencia' ) ) {
			gv_transferencia_correo( $order, 'aviso' );
			$order->update_meta_data( '_gv_aviso_transferencia', current_time( 'mysql' ) );
			$order->add_order_note( 'Enviado recordatorio de pago por transferencia: mañana se cancela si no llega.' );
			$order->save();
			$resumen['avisados'][] = $order->get_id();
		}
	}
	return $resumen;
}

/** Correo a la alumna: recordatorio ("aviso") o cancelación ("cancelado"). */
function gv_transferencia_correo( $order, $tipo ) {
	$email = $order->get_billing_email();
	if ( ! $email || ! function_exists( 'WC' ) ) {
		return;
	}
	$nombre = $order->get_billing_first_name();
	$num    = $order->get_order_number();
	$total  = wp_strip_all_tags( $order->get_formatted_order_total() );
	$cursos = array();
	foreach ( $order->get_items() as $item ) {
		$cursos[] = $item->get_name();
	}
	$cursos = esc_html( implode( ', ', $cursos ) );

	if ( 'aviso' === $tipo ) {
		$asunto  = sprintf( 'Recordatorio: tu plaza (pedido %s) está pendiente de pago', $num );
		$titulo  = 'Tu plaza sigue reservada';
		$cuerpo  = '<p>Hola ' . esc_html( $nombre ) . ',</p>';
		$cuerpo .= '<p>Tenemos reservada tu plaza en <strong>' . $cursos . '</strong>, pero todavía no hemos recibido la transferencia de <strong>' . esc_html( $total ) . '</strong>.</p>';
		$cuerpo .= '<p>Si no la recibimos <strong>mañana</strong>, la reserva se cancelará y la plaza quedará libre para otra persona.</p>';
		$cuerpo .= gv_transferencia_datos_bancarios_html( $num );
		$cuerpo .= '<p>Si ya la has hecho, no tienes que hacer nada: las transferencias pueden tardar uno o dos días en llegar y lo revisamos a mano.</p>';
	} else {
		$asunto  = sprintf( 'Hemos cancelado tu reserva (pedido %s)', $num );
		$titulo  = 'Reserva cancelada';
		$cuerpo  = '<p>Hola ' . esc_html( $nombre ) . ',</p>';
		$cuerpo .= '<p>No hemos recibido la transferencia de tu reserva en <strong>' . $cursos . '</strong>, así que la hemos cancelado y la plaza ha quedado libre.</p>';
		$cuerpo .= '<p>Si quieres apuntarte, puedes volver a inscribirte desde la academia mientras queden plazas. Y si ya habías hecho la transferencia, respóndenos a este correo y lo solucionamos.</p>';
	}

	$mailer = WC()->mailer();
	$mailer->send( $email, $asunto, $mailer->wrap_message( $titulo, $cuerpo ) );
}

/** Datos bancarios tal y como están configurados en WooCommerce > Pagos > Transferencia bancaria. */
function gv_transferencia_datos_bancarios_html( $num ) {
	$cuentas = (array) get_option( 'woocommerce_bacs_accounts', array() );
	if ( empty( $cuentas ) ) {
		return '';
	}
	$html = '<p><strong>Datos para la transferencia</strong></p><ul>';
	foreach ( $cuentas as $c ) {
		$filas = array(
			'Titular' => $c['account_name'] ?? '',
			'Banco'   => $c['bank_name'] ?? '',
			'IBAN'    => $c['iban'] ?? '',
			'BIC'     => $c['bic'] ?? '',
		);
		foreach ( $filas as $etiqueta => $valor ) {
			if ( '' !== trim( (string) $valor ) ) {
				$html .= '<li>' . esc_html( $etiqueta ) . ': <strong>' . esc_html( $valor ) . '</strong></li>';
			}
		}
	}
	$html .= '<li>Concepto: <strong>Pedido ' . esc_html( $num ) . '</strong></li></ul>';
	return $html;
}

/** Al desactivar el plugin no debe quedar la tarea programada. */
register_deactivation_hook( dirname( __DIR__ ) . '/gemmavila-academia.php', function () {
	wp_clear_scheduled_hook( GV_TRANSF_HOOK );
} );
