<?php
/**
 * Plugin Name: Gemma Vila · Entorno de pruebas
 * Description: Aísla la copia de pruebas de la academia. Solo actúa si wp-config.php define GV_ENTORNO_PRUEBAS.
 * Version: 1.0.0
 * Author: Francisco Cholbi
 *
 * Se instala como mu-plugin (wp-content/mu-plugins/) SOLO en pruebas-academia.gemmavilaaccion.es.
 * - Cerrada al público: solo entra quien ha iniciado sesión o tiene el enlace de acceso
 *   (?gv_pruebas=<GV_PRUEBAS_TOKEN>, definido en wp-config.php de la copia; nunca en el repositorio).
 * - Todos los correos se desvían a GV_PRUEBAS_EMAIL (por defecto, Francisco). Ningún cliente real recibe nada.
 * - Fuera de Google (noindex) y aviso visible "ENTORNO DE PRUEBAS".
 * - Stripe siempre en modo pruebas.
 */
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'GV_ENTORNO_PRUEBAS' ) || ! GV_ENTORNO_PRUEBAS ) {
	return;
}

if ( ! defined( 'GV_PRUEBAS_EMAIL' ) ) {
	define( 'GV_PRUEBAS_EMAIL', 'francisco.cholbi@gmail.com' );
}

/* ---------- 1. Correos: todos a una sola persona ---------- */
add_filter( 'wp_mail', function ( $args ) {
	$original        = is_array( $args['to'] ) ? implode( ', ', $args['to'] ) : (string) $args['to'];
	$args['to']      = GV_PRUEBAS_EMAIL;
	$args['subject'] = '[PRUEBAS] ' . $args['subject'] . ' (para: ' . $original . ')';
	$headers         = is_array( $args['headers'] ) ? $args['headers'] : explode( "\n", (string) $args['headers'] );
	$args['headers'] = array_values( array_filter( $headers, function ( $h ) {
		return ! preg_match( '/^\s*(cc|bcc)\s*:/i', $h );
	} ) );
	return $args;
}, 999 );

/* ---------- 2. Acceso: solo equipo o enlace con clave ---------- */
add_action( 'init', function () {
	$token = defined( 'GV_PRUEBAS_TOKEN' ) ? GV_PRUEBAS_TOKEN : '';
	if ( $token && isset( $_GET['gv_pruebas'] ) && hash_equals( $token, (string) $_GET['gv_pruebas'] ) ) {
		setcookie( 'gv_pruebas', hash( 'sha256', $token ), time() + 30 * DAY_IN_SECONDS, '/', '', true, true );
		$_COOKIE['gv_pruebas'] = hash( 'sha256', $token );
	}
}, 0 );

add_action( 'template_redirect', function () {
	if ( is_user_logged_in() ) {
		return;
	}
	$token = defined( 'GV_PRUEBAS_TOKEN' ) ? GV_PRUEBAS_TOKEN : '';
	if ( $token && isset( $_COOKIE['gv_pruebas'] ) && hash_equals( hash( 'sha256', $token ), (string) $_COOKIE['gv_pruebas'] ) ) {
		return;
	}
	status_header( 403 );
	nocache_headers();
	echo '<!doctype html><meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><title>Entorno de pruebas</title>'
		. '<div style="font-family:sans-serif;max-width:520px;margin:15vh auto;text-align:center;color:#221D3F">'
		. '<h1 style="color:#34508A">Entorno de pruebas</h1><p>Esta es una copia privada de la academia de Gemma Vila.</p>'
		. '<p><a href="https://academia.gemmavilaaccion.es/">Ir a la academia</a> · <a href="' . esc_url( wp_login_url() ) . '">Acceso del equipo</a></p></div>';
	exit;
}, 0 );

/* ---------- 3. Fuera de Google ---------- */
add_filter( 'pre_option_blog_public', function () {
	return '0';
} );
add_action( 'send_headers', function () {
	header( 'X-Robots-Tag: noindex, nofollow', true );
} );

/* ---------- 4. Stripe nunca cobra de verdad ---------- */
add_filter( 'option_woocommerce_stripe_settings', function ( $s ) {
	if ( is_array( $s ) ) {
		$s['testmode'] = 'yes';
	}
	return $s;
} );

/* ---------- 5. Aviso visible ---------- */
$gv_aviso = function () {
	echo '<div style="position:fixed;bottom:0;left:0;right:0;z-index:999999;background:#E58AC6;color:#221D3F;text-align:center;font:600 13px/2.2 sans-serif">ENTORNO DE PRUEBAS · no es la academia real · los correos solo llegan a ' . esc_html( GV_PRUEBAS_EMAIL ) . '</div>';
};
add_action( 'wp_footer', $gv_aviso );
add_action( 'admin_footer', $gv_aviso );
