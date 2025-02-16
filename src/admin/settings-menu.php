<?php
require_once(__DIR__."/gads-conversion-settings.php");
require_once(__DIR__."/meta-ads-conversion-settings.php");
require_once(__DIR__."/event-schema-settings.php");

class AFBP_Settings{
    public function __construct() {
		add_action( 'admin_menu', array( $this, 'afbp_settings_add_plugin_page' ) );
		//add_action( 'admin_init', array( $this, 'google_ads_conversions_page_init' ) );
		add_action('admin_enqueue_scripts', array( $this,'enqueue_styles') );
	}

	public function enqueue_styles(){
		wp_enqueue_style('afbp-admin',plugin_dir_url( "./afb-parade/build/admin" )."admin/style.css");
	}

	public function afbp_settings_add_plugin_page() {
		add_menu_page(
			'AFB Parade Settings', // page_title
			'AFB Parade', // menu_title
			'manage_options', // capability
			'afbp', // menu_slug
			array( $this, 'afbp_create_admin_page' ), // function
			'dashicons-megaphone', // icon_url
			80 // position
		);
	}

    public function afbp_create_admin_page(){
        ?>
		<div class="afb-admin">
        	<h1>Hey, y'all!</h1>
				<p>AFB Parade is a custom-developed plugin for Atlanta Freedom Bands.</p>
				<p>(But don't worry, it's <a href="https://github.com/ospirito/afb-parade" target="_blank">open-sourced</a> so you can freel free to use it!)</p>
			<h2>Features of AFB Parade</h2>
			<h3>Custom blocks</h3>
				<h4>Leader Bio Block</h4>
				<h4>Conditional Query Params</h4>
			<h3>Behind-the-scenes features</h3>
				<h4>Google Ads Conversions</h4>
		</div>
        <?php
    }
}

if ( is_admin() )
	$afbp_settings = new AFBP_Settings();
    $google_ads_conversions = new AFBP_GoogleAdsConversions();
	$meta_ads_conversions = new AFBP_MetaAdsConversions();
	$event_schema = new AFBP_EventSchemaSettings();
?>