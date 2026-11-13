<?php
/**
 * Matrículas en tecnica-estructural.com (API Hermes, POST /bookings).
 *
 * Cuando un pedido queda pagado (Procesando o Completado), cada formación del pedido que tenga
 * evento de Técnica Estructural (amelia_event_id) se envía como matrícula.
 * - Éxito (201): se guarda el booking_id en la línea del pedido (_gv_hermes_booking_id) y una nota.
 * - Ya matriculada (409): no se reintenta; se marca y se avisa al equipo.
 * - Sin plazas u otro error de datos (4xx): no se reintenta; nota + aviso al equipo.
 * - Error temporal (429, 5xx, sin conexión): hasta 3 reintentos (1, 5 y 30 minutos) y después aviso.
 * Botón en el pedido: «Enviar matrícula a Técnica Estructural» para reenviar a mano.
 *
 * Seguridad en el entorno de pruebas: si wp-config define GV_HERMES_EVENTOS_PERMITIDOS
 * (p. ej. '1052'), solo se envían esos eventos; el resto se salta con una nota.
 */
defined( 'ABSPATH' ) || exit;

const GV_MATRICULA_HOOK = 'gv_hermes_enviar_matricula';

/** POST a la API Hermes. Devuelve [código HTTP, cuerpo decodificado] (código 0 si no hay conexión). */
function gv_hermes_post( $path, array $body ) {
	if ( ! defined( 'GV_HERMES_API_KEY' ) || ! GV_HERMES_API_KEY ) {
		return array( 0, array( 'message' => 'Falta la clave GV_HERMES_API_KEY en wp-config.php' ) );
	}
	$r = wp_remote_post( GV_HERMES_URL . $path, array(
		'headers' => array( 'X-API-Key' => GV_HERMES_API_KEY, 'Content-Type' => 'application/json' ),
		'body'    => wp_json_encode( $body ),
		'timeout' => 20,
	) );
	if ( is_wp_error( $r ) ) {
		return array( 0, array( 'message' => $r->get_error_message() ) );
	}
	return array( (int) wp_remote_retrieve_response_code( $r ), json_decode( wp_remote_retrieve_body( $r ), true ) );
}

/** ¿Se puede enviar este evento desde esta instalación? */
function gv_hermes_evento_permitido( $event_id ) {
	if ( ! defined( 'GV_HERMES_EVENTOS_PERMITIDOS' ) ) {
		return true;
	}
	$ok = array_map( 'intval', array_filter( array_map( 'trim', explode( ',', (string) GV_HERMES_EVENTOS_PERMITIDOS ) ) ) );
	return in_array( (int) $event_id, $ok, true );
}

/** Aviso por correo a los destinatarios del correo «Nuevo pedido» (Gemma, Dana, Johi). */
function gv_matricula_avisar_equipo( $order, $texto ) {
	$ajustes = get_option( 'woocommerce_new_order_settings', array() );
	$para    = ! empty( $ajustes['recipient'] ) ? $ajustes['recipient'] : get_option( 'admin_email' );
	$asunto  = sprintf( 'Matrícula NO enviada a Técnica Estructural · pedido %s', $order->get_order_number() );
	$cuerpo  = $texto . "\n\nPedido: " . $order->get_edit_order_url() . "\nAlumna: " . $order->get_formatted_billing_full_name() . ' <' . $order->get_billing_email() . ">\n\nHay que revisarlo y, si hace falta, matricularla a mano en tecnica-estructural.com.";
	wp_mail( $para, $asunto, $cuerpo );
}

/** Envía las matrículas pendientes de un pedido. $intento cuenta los reintentos automáticos. */
function gv_matricula_enviar_pedido( $order_id, $intento = 0 ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || ! in_array( $order->get_status(), array( 'processing', 'completed' ), true ) ) {
		return;
	}
	$reintentar = false;
	foreach ( $order->get_items() as $item ) {
		$pid   = $item->get_product_id();
		$event = (int) get_post_meta( $pid, 'amelia_event_id', true );
		if ( ! $event || $item->get_meta( '_gv_hermes_booking_id' ) || in_array( $item->get_meta( '_gv_hermes_estado' ), array( 'enviado', 'duplicado', 'rechazado' ), true ) ) {
			continue;
		}
		if ( ! gv_hermes_evento_permitido( $event ) ) {
			$order->add_order_note( sprintf( 'Matrícula de «%s» no enviada: el evento %d no está permitido en esta instalación (GV_HERMES_EVENTOS_PERMITIDOS).', $item->get_name(), $event ) );
			continue;
		}
		$body = array(
			'event_id' => $event,
			'customer' => array(
				'first_name'    => $order->get_billing_first_name(), // La API real usa snake_case (su OpenAPI dice firstName/lastName).
				'last_name'     => $order->get_billing_last_name(),
				'email'         => $order->get_billing_email(),
				'phone'         => $order->get_billing_phone(),
				'custom_fields' => array_filter( array( 'dni' => $order->get_meta( '_billing_nif' ) ) ),
			),
			'payment'  => array(
				'gateway'           => 'wc',
				'status'            => 'paid',
				'external_order_id' => 'WC-' . $order->get_order_number(),
			),
			'locale'             => 'es',
			'timezone'           => 'Europe/Madrid',
			'send_notifications' => false,
		);
		$ticket = (int) get_post_meta( $pid, 'amelia_ticket_id', true );
		if ( $ticket ) {
			$body['tickets'] = array( array( 'ticket_id' => $ticket, 'quantity' => max( 1, (int) $item->get_quantity() ) ) );
		}
		list( $code, $res ) = gv_hermes_post( '/bookings', $body );
		$msg = is_array( $res ) ? ( $res['message'] ?? ( $res['data']['message'] ?? '' ) ) : '';
		$cod = is_array( $res ) ? ( $res['code'] ?? '' ) : '';

		if ( 201 === $code || 200 === $code ) {
			$bid = $res['data']['booking_id'] ?? '';
			$item->update_meta_data( '_gv_hermes_booking_id', $bid );
			$item->update_meta_data( '_gv_hermes_estado', 'enviado' );
			$item->save();
			$order->add_order_note( sprintf( 'Matrícula enviada a Técnica Estructural: «%s», evento %d, reserva %s.', $item->get_name(), $event, $bid ? $bid : '(la API no devolvió número de reserva)' ) );
		} elseif ( 409 === $code ) {
			$item->update_meta_data( '_gv_hermes_estado', 'duplicado' );
			$item->save();
			$texto = sprintf( 'La alumna ya estaba matriculada en el evento %d («%s»). Técnica Estructural respondió: %s %s', $event, $item->get_name(), $cod, $msg );
			$order->add_order_note( 'Matrícula NO enviada. ' . $texto );
			gv_matricula_avisar_equipo( $order, $texto );
		} elseif ( 0 === $code || 429 === $code || ( $code >= 500 && 'validation_error' !== $cod ) ) {
			$reintentar = true;
			$order->add_order_note( sprintf( 'Error temporal al enviar la matrícula de «%s» (HTTP %d %s %s). Reintento %d de 3.', $item->get_name(), $code, $cod, $msg, $intento + 1 ) );
		} else {
			$item->update_meta_data( '_gv_hermes_estado', 'rechazado' );
			$item->save();
			$texto = sprintf( 'Técnica Estructural rechazó la matrícula de «%s» en el evento %d (HTTP %d): %s %s', $item->get_name(), $event, $code, $cod, $msg );
			$order->add_order_note( 'Matrícula NO enviada. ' . $texto );
			gv_matricula_avisar_equipo( $order, $texto );
		}
	}
	if ( $reintentar ) {
		$esperas = array( 60, 300, 1800 );
		if ( $intento < count( $esperas ) && function_exists( 'as_schedule_single_action' ) ) {
			as_schedule_single_action( time() + $esperas[ $intento ], GV_MATRICULA_HOOK, array( (int) $order_id, $intento + 1 ), 'gemmavila' );
		} else {
			gv_matricula_avisar_equipo( $order, 'No se pudo enviar la matrícula tras varios intentos (error de conexión o del servidor de Técnica Estructural).' );
		}
	}
}
add_action( GV_MATRICULA_HOOK, 'gv_matricula_enviar_pedido', 10, 2 );

// Al quedar pagado el pedido.
add_action( 'woocommerce_order_status_processing', 'gv_matricula_enviar_pedido', 20, 1 );
add_action( 'woocommerce_order_status_completed', 'gv_matricula_enviar_pedido', 20, 1 );

// Botón manual en el pedido (Acciones del pedido).
add_filter( 'woocommerce_order_actions', function ( $acciones ) {
	$acciones['gv_enviar_matricula'] = 'Enviar matrícula a Técnica Estructural';
	return $acciones;
} );
add_action( 'woocommerce_order_action_gv_enviar_matricula', function ( $order ) {
	foreach ( $order->get_items() as $item ) {
		if ( in_array( $item->get_meta( '_gv_hermes_estado' ), array( 'duplicado', 'rechazado' ), true ) ) {
			$item->delete_meta_data( '_gv_hermes_estado' ); // Envío manual: se vuelve a intentar.
			$item->save();
		}
	}
	gv_matricula_enviar_pedido( $order->get_id() );
} );
