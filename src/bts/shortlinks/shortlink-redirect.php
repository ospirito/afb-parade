<?php
/**
 * Shortlink Redirect Hooks
 */

// Register Rewrite Rule
function afb_shortlink_rewrite_rule() {
	add_rewrite_rule( '^s/([^/]+)/?$', 'index.php?afb_shortlink_slug=$matches[1]', 'top' );
}
add_action( 'init', 'afb_shortlink_rewrite_rule' );

// Register Query Var
function afb_shortlink_query_vars( $vars ) {
	$vars[] = 'afb_shortlink_slug';
	return $vars;
}
add_filter( 'query_vars', 'afb_shortlink_query_vars' );

// Handle Redirect
function afb_shortlink_template_redirect() {
	$slug = get_query_var( 'afb_shortlink_slug' );
	
	if ( ! empty( $slug ) ) {
		$args = array(
			'name'        => $slug,
			'post_type'   => 'afb_shortlink',
			'post_status' => 'publish',
			'numberposts' => 1
		);
		$shortlinks = get_posts( $args );

		if ( $shortlinks ) {
			$shortlink = $shortlinks[0];
			$target_id = get_post_meta( $shortlink->ID, '_target_page_id', true );
			$query_params = get_post_meta( $shortlink->ID, '_query_params', true );
			
			if ( $target_id ) {
				$redirect_url = get_permalink( $target_id );
				if ( ! empty( $query_params ) ) {
					$redirect_url .= ( parse_url( $redirect_url, PHP_URL_QUERY ) ? '&' : '?' ) . $query_params;
				}
				wp_redirect( $redirect_url, 301 );
				exit;
			}
		}

		// If shortlink not found, maybe redirect to 404 or home.
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}
add_action( 'template_redirect', 'afb_shortlink_template_redirect' );

// Activation Hook to flush rewrite rules
function afb_shortlink_flush_rules() {
	afb_shortlink_rewrite_rule();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'afb_shortlink_flush_rules' );
