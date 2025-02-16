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
require __DIR__.'/build/bts/event-schema/event-schema.php';


function aosp_afb_parade_block_init() {
	$staticBlocksToRegister = ["leader-bio"];
	$dynamicBlocksToRegister = ["conditional-query-params" => 'conditional_query_param_callback'];

	foreach( $staticBlocksToRegister as $dir){
		register_block_type( __DIR__ . '/build/blocks/'.$dir );
	}

	foreach( $dynamicBlocksToRegister as $dir => $callback){
		register_block_type( __DIR__ . '/build/blocks/'.$dir, array("render_callback" => $callback));
	}

	//if(get_post_type() == afbp\EventManager::EVENT_POST){
		add_action( "wp_footer", 'afbp\createSchema');
	//}
}
add_action( 'init', 'aosp_afb_parade_block_init' );

// function add_settings_page(){
// 	add_menu_page('AFB Parade', 'AFB Parade', 'manage_options', 'afbp','afb_parade_render_settings');
// 	add_submenu_page('afbp','Google Ads Conversions', 'Google Ads Conversions', 'activate_plugins', 'afbpconversions', 'afb_parade_render_gads_settings');
// }
// add_action('admin_menu', 'add_settings_page');

add_action('wp_enqueue_scripts', 'conditional_enqueue_google_ads');
add_action('wp_enqueue_scripts', 'conditional_enqueue_meta_ads');

add_action( 'add_meta_boxes', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		// Don't display the metabox to users who can't manage options
		return;
	}

	add_meta_box( 'wpcode-view-post-meta', 'Post Meta', function () {
		$custom_fields = get_post_meta( get_the_ID() );
		?>
		<table style="width: 100%; text-align: left;">
			<thead>
			<tr>
				<th style="width: 28%">Meta Key</th>
				<th style="width: 70%">Value</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $custom_fields as $key => $values ) {
				?>
				<?php foreach ( $values as $value ) { ?>
					<tr>
						<td><?php echo esc_html( $key ); ?></td>
						<td><code><?php echo esc_html( $value ); ?></code></td>
					</tr>
				<?php } ?>
			<?php } ?>
			</tbody>
		</table>
		<?php
	}, get_post_type() );
} );

