<?php
/**
 * Register the AFB Shortlink Custom Post Type
 */

function afb_register_shortlink_cpt() {
	$args = array(
		'label'               => 'Shortlinks',
		'public'              => false, // We don't want these to be queryable natively except via our API
		'publicly_queryable'  => false,
		'show_ui'             => true, // Show in admin menu just in case admin wants to see them
		'show_in_menu'        => true,
		'show_in_rest'        => true, // Enable REST API
		'rest_base'           => 'afb-shortlinks',
		'supports'            => array( 'title' ),
		'capability_type'     => 'post',
		'has_archive'         => false,
		'rewrite'             => false,
	);

	register_post_type( 'afb_shortlink', $args );

	// Register metadata
	register_post_meta( 'afb_shortlink', '_target_page_id', array(
		'type'         => 'integer',
		'description'  => 'Target post or page ID',
		'single'       => true,
		'show_in_rest' => true,
	) );

	register_post_meta( 'afb_shortlink', '_query_params', array(
		'type'         => 'string',
		'description'  => 'Query parameters to append to the URL',
		'single'       => true,
		'show_in_rest' => true,
	) );
}
add_action( 'init', 'afb_register_shortlink_cpt' );
