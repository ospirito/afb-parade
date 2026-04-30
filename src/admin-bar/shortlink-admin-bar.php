<?php
/**
 * Shortlink Admin Bar Integration
 */

function afb_shortlink_admin_bar_node( $wp_admin_bar ) {
	if ( ! is_singular() ) {
		return;
	}

	$post_id = get_the_ID();
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$args = array(
		'id'    => 'afb-shortlink-btn',
		'title' => 'Generate Shortlink',
		'href'  => '#',
		'meta'  => array(
			'class' => 'afb-shortlink-admin-btn',
			'title' => 'Generate a shortlink for this page',
		),
	);
	$wp_admin_bar->add_node( $args );
}
add_action( 'admin_bar_menu', 'afb_shortlink_admin_bar_node', 100 );

function afb_shortlink_enqueue_admin_bar_scripts() {
	if ( ! is_admin_bar_showing() || ! is_singular() ) {
		return;
	}

	$post_id = get_the_ID();
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	
	wp_enqueue_style( 'wp-components' );
	wp_enqueue_script(
		'afb-shortlink-modal',
		plugins_url( 'build/admin-bar/shortlink-modal.js', dirname(__DIR__, 2) . '/afb-parade.php' ),
		array( 'wp-element', 'wp-components', 'wp-api-fetch' ),
		file_exists( AFB_PARADE_PATH . 'build/admin-bar/shortlink-modal.js' ) ? filemtime( AFB_PARADE_PATH . 'build/admin-bar/shortlink-modal.js' ) : '1.0.0',
		true
	);
	
	$shortlink_options = get_option( 'afbp_shortlink_option_name' );
	$base_domain = isset( $shortlink_options['base_domain'] ) ? $shortlink_options['base_domain'] : site_url( '/s/' );
	$default_params_raw = isset( $shortlink_options['default_params'] ) ? $shortlink_options['default_params'] : 'utm_source,utm_medium,utm_campaign';
	
	$default_params = array();
	if ( ! empty( $default_params_raw ) ) {
		$keys = explode( ',', $default_params_raw );
		foreach ( $keys as $key ) {
			$default_params[] = array( 'key' => trim( $key ), 'value' => '' );
		}
	}

	wp_localize_script( 'afb-shortlink-modal', 'AFBShortlinkData', array(
		'postId' => $post_id,
		'nonce'  => wp_create_nonce( 'wp_rest' ),
		'apiUrl' => esc_url_raw( rest_url( 'afb-parade/v1/shortlinks' ) ),
		'baseDomain' => esc_url( $base_domain ),
		'defaultParams' => $default_params
	) );
}
add_action( 'wp_enqueue_scripts', 'afb_shortlink_enqueue_admin_bar_scripts' );
add_action( 'admin_enqueue_scripts', 'afb_shortlink_enqueue_admin_bar_scripts' );
