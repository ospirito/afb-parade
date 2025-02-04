<?php

/**
 * Generated by the WordPress Option Page generator
 * at http://jeremyhixon.com/wp-tools/option-page/
 */

class AFBP_MetaAdsConversions {
	private $meta_ads_conversions_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'meta_ads_conversions_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'meta_ads_conversions_page_init' ) );
	}

	public function meta_ads_conversions_add_plugin_page() {
		add_submenu_page('afbp',
			'Meta Ads Conversions', // page_title
			'Meta Ads Conversions', // menu_title
			'manage_options', // capability
			'afbp-meta-ads-conversions', // menu_slug
			array( $this, 'meta_ads_conversions_create_admin_page' ), // function
			'dashicons-megaphone', // icon_url
			80 // position
		);
	}

	public function meta_ads_conversions_create_admin_page() {
		$this->meta_ads_conversions_options = get_option( 'afbp_meta_ads_conversions_option_name' ); ?>

		<div class="wrap">
			<h2>Meta Ads Conversions</h2>
			<p>Record conversion events for Meta Ads</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'meta_ads_conversions_option_group' );
					do_settings_sections( 'meta-ads-conversions-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function meta_ads_conversions_page_init() {
		register_setting(
			'meta_ads_conversions_option_group', // option_group
			'afbp_meta_ads_conversions_option_name', // option_name
			array(
            'sanitize_callback' => array($this, 'meta_ads_conversions_sanitize'), 
            'default' => Array(
                'record_meta_ads_conversions' => FALSE,
                'param_to_watch' => 'tt_order_id',
				'body_json' => '{ "currency":"USD", "#value":"~tt_order_value", "[]contentIds":"~tt_event_id" }'
            ))
		);

        //MARK: Settings sections
		add_settings_section(
			'afbp_meta_ads_conversion_basic_config', // id
			'Activation', // title
			array( $this, 'meta_ads_conversions_section_info' ), // callback
			'meta-ads-conversions-admin', // page
		);

        add_settings_section(
			'meta_ads_conversion_param_config', // id
			'Query Parameters', // title
			array( $this, 'meta_ads_conversions_section_info' ), // callback
			'meta-ads-conversions-admin' // page
		);

        //MARK: Settings fields
        //Basic settings
		add_settings_field(
			'record_meta_ads_conversions', // id
			'Record Meta Ads Conversions?', // title
			array( $this, 'record_meta_ads_conversions_callback' ), // callback
			'meta-ads-conversions-admin', // page
			'afbp_meta_ads_conversion_basic_config' // section
		);

		add_settings_field(
			'param_to_watch', // id
			'Conversion Trigger', // title
			array( $this, 'param_to_watch_callback' ), // callback
			'meta-ads-conversions-admin', // page
			'afbp_meta_ads_conversion_basic_config' // section
		);

        //Param settings
		add_settings_field(
			"body_json",
			"Conversion body",
			array($this, 'param_conversion_body'), //callback
			'meta-ads-conversions-admin',
			'meta_ads_conversion_param_config'
		);
	}

	public function meta_ads_conversions_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['record_meta_ads_conversions'] ) ) {
			$sanitary_values['record_meta_ads_conversions'] = $input['record_meta_ads_conversions'];
		}

		if ( isset( $input['param_to_watch'] ) ) {
			$sanitary_values['param_to_watch'] = sanitize_text_field( $input['param_to_watch'] );
		}

		if ( isset( $input['body_json'] ) ) {
			$sanitary_values['body_json'] = sanitize_text_field( $input['body_json'] );
		}

		return $sanitary_values;
	}

	public function meta_ads_conversions_section_info() {
		
	}

	public function record_meta_ads_conversions_callback() {
        $value = isset( $this->meta_ads_conversions_options['record_meta_ads_conversions'] ) && ($this->meta_ads_conversions_options['record_meta_ads_conversions'] === 'record_meta_ads_conversions') ? 'checked' : '';
		?>
        <input type="checkbox" name="afbp_meta_ads_conversions_option_name[record_meta_ads_conversions]" id="record_meta_ads_conversions" value="record_meta_ads_conversions" <?php echo $value ?>> <label for="record_meta_ads_conversions">Check to activate conversion reporting.</label>
		<?php
	}

	public function text_field_option($optionId){
        $value = isset( $this->meta_ads_conversions_options[$optionId] ) ? esc_attr( $this->meta_ads_conversions_options[$optionId]) : '';
        ?>
            <input class="regular-text" type="text" name="afbp_meta_ads_conversions_option_name[<?php echo $optionId ?>]" id="<?php echo $optionId ?>" value="<?php echo $value ?>">
        <?php

    }

	public function param_to_watch_callback() {
        ?>
        <div><p>Enter the name of a query parameter - the conversion logic will be run on any page that has this parameter appended to the URL.</p>
        <p>For example, if a conversion will land you on the page <code><?php echo site_url()?>/convert?transaction_id=12345</code>, enter <code>transaction_id</code> here.</p>
    	</div>
        <?php
		$this->text_field_option('param_to_watch');
	}


	public function param_conversion_body(){
		$value = isset( $this->meta_ads_conversions_options["body_json"] ) ? esc_attr( $this->meta_ads_conversions_options["body_json"]) : '';
		$value = str_replace(",", ",\n",$value); //purely for formatting
		$placeholder = '{&#10;"send_to":"[yourAdsIdHere]",&#10;"currency":"USD" //sends a static value of USD,&#10;"id":"~tt_order_id", //passes the value of tt_order_id from the URL&#10;"#value":"~tt_order_value", //passes the value of tt_order_value as a number&#10;"eventId":"~tt_event_id"&#10;}'
		?>
		<div>
			<p>Tell AFB Parade what to send in the Meta Ads conversion body.</p>
			<p>Write JSON below -- JSON keys should match the keys that need to be sent in the conversion.</p>
			<ul style="list-style:circle">
				<li>If the value associated with a key is static: just enter it in quotes, like <code>"send_to":"yourAdId"</code></li>
				<li>If the value associated with a key is provided in a query param: add a tilde (~) followed by the param's name, like <code>"id":"~tt_order_number"</code></li>
				<li>If the value assoiated with a key needs to be provided as a number, prefix the key with #, like <code>"#value":"~tt_order_value"</code>.</li>
				<li>If the value associated with a key needs to be provided in an array, prefix the key with [], like <code>"[]items":"[1,2,3,4]"</code>.</li>
			</ul>
			<i>All values will be sent as strings. If you need the value sent as a number, prefix the JSON key with a <code>#</code>. For example, <code>"#value":"~tt_123"</code>
				<textarea class="large-text" style="height:15em; resize:both;" name="afbp_meta_ads_conversions_option_name[body_json]" id="body_json" placeholder='<?php echo $placeholder ?>'><?php echo $value ?></textarea>
		<?php
	}

}
// if ( is_admin() )
// 	$google_ads_conversions = new AFBP_GoogleAdsConversions();

/* 
 * Retrieve this value with:
 * $google_ads_conversions_options = get_option( 'afbp_google_ads_conversions_option_name' ); // Array of All Options
 * $record_google_ads_conversions = $google_ads_conversions_options['record_google_ads_conversions']; // Record Google Ads Conversions?
 * $param_to_watch = $google_ads_conversions_options['param_to_watch']; // Conversion Trigger
 * $google_ads_id = $google_ads_conversions_options['google_ads_id']; // Google Ad Id
 * $param_conversion_unique_id = $google_ads_conversions_options['param_conversion_unique_id']; // Param for conversion unique ID
 * $param_conversion_value = $google_ads_conversions_options['param_conversion_value']; // Param for conversion value
 * $param_event_id = $google_ads_conversions_options['param_event_id']; // Param for event id
 */
