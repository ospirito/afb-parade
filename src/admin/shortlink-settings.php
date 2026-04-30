<?php

class AFBP_ShortlinkSettings {
	private $shortlink_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'shortlink_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'shortlink_page_init' ) );
	}

	public function shortlink_add_plugin_page() {
		add_submenu_page('afbp',
			'URL Shortener', // page_title
			'URL Shortener', // menu_title
			'manage_options', // capability
			'afbp-shortlinks', // menu_slug
			array( $this, 'shortlink_create_admin_page' ), // function
			20 // position
		);
	}

	public function shortlink_create_admin_page() {
		$this->shortlink_options = get_option( 'afbp_shortlink_option_name' ); ?>

		<div class="wrap">
			<h2>URL Shortener Settings</h2>
			<p>Configure how shortlinks are generated and displayed.</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'shortlink_option_group' );
					do_settings_sections( 'shortlink-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function shortlink_page_init() {
		register_setting(
			'shortlink_option_group', // option_group
			'afbp_shortlink_option_name', // option_name
			array(
				'sanitize_callback' => array($this, 'shortlink_sanitize'),
				'default' => array(
					'base_domain' => site_url( '/s/' )
				)
			)
		);

		add_settings_section(
			'afbp_shortlink_basic_config', // id
			'General Settings', // title
			array( $this, 'shortlink_section_info' ), // callback
			'shortlink-admin', // page
		);

		add_settings_field(
			'base_domain', // id
			'Base Domain', // title
			array( $this, 'base_domain_callback' ), // callback
			'shortlink-admin', // page
			'afbp_shortlink_basic_config' // section
		);

		add_settings_field(
			'default_params', // id
			'Default Query Parameters', // title
			array( $this, 'default_params_callback' ), // callback
			'shortlink-admin', // page
			'afbp_shortlink_basic_config' // section
		);

		// Manager section
		add_settings_section(
			'afbp_shortlink_manager_section',
			'Manage Shortlinks',
			array( $this, 'shortlink_manager_info' ),
			'shortlink-admin'
		);
	}

	public function shortlink_manager_info() {
		echo '<div id="afb-shortlink-manager-root"></div>';
		
		wp_enqueue_script(
			'afb-shortlink-manager',
			plugins_url( 'build/shortlink-manager.js', dirname(__DIR__, 2) . '/afb-parade.php' ),
			array( 'wp-element', 'wp-components', 'wp-api-fetch' ),
			file_exists( AFB_PARADE_PATH . 'build/shortlink-manager.js' ) ? filemtime( AFB_PARADE_PATH . 'build/shortlink-manager.js' ) : '1.0.0',
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

		wp_localize_script( 'afb-shortlink-manager', 'AFBShortlinkData', array(
			'baseDomain' => esc_url( $base_domain ),
			'defaultParams' => $default_params
		) );
		
		wp_enqueue_style( 'wp-components' );
	}

	public function shortlink_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['base_domain'] ) ) {
			// Ensure it ends with a slash and is a valid format
			$domain = sanitize_text_field( $input['base_domain'] );
			$sanitary_values['base_domain'] = rtrim($domain, '/') . '/';
		}
		if ( isset( $input['default_params'] ) ) {
			$sanitary_values['default_params'] = sanitize_text_field( $input['default_params'] );
		}
		return $sanitary_values;
	}

	public function shortlink_section_info() {}

	public function base_domain_callback() {
		$value = isset( $this->shortlink_options['base_domain'] ) ? esc_url( $this->shortlink_options['base_domain']) : site_url( '/s/' );
		?>
		<input class="regular-text" type="text" name="afbp_shortlink_option_name[base_domain]" id="base_domain" value="<?php echo $value ?>">
		<p class="description">The root domain where shortlinks are hosted. For example, <code>https://pride.band/</code> or <code><?php echo site_url( '/s/' ); ?></code>.</p>
		<?php
	}

	public function default_params_callback() {
		$value = isset( $this->shortlink_options['default_params'] ) ? $this->shortlink_options['default_params'] : 'utm_source,utm_medium,utm_campaign';
		?>
		<input class="regular-text" type="text" name="afbp_shortlink_option_name[default_params]" id="default_params" value="<?php echo esc_attr( $value ) ?>">
		<p class="description">Comma-separated list of query parameter keys to pre-fill when creating a new shortlink (e.g. <code>utm_source,utm_medium,utm_campaign</code>).</p>
		<?php
	}
}
