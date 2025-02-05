<?php
/**
 * Plugin Name:       Parade
 * Plugin URI:        atlantafreedombands.com
 * Description:       Custom blocks for Atlanta Freedom Bands
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.0.5
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
require __DIR__.'/build/blocks/conditional-query-params/conditionalShowHide.php';
require __DIR__.'/build/admin/gads-conversion-settings.php';
require __DIR__.'/build/admin/settings-menu.php';
require __DIR__.'/build/bts/google-ads-conversion/init_gads_conversion.php';
require __DIR__.'/build/bts/meta-ads-conversion/init_meta_ads_conversion.php';

function aosp_afb_parade_block_init() {
	$staticBlocksToRegister = ["leader-bio"];
	$dynamicBlocksToRegister = ["conditional-query-params" => 'conditional_query_param_callback'];

	foreach( $staticBlocksToRegister as $dir){
		register_block_type( __DIR__ . '/build/blocks/'.$dir );
	}

	foreach( $dynamicBlocksToRegister as $dir => $callback){
		register_block_type( __DIR__ . '/build/blocks/'.$dir, array("render_callback" => $callback));
	}
}
add_action( 'init', 'aosp_afb_parade_block_init' );

// function add_settings_page(){
// 	add_menu_page('AFB Parade', 'AFB Parade', 'manage_options', 'afbp','afb_parade_render_settings');
// 	add_submenu_page('afbp','Google Ads Conversions', 'Google Ads Conversions', 'activate_plugins', 'afbpconversions', 'afb_parade_render_gads_settings');
// }
// add_action('admin_menu', 'add_settings_page');

add_action('wp_enqueue_scripts', 'conditional_enqueue_google_ads');
add_action('wp_enqueue_scripts', 'conditional_enqueue_meta_ads');

