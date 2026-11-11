<?php
/**
 * Los productos son formaciones: nombre en el escritorio y en las direcciones.
 *
 * - En el escritorio, "Productos" pasa a llamarse "Formaciones".
 * - Las fichas viven en /formacion/<curso>/ en lugar de /producto/<curso>/.
 * - Las direcciones antiguas /producto/... redirigen (301) a las nuevas.
 */
defined( 'ABSPATH' ) || exit;

const GV_BASE_FORMACION = 'formacion';

add_filter( 'woocommerce_register_post_type_product', function ( $args ) {
	$args['labels'] = array_merge( (array) ( $args['labels'] ?? array() ), array(
		'name'                  => 'Formaciones',
		'singular_name'         => 'Formación',
		'all_items'             => 'Todas las formaciones',
		'menu_name'             => 'Formaciones',
		'add_new'               => 'Añadir formación',
		'add_new_item'          => 'Añadir formación',
		'edit'                  => 'Editar',
		'edit_item'             => 'Editar formación',
		'new_item'              => 'Nueva formación',
		'view_item'             => 'Ver formación',
		'view_items'            => 'Ver formaciones',
		'search_items'          => 'Buscar formaciones',
		'not_found'             => 'No hay formaciones',
		'not_found_in_trash'    => 'No hay formaciones en la papelera',
		'parent'                => 'Formación superior',
		'featured_image'        => 'Imagen de la formación',
		'set_featured_image'    => 'Poner imagen de la formación',
		'remove_featured_image' => 'Quitar imagen de la formación',
		'use_featured_image'    => 'Usar como imagen de la formación',
		'insert_into_item'      => 'Insertar en la formación',
		'uploaded_to_this_item' => 'Subido a esta formación',
		'filter_items_list'     => 'Filtrar formaciones',
		'items_list_navigation' => 'Navegación de formaciones',
		'items_list'            => 'Lista de formaciones',
		'item_link'             => 'Enlace a la formación',
		'item_published'        => 'Formación publicada.',
		'item_updated'          => 'Formación actualizada.',
	) );
	return $args;
} );

// La base de las direcciones es un ajuste de WooCommerce: se fija a /formacion/ una sola vez.
add_action( 'init', function () {
	$p = (array) get_option( 'woocommerce_permalinks', array() );
	if ( ( $p['product_base'] ?? '' ) !== '/' . GV_BASE_FORMACION ) {
		$p['product_base'] = '/' . GV_BASE_FORMACION;
		update_option( 'woocommerce_permalinks', $p );
		update_option( 'gv_flush_rewrite', 1 );
	}
}, 5 );
add_action( 'init', function () {
	if ( get_option( 'gv_flush_rewrite' ) ) {
		flush_rewrite_rules( false );
		delete_option( 'gv_flush_rewrite' );
	}
}, 99 );

// Direcciones antiguas: /producto/<slug>/ -> /formacion/<slug>/ (301).
add_action( 'template_redirect', function () {
	$path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	if ( ! $path || ! preg_match( '#^/producto/(.+)$#', $path, $m ) ) {
		return;
	}
	$query = wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY );
	wp_safe_redirect( home_url( '/' . GV_BASE_FORMACION . '/' . $m[1] ) . ( $query ? '?' . $query : '' ), 301 );
	exit;
}, 1 );
