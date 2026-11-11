<?php
/**
 * Plazas a la vista.
 *
 * Público (catálogo y ficha): solo se enseña el número cuando quedan pocas (GV_UMBRAL_PLAZAS o menos),
 * que es cuando genera urgencia. Con muchas plazas libres no se muestra número: "quedan 56" no empuja a nadie.
 *
 * Equipo (escritorio): columna "Plazas" en el listado de Formaciones y recuadro en cada formación
 * con las plazas, la hora de la última sincronización y un botón "Sincronizar ahora".
 */
defined( 'ABSPATH' ) || exit;

const GV_UMBRAL_PLAZAS = 10;

/** Texto público de plazas. Cadena vacía = no mostrar nada. */
function gv_texto_plazas( $product ) {
	if ( ! $product || ! get_post_meta( $product->get_id(), 'amelia_event_id', true ) || ! $product->get_manage_stock() ) {
		return '';
	}
	if ( ! $product->is_in_stock() ) {
		return 'Completo';
	}
	$n = (int) $product->get_stock_quantity();
	if ( $n > GV_UMBRAL_PLAZAS ) {
		return '';
	}
	if ( 1 === $n ) {
		return '¡Última plaza!';
	}
	return $n <= 3 ? sprintf( '¡Últimas %d plazas!', $n ) : sprintf( 'Quedan %d plazas', $n );
}

// Ficha: sustituye el texto de existencias de WooCommerce.
add_filter( 'woocommerce_get_availability_text', function ( $text, $product ) {
	if ( ! get_post_meta( $product->get_id(), 'amelia_event_id', true ) ) {
		return $text;
	}
	return gv_texto_plazas( $product );
}, 20, 2 );

// Catálogo: etiqueta bajo el título de cada formación.
add_action( 'woocommerce_after_shop_loop_item_title', function () {
	global $product;
	$t = gv_texto_plazas( $product );
	if ( $t ) {
		$clase = $product->is_in_stock() ? 'gv-plazas' : 'gv-plazas gv-plazas--completo';
		echo '<span class="' . esc_attr( $clase ) . '">' . esc_html( $t ) . '</span>';
	}
}, 15 );

add_action( 'wp_head', function () {
	echo '<style>.gv-plazas{display:inline-block;margin:6px 0 4px;padding:3px 10px;border-radius:999px;background:#FDE8D7;color:#9A3D0B;font-size:13px;font-weight:600}.gv-plazas--completo{background:#E9E9EE;color:#55556A}.stock.in-stock{color:#9A3D0B;font-weight:600}</style>';
} );

/* ---------- Escritorio ---------- */

function gv_info_plazas_admin( $id ) {
	if ( ! get_post_meta( $id, 'amelia_event_id', true ) ) {
		return '—';
	}
	$p = wc_get_product( $id );
	if ( function_exists( 'gv_curso_ha_empezado' ) && gv_curso_ha_empezado( $id ) ) {
		return 'Cerrada (ya empezó)';
	}
	$n    = $p && $p->get_manage_stock() ? (int) $p->get_stock_quantity() : null;
	$sync = get_post_meta( $id, '_gv_plazas_sincronizadas', true );
	$txt  = null === $n ? 'Sin sincronizar' : ( $n > 0 ? sprintf( '%d libres', $n ) : 'Completo' );
	if ( $sync ) {
		$txt .= '<br><small style="color:#787c82">Sincronizado ' . esc_html( mysql2date( 'd/m H:i', $sync ) ) . '</small>';
	}
	return $txt;
}

add_filter( 'manage_edit-product_columns', function ( $cols ) {
	$nuevo = array();
	foreach ( $cols as $k => $v ) {
		$nuevo[ $k ] = $v;
		if ( 'name' === $k ) {
			$nuevo['gv_plazas'] = 'Plazas';
		}
	}
	return $nuevo;
} );
add_action( 'manage_product_posts_custom_column', function ( $col, $id ) {
	if ( 'gv_plazas' === $col ) {
		echo wp_kses_post( gv_info_plazas_admin( $id ) );
	}
}, 10, 2 );

add_action( 'add_meta_boxes_product', function () {
	add_meta_box( 'gv_plazas', 'Plazas en Técnica Estructural', function ( $post ) {
		echo '<p style="font-size:14px">' . wp_kses_post( gv_info_plazas_admin( $post->ID ) ) . '</p>';
		if ( get_post_meta( $post->ID, 'amelia_event_id', true ) ) {
			$url = wp_nonce_url( admin_url( 'admin-post.php?action=gv_sincronizar_plazas&id=' . $post->ID ), 'gv_sync_' . $post->ID );
			echo '<p><a class="button" href="' . esc_url( $url ) . '">Sincronizar ahora</a></p>';
			echo '<p style="color:#787c82">Se sincroniza sola cada hora y antes de cada cobro.</p>';
		} else {
			echo '<p style="color:#787c82">Esta formación no está conectada a un curso de Técnica Estructural.</p>';
		}
	}, 'product', 'side', 'high' );
} );

add_action( 'admin_post_gv_sincronizar_plazas', function () {
	$id = absint( $_GET['id'] ?? 0 );
	if ( ! $id || ! current_user_can( 'edit_product', $id ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'gv_sync_' . $id ) ) {
		wp_die( 'No permitido.' );
	}
	$libres = gv_plazas_libres( $id );
	if ( null !== $libres ) {
		$p = wc_get_product( $id );
		$p->set_manage_stock( true );
		$p->set_backorders( 'no' );
		$p->set_stock_quantity( $libres );
		$p->save();
		update_post_meta( $id, '_gv_plazas_sincronizadas', current_time( 'mysql' ) );
	}
	wp_safe_redirect( get_edit_post_link( $id, 'url' ) );
	exit;
} );
