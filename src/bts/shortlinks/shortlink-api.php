<?php
/**
 * Register Custom REST API Endpoints for Shortlinks
 */

function afb_register_shortlink_api() {
	register_rest_route( 'afb-parade/v1', '/shortlinks/(?P<post_id>\d+)', array(
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'afb_get_shortlinks_for_post',
			'permission_callback' => 'afb_shortlink_permissions_check',
		),
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'afb_create_shortlink_for_post',
			'permission_callback' => 'afb_shortlink_permissions_check',
		)
	) );

	register_rest_route( 'afb-parade/v1', '/shortlinks', array(
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'afb_get_all_shortlinks',
			'permission_callback' => function() { return current_user_can('manage_options'); },
		),
		array(
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => 'afb_delete_shortlink',
			'permission_callback' => 'afb_shortlink_delete_permissions_check',
		)
	) );

	register_rest_route( 'afb-parade/v1', '/shortlinks/update/(?P<id>\d+)', array(
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'afb_update_shortlink',
			'permission_callback' => 'afb_shortlink_update_permissions_check',
		)
	) );
}
add_action( 'rest_api_init', 'afb_register_shortlink_api' );

function afb_shortlink_permissions_check( $request ) {
	$post_id = $request['post_id'];
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot edit this post.', 'afb-parade' ), array( 'status' => 401 ) );
	}
	return true;
}

function afb_shortlink_delete_permissions_check( $request ) {
	$id = $request->get_param( 'id' );
	if ( ! $id ) {
		return new WP_Error( 'rest_missing_id', 'Missing shortlink ID.', array( 'status' => 400 ) );
	}
	
	$shortlink = get_post( $id );
	if ( ! $shortlink || $shortlink->post_type !== 'afb_shortlink' ) {
		return new WP_Error( 'rest_invalid_id', 'Invalid shortlink ID.', array( 'status' => 404 ) );
	}

	$target_post_id = get_post_meta( $id, '_target_page_id', true );
	if ( ! current_user_can( 'edit_post', $target_post_id ) ) {
		return new WP_Error( 'rest_forbidden', 'You cannot delete shortlinks for this post.', array( 'status' => 401 ) );
	}

	return true;
}

function afb_get_shortlinks_for_post( $request ) {
	$post_id = $request['post_id'];
	
	$args = array(
		'post_type'      => 'afb_shortlink',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'meta_query'     => array(
			array(
				'key'     => '_target_page_id',
				'value'   => $post_id,
				'compare' => '='
			)
		)
	);

	$query = new WP_Query( $args );
	$shortlinks = array();

	foreach ( $query->posts as $post ) {
		$shortlinks[] = array(
			'id'           => $post->ID,
			'slug'         => $post->post_title,
			'query_params' => get_post_meta( $post->ID, '_query_params', true ),
		);
	}

	return rest_ensure_response( $shortlinks );
}

function afb_create_shortlink_for_post( $request ) {
	$post_id = $request['post_id'];
	$slug = $request->get_param( 'slug' );
	$query_params = $request->get_param( 'query_params' );

	if ( empty( $slug ) ) {
		$slug = substr( str_shuffle( 'abcdefghijklmnopqrstuvwxyz0123456789' ), 0, 5 );
	}

	$slug = sanitize_text_field( $slug );
	$query_params = sanitize_text_field( $query_params );

	// Check if slug exists
	$existing = get_page_by_title( $slug, OBJECT, 'afb_shortlink' );
	if ( $existing ) {
		return new WP_Error( 'slug_exists', 'This short link slug already exists.', array( 'status' => 400 ) );
	}

	$shortlink_id = wp_insert_post( array(
		'post_title'  => $slug,
		'post_type'   => 'afb_shortlink',
		'post_status' => 'publish'
	) );

	if ( is_wp_error( $shortlink_id ) ) {
		return $shortlink_id;
	}

	update_post_meta( $shortlink_id, '_target_page_id', $post_id );
	update_post_meta( $shortlink_id, '_query_params', ltrim( $query_params, '?' ) );

	return rest_ensure_response( array(
		'id'           => $shortlink_id,
		'slug'         => $slug,
		'query_params' => $query_params
	) );
}

function afb_delete_shortlink( $request ) {
	$id = $request->get_param( 'id' );
	$result = wp_delete_post( $id, true );
	if ( ! $result ) {
		return new WP_Error( 'delete_failed', 'Failed to delete shortlink.', array( 'status' => 500 ) );
	}
	return rest_ensure_response( array( 'success' => true ) );
}

function afb_update_shortlink( $request ) {
	$id = $request['id'];
	$slug = $request->get_param( 'slug' );
	$query_params = $request->get_param( 'query_params' );

	if ( empty( $slug ) ) {
		return new WP_Error( 'missing_slug', 'Slug cannot be empty.', array( 'status' => 400 ) );
	}

	$slug = sanitize_text_field( $slug );
	$query_params = sanitize_text_field( $query_params );

	// Check if slug exists elsewhere
	$existing = get_page_by_title( $slug, OBJECT, 'afb_shortlink' );
	if ( $existing && (int) $existing->ID !== (int) $id ) {
		return new WP_Error( 'slug_exists', 'This short link slug already exists.', array( 'status' => 400 ) );
	}

	wp_update_post( array(
		'ID'         => $id,
		'post_title' => $slug,
	) );

	update_post_meta( $id, '_query_params', ltrim( $query_params, '?' ) );

	return rest_ensure_response( array(
		'id'           => $id,
		'slug'         => $slug,
		'query_params' => $query_params
	) );
}

function afb_get_all_shortlinks() {
	$args = array(
		'post_type'      => 'afb_shortlink',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	);

	$query = new WP_Query( $args );
	$shortlinks = array();

	foreach ( $query->posts as $post ) {
		$target_post_id = get_post_meta( $post->ID, '_target_page_id', true );
		$shortlinks[] = array(
			'id'              => $post->ID,
			'slug'            => $post->post_title,
			'query_params'    => get_post_meta( $post->ID, '_query_params', true ),
			'target_post_id'  => $target_post_id,
			'target_post_title' => get_the_title( $target_post_id ),
			'target_url'      => get_permalink( $target_post_id ),
		);
	}

	return rest_ensure_response( $shortlinks );
}

function afb_shortlink_update_permissions_check( $request ) {
	$id = $request['id'];
	$shortlink = get_post( $id );
	if ( ! $shortlink || $shortlink->post_type !== 'afb_shortlink' ) {
		return new WP_Error( 'rest_invalid_id', 'Invalid shortlink ID.', array( 'status' => 404 ) );
	}

	$target_post_id = get_post_meta( $id, '_target_page_id', true );
	if ( ! current_user_can( 'edit_post', $target_post_id ) ) {
		return new WP_Error( 'rest_forbidden', 'You cannot update shortlinks for this post.', array( 'status' => 401 ) );
	}

	return true;
}
