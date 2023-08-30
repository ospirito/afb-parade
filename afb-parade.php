<?php
/**
 * Plugin Name:       Parade
 * Plugin URI:        atlantafreedombands.com
 * Description:       Custom blocks for Atlanta Freedom Bands
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.0.4
 * Author:            Oliver Spirito
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       afb-parade
 *
 * @package           aosp
 */

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
require __DIR__.'/build/conditionalQueryParams/conditionalShowHide.php';

function aosp_afb_parade_block_init() {
	$staticBlocksToRegister = ["leaderBio"];
	$dynamicBlocksToRegister = ["conditionalQueryParams" => 'conditional_query_param_callback'];

	foreach( $staticBlocksToRegister as $dir){
		register_block_type( __DIR__ . '/build/'.$dir );
	}

	foreach( $dynamicBlocksToRegister as $dir => $callback){
		register_block_type( __DIR__ . '/build/'.$dir, array("render_callback" => $callback));
	}
}
add_action( 'init', 'aosp_afb_parade_block_init' );
