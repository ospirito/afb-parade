<?php
/**
 * Plugin Name:       Parade
 * Plugin URI:        atlantafreedombands.com
 * Description:       Custom blocks for Atlanta Freedom Bands
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Oliver Spirito
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       afb-parade
 *
 * @package           aosp
 */

define('AFB_PARADE_PATH', plugin_dir_path(__FILE__));
define('AFB_PARADE_URL', plugin_dir_url(__FILE__));

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
require __DIR__ . '/build/blocks/conditional-query-params/conditionalShowHide.php';
require __DIR__ . '/build/admin/gads-conversion-settings.php';
require __DIR__ . '/build/admin/settings-menu.php';
require __DIR__ . '/build/bts/google-ads-conversion/init_gads_conversion.php';
require __DIR__ . '/build/bts/meta-ads-conversion/init_meta_ads_conversion.php';

// Shortlinks module
require __DIR__ . '/build/bts/shortlinks/shortlink-cpt.php';
require __DIR__ . '/build/bts/shortlinks/shortlink-api.php';
require __DIR__ . '/build/bts/shortlinks/shortlink-redirect.php';
require __DIR__ . '/build/admin-bar/shortlink-admin-bar.php';

function afb_shortlink_enqueue_block_editor_assets()
{
	wp_enqueue_script(
		'afb-parade-shortlink-sidebar',
		plugins_url('build/plugins/shortlink-sidebar.js', __FILE__),
		array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-api-fetch'),
		file_exists(AFB_PARADE_PATH . 'build/plugins/shortlink-sidebar.js') ? filemtime(AFB_PARADE_PATH . 'build/plugins/shortlink-sidebar.js') : '1.0.0'
	);

	$shortlink_options = get_option('afbp_shortlink_option_name');
	$base_domain = isset($shortlink_options['base_domain']) ? $shortlink_options['base_domain'] : site_url('/s/');
	$default_params_raw = isset($shortlink_options['default_params']) ? $shortlink_options['default_params'] : 'utm_source,utm_medium,utm_campaign';

	$default_params = array();
	if (!empty($default_params_raw)) {
		$keys = explode(',', $default_params_raw);
		foreach ($keys as $key) {
			$default_params[] = array('key' => trim($key), 'value' => '');
		}
	}

	wp_localize_script('afb-parade-shortlink-sidebar', 'AFBShortlinkData', array(
		'baseDomain' => esc_url($base_domain),
		'defaultParams' => $default_params
	));
}
add_action('enqueue_block_editor_assets', 'afb_shortlink_enqueue_block_editor_assets');


function aosp_afb_parade_block_init()
{
	$staticBlocksToRegister = ["leader-bio"];
	$dynamicBlocksToRegister = ["conditional-query-params" => 'conditional_query_param_callback'];

	foreach ($staticBlocksToRegister as $dir) {
		register_block_type(__DIR__ . '/build/blocks/' . $dir);
	}

	foreach ($dynamicBlocksToRegister as $dir => $callback) {
		register_block_type(__DIR__ . '/build/blocks/' . $dir, array("render_callback" => $callback));
	}
}
add_action('init', 'aosp_afb_parade_block_init');

// function add_settings_page(){
// 	add_menu_page('AFB Parade', 'AFB Parade', 'manage_options', 'afbp','afb_parade_render_settings');
// 	add_submenu_page('afbp','Google Ads Conversions', 'Google Ads Conversions', 'activate_plugins', 'afbpconversions', 'afb_parade_render_gads_settings');
// }
// add_action('admin_menu', 'add_settings_page');

add_action('wp_enqueue_scripts', 'conditional_enqueue_google_ads');
add_action('wp_enqueue_scripts', 'conditional_enqueue_meta_ads');

