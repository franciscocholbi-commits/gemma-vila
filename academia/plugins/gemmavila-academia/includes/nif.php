<?php
/**
 * NIF / DNI / NIE en el pago.
 *
 * Los cursos superan los 400 € y necesitan factura completa, que lleva el NIF del alumno.
 * - Campo obligatorio "NIF / DNI / NIE" en los datos de facturación (pago y Mi cuenta).
 * - Si el país es España se comprueba que el formato y la letra son correctos (DNI, NIE o CIF).
 *   Fuera de España se acepta el documento que tenga.
 * - Se guarda en el pedido (_billing_nif) y en la cuenta del alumno (billing_nif), y aparece
 *   en la dirección de facturación: escritorio, correos, Mi cuenta y página de gracias.
 */
defined( 'ABSPATH' ) || exit;

/** Quita espacios, guiones y puntos y pasa a mayúsculas. */
function gv_nif_normalizar( $nif ) {
	return strtoupper( preg_replace( '/[\s\-\.]/', '', (string) $nif ) );
}

/** ¿Es un DNI, NIE o CIF español válido? */
function gv_nif_valido_es( $nif ) {
	$nif    = gv_nif_normalizar( $nif );
	$letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
	// DNI: 8 cifras + letra.
	if ( preg_match( '/^(\d{8})([A-Z])$/', $nif, $m ) ) {
		return $letras[ (int) $m[1] % 23 ] === $m[2];
	}
	// NIE: X/Y/Z + 7 cifras + letra.
	if ( preg_match( '/^([XYZ])(\d{7})([A-Z])$/', $nif, $m ) ) {
		$num = strtr( $m[1], array( 'X' => '0', 'Y' => '1', 'Z' => '2' ) ) . $m[2];
		return $letras[ (int) $num % 23 ] === $m[3];
	}
	// CIF (empresas): letra + 7 cifras + control (cifra o letra).
	if ( preg_match( '/^([ABCDEFGHJNPQRSUVW])(\d{7})([0-9A-J])$/', $nif, $m ) ) {
		$d   = $m[2];
		$sum = 0;
		for ( $i = 0; $i < 7; $i++ ) {
			$n = (int) $d[ $i ];
			if ( 0 === $i % 2 ) { // Posiciones impares (1.ª, 3.ª, 5.ª, 7.ª): doble y se suman sus cifras.
				$n  *= 2;
				$sum += intdiv( $n, 10 ) + ( $n % 10 );
			} else {
				$sum += $n;
			}
		}
		$c = ( 10 - ( $sum % 10 ) ) % 10;
		return $m[3] === (string) $c || $m[3] === 'JABCDEFGHI'[ $c ];
	}
	return false;
}

/** Mensaje de error o cadena vacía si el NIF vale. */
function gv_nif_error( $nif, $pais ) {
	$nif = gv_nif_normalizar( $nif );
	if ( '' === $nif ) {
		return 'Necesitamos tu NIF, DNI o NIE para la factura.';
	}
	if ( 'ES' === $pais && ! gv_nif_valido_es( $nif ) ) {
		return 'El NIF, DNI o NIE no parece correcto. Revisa los números y la letra.';
	}
	if ( 'ES' !== $pais && strlen( $nif ) < 5 ) {
		return 'Escribe tu número de documento de identidad completo.';
	}
	return '';
}

// Campo en los datos de facturación (pago y Mi cuenta > Direcciones), justo después del nombre.
add_filter( 'woocommerce_billing_fields', function ( $fields ) {
	$fields['billing_nif'] = array(
		'label'       => 'NIF / DNI / NIE',
		'placeholder' => 'Para tu factura',
		'required'    => true,
		'class'       => array( 'form-row-wide' ),
		'priority'    => 25,
		'autocomplete'=> 'off',
	);
	return $fields;
} );

// Comprobación en el pago.
add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
	$e = gv_nif_error( $data['billing_nif'] ?? '', $data['billing_country'] ?? 'ES' );
	if ( $e ) {
		$errors->add( 'billing_nif', $e );
	}
}, 5, 2 );

// Comprobación al guardar la dirección en Mi cuenta.
add_action( 'woocommerce_after_save_address_validation', function ( $user_id, $load_address ) {
	if ( 'billing' !== $load_address || ! isset( $_POST['billing_nif'] ) ) {
		return;
	}
	$e = gv_nif_error( wp_unslash( $_POST['billing_nif'] ), sanitize_text_field( wp_unslash( $_POST['billing_country'] ?? 'ES' ) ) );
	if ( $e ) {
		wc_add_notice( $e, 'error' );
	}
}, 10, 2 );

// Se guarda siempre normalizado.
add_action( 'woocommerce_checkout_create_order', function ( $order, $data ) {
	if ( ! empty( $data['billing_nif'] ) ) {
		$order->update_meta_data( '_billing_nif', gv_nif_normalizar( $data['billing_nif'] ) );
	}
}, 10, 2 );

// Escritorio: el NIF dentro de los datos de facturación del pedido (y editable).
add_filter( 'woocommerce_admin_billing_fields', function ( $fields ) {
	$nuevo = array();
	foreach ( $fields as $k => $v ) {
		$nuevo[ $k ] = $v;
		if ( 'last_name' === $k ) {
			$nuevo['nif'] = array( 'label' => 'NIF / DNI / NIE', 'show' => true );
		}
	}
	return $nuevo;
} );

// El NIF aparece en la dirección de facturación (correos, Mi cuenta, página de gracias, escritorio).
add_filter( 'woocommerce_order_formatted_billing_address', function ( $address, $order ) {
	$address['nif'] = $order->get_meta( '_billing_nif' );
	return $address;
}, 10, 2 );
add_filter( 'woocommerce_my_account_my_address_formatted_address', function ( $address, $customer_id, $type ) {
	if ( 'billing' === $type ) {
		$address['nif'] = get_user_meta( $customer_id, 'billing_nif', true );
	}
	return $address;
}, 10, 3 );
add_filter( 'woocommerce_formatted_address_replacements', function ( $replace, $args ) {
	$replace['{nif}'] = ! empty( $args['nif'] ) ? 'NIF: ' . $args['nif'] : '';
	return $replace;
}, 10, 2 );
add_filter( 'woocommerce_localisation_address_formats', function ( $formats ) {
	foreach ( $formats as $pais => $f ) {
		if ( false === strpos( $f, '{nif}' ) ) {
			$formats[ $pais ] = $f . "\n{nif}";
		}
	}
	return $formats;
} );
