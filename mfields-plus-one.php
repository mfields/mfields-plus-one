<?php
/*
Plugin Name:    Plus One Button
Plugin URI:     http://wordpress.org/extend/plugins/plus-one-button/
Description:    Add a +1 button to your WordPress powered website.
Version:        0.1.2
Author:         Michael Fields
Author URI:     http://wordpress.mfields.org/
License:        GPLv2
*/

Mfields_Plus_One::init();

/**
 * Mfields Plus One
 *
 * @todo       Readme file.
 * @todo       Docs.
 * @todo       Release.
 *
 * @since      2011-06-02
 */
class Mfields_Plus_One {
	static $domain        = 'mfields_plus_one';
	static $url           = '';
	static $version       = '0.2';
	static $settings_page = '';

	/**
	 * Hook into WordPress.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function init() {
		register_activation_hook( __FILE__, array( __class__, 'activate' ) );
		add_action( 'init', array( __class__, 'load_textdomain' ) );
		add_action( 'init', array( __class__, 'register_enqueueables' ) );
		add_action( 'admin_menu', array( __class__, 'settings_menu' ),    10 );
		add_action( 'admin_menu', array( __class__, 'settings_enqueue' ), 11 );
		add_action( 'admin_init', array( __class__, 'settings_register' ) );
		add_action( 'template_redirect', array( __class__, 'integrate_singular' ) );
		add_action( 'template_redirect', array( __class__, 'integrate_multiple' ) );
		add_action( 'wp_footer', array( __class__, 'script_public' ) );
		add_action( 'mfields-plus-one-button', array( __class__, 'button_print' ) );
	}

	/**
	 * Activation.
	 *
	 * An entry in the options table will created when this plugin
	 * is activated in the event that it does not already exist.
	 *
	 * @since     2011-06-03
	 * @access    private
	 */
	function activate() {
		if ( false === get_option( 'mfields_plus_one' ) ) {
			add_option( 'mfields_plus_one', self::get_defaults() );
		}
	}

	/**
	 * Load Text Domain.
	 *
	 * @since      2011-06-03
	 * @access     private
	 */
	function load_textdomain() {
		load_plugin_textdomain( 'mfields_plus_one', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Register Scripts and Styles.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function register_enqueueables() {
		wp_register_script( 'mfields-plus-one-settings', plugin_dir_url( __FILE__ ) . 'style-settings-page.js', array( 'jquery' ), self::$version, true );
		wp_register_style( 'mfields-plus-one-settings', plugin_dir_url( __FILE__ ) . 'style-settings-page.css', array(), self::$version, 'screen' );
	}

	/**
	 * Dynamic hooks for Settings page.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function settings_enqueue() {
		add_action( 'admin_print_styles-' . self::$settings_page, array( __class__, 'style_settings_page' ) );
		add_action( 'admin_print_scripts-' . self::$settings_page, array( __class__, 'script_settings_page' ) );
	}

	/**
	 * Public Scripts.
	 *
	 * Prints the required script tag to a public-facing page.
	 * Language property will be added via stored settings.
	 * "Type" attribute will only be printed if user has explicity
	 * chosen "xhtml" as markup type. Unfortunately we were not able
	 * to use the WordPress script API to load this due to the presence
	 * of the language attribute.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function script_public() {
		$settings = self::get_settings();
		$atts = array(
			'src'  => 'https://apis.google.com/js/plusone.js',
			);

		if ( 'xhtml' == $settings['markup'] ) {
			$atts['type'] = 'text/javascript';
		}

		$attributes = '';
		foreach( $atts as $key => $value ) {
			$attributes .= ' ' . $key . '="' . $value . '"';
		}
		print "\n" . '<script' . $attributes . '>{lang: "' . esc_html( $settings['language'] ) . '"}</script>' . "\n";
	}

	/**
	 * Settings Page Styles.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function style_settings_page() {
		wp_enqueue_style( 'mfields-plus-one-settings' );
	}

	/**
	 * Settings Page Scripts.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function script_settings_page() {
		wp_enqueue_script( 'mfields-plus-one-settings' );
	}

	/**
	 * Integrate into singular templates.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function integrate_singular() {
		$settings = self::get_settings();

		if ( empty( $settings['singular'] ) ) {
			return;
		}

		if ( empty( $settings['post_types'] ) ) {
			return;
		}

		if ( ! is_singular( $settings['post_types'] ) ) {
			return;
		}

		foreach ( $settings['singular'] as $location ) {
			switch ( $location ) {
				case 'before_content' :
					add_filter( 'the_content', array( __class__, 'prepend' ) );
					break;
				case 'after_content' :
					add_filter( 'the_content', array( __class__, 'append' ) );
					break;
				default :
					continue;
					break;
			}
		}
	}

	/**
	 * Integrate into archive templates.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function integrate_multiple() {
		$settings = self::get_settings();

		if ( empty( $settings['multiple'] ) ) {
			return;
		}

		if ( is_singular() ) {
			return;
		}

		foreach ( $settings['multiple'] as $location ) {
			switch ( $location ) {
				case 'before_content' :
					add_filter( 'the_content', array( __class__, 'prepend_archive' ) );
					break;
				case 'after_content' :
					add_filter( 'the_content', array( __class__, 'append_archive' ) );
					break;
				case 'before_excerpt' :
					add_filter( 'the_excerpt', array( __class__, 'prepend_archive' ) );
					break;
				case 'after_excerpt' :
					add_filter( 'the_excerpt', array( __class__, 'append_archive' ) );
					break;
				default :
					continue;
					break;
			}
		}
	}

	/**
	 * Generate a button.
	 *
	 * Recognized Arguments
	 *
	 * count (string) - Either "true" or "false".
	 * markup (string) - Either "html" or "xhtml".
	 * size (string) - Either "small", "medium", "standard" or "tall".
	 * url (string) - The url to send to Google.
	 *
	 * @param      array        Arguments to customize the button.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function button( $args = array() ) {
		$url = esc_url( get_permalink() );
		if ( isset( $args['url'] ) ) {
			$url = esc_url( $args['url'] );
		}

		$atts = array(
			'html' => array(
				'class'      => 'g-plusone',
				'data-href'  => $url,
				),
			'xhtml' => array(
				'href'  => $url,
				),
			);

		$settings = self::get_settings();

		$size = $settings['size'];
		if ( isset( $args['size'] ) && array_key_exists( $args['size'], self::get_sizes() ) ) {
			$size = $args['size'];
		}

		$atts['xhtml']['size'] = $size;
		$atts['html']['size'] = $size;
		/*
		This should be the correct attribute but does not seem to be working
		at the moment. Revist in the future.
		$atts['html']['data-size'] = $size;
		*/

		$count = $settings['show_count'];
		if ( isset( $args['count'] ) && array_key_exists( $args['count'], self::get_counts() ) ) {
			$count = $args['count'];
		}

		$atts['xhtml']['count'] = $count;
		$atts['html']['count'] = $count;

		/*
		This should be the correct attribute but does not seem to be working
		at the moment. Revist in the future.
		$atts['html']['data-count'] = $count;
		*/

		$markup = $settings['markup'];
		if ( isset( $args['markup'] ) && in_array( $args['markup'], array( 'html', 'xhtml' ) ) ) {
			$markup = $args['markup'];
		}

		$attributes = '';
		foreach ( $atts[$markup] as $name => $value ) {
			$attributes .= ' ' . $name . '="' .  esc_attr( $value ). '"';
		}

		$button = sprintf( '<g:plusone%s></g:plusone>', $attributes );
		if ( 'html' == $markup ) {
			$button = sprintf( '<div%s></div>', $attributes );
		}

		return '<div class="' . esc_attr( $settings['alignment'] ) . '">' . $button . '</div>';
	}

	/**
	 * Print a button.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function button_print( $args = array() ) {
		print "\n" . self::button( $args );
	}

	/**
	 * Prepend button to element in the loop.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function prepend( $content ) {
		if ( ! in_the_loop() ) {
			return $content;
		}
		return self::button() . $content;
	}

	/**
	 * Prepend button to element in archive view.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function prepend_archive( $content ) {
		$settings = self::get_settings();
		if ( empty( $settings['post_types'] ) ) {
			return $content;
		}
		if ( ! in_array( get_post_type(), $settings['post_types'] ) ) {
			return $content;
		}
		return self::prepend( $content );
	}

	/**
	 * Append button to element in the loop.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function append( $content ) {
		if ( ! in_the_loop() ) {
			return $content;
		}
		return $content . self::button();
	}

	/**
	 * Append button to element in archive view.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function append_archive( $content ) {
		$settings = self::get_settings();
		if ( empty( $settings['post_types'] ) ) {
			return $content;
		}
		if ( ! in_array( get_post_type(), $settings['post_types'] ) ) {
			return $content;
		}
		return self::append( $content );
	}

	/**
	 * Add Link to Admin Menu.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function settings_menu() {
		self::$settings_page = add_options_page(
			__( 'Plus One', self::$domain ),
			__( 'Plus One', self::$domain ),
			'manage_options',
			'mfields_plus_one',
			array( __class__, 'settings_page' )
			);
	}

	/**
	 * Register Setting + Admin Panel.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function settings_register() {
		register_setting(
			'mfields_plus_one',
			'mfields_plus_one',
			array( __class__, 'settings_sanitize' )
			);
		add_settings_section(
			'mfields_plus_one_configuration',
			__( 'Configuration', self::$domain ),
			array( __class__, 'message_configuration' ),
			'mfields_plus_one'
			);
		add_settings_field(
			'mfields_plus_one_language',
			__( 'Select Language', self::$domain ),
			array( __class__, 'control_language' ),
			'mfields_plus_one',
			'mfields_plus_one_configuration'
			);
		add_settings_field(
			'mfields_plus_one_markup',
			__( 'Markup Type', self::$domain ),
			array( __class__, 'control_markup' ),
			'mfields_plus_one',
			'mfields_plus_one_configuration'
			);
		add_settings_field(
			'mfields_plus_one_count',
			__( 'Display Count', self::$domain ),
			array( __class__, 'control_count' ),
			'mfields_plus_one',
			'mfields_plus_one_configuration'
			);
		add_settings_field(
			'mfields_plus_one_size',
			__( 'Button Size', self::$domain ),
			array( __class__, 'control_size' ),
			'mfields_plus_one',
			'mfields_plus_one_configuration'
			);
		add_settings_section(
			'mfields_plus_one_theme_integration',
			__( 'Theme Integration', self::$domain ),
			array( __class__, 'message_theme_integration' ),
			'mfields_plus_one'
			);
		add_settings_field(
			'mfields_plus_one_singular',
			__( 'Singular Templates', self::$domain ),
			array( __class__, 'control_singular' ),
			'mfields_plus_one',
			'mfields_plus_one_theme_integration'
			);
		add_settings_field(
			'mfields_plus_one_multiple',
			__( 'Archive Templates', self::$domain ),
			array( __class__, 'control_multiple' ),
			'mfields_plus_one',
			'mfields_plus_one_theme_integration'
			);
		add_settings_field(
			'mfields_plus_one_post_types',
			__( 'Post Types', self::$domain ),
			array( __class__, 'control_post_types' ),
			'mfields_plus_one',
			'mfields_plus_one_theme_integration'
			);
		add_settings_field(
			'mfields_plus_one_alignment',
			__( 'Alignment', self::$domain ),
			array( __class__, 'control_alignment' ),
			'mfields_plus_one',
			'mfields_plus_one_theme_integration'
			);
	}

	/**
	 * Message for Configuration section of settings page.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function message_configuration() {
		print sprintf( esc_html__( 'All settings in this section apply to all buttons.', self::$domain ), '<code>plus-one-button</code>' );
	}

	/**
	 * Message for Theme Integration section of settings page.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function message_theme_integration() {
		print sprintf( esc_html__( 'The following settings enable you to automatically add +1 buttons at different places in your theme. To disable automatic theme intgration, just leave these settings unchecked. You will need to use the %1$s action to display the button in your theme.', self::$domain ), '<code>mfields-plus-one-button</code>' );
	}

	/**
	 * Settings Page Template.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function settings_page() {
		print "\n" . '<div class="wrap" id="' . esc_attr( self::$settings_page ) . '">';
		screen_icon();
		print "\n" . '<h2>' . __( 'Plus One Settings', self::$domain ) . '</h2>';
		print "\n" . '<form action="options.php" method="post">';

		settings_fields( 'mfields_plus_one' );
		do_settings_sections( 'mfields_plus_one' );

		print "\n" . '<div class="button-holder"><input name="submit" type="submit" value="' . esc_attr__( 'Save Changes', self::$domain ) . '" /></div>';
		print "\n" . '</div></form>';
	}

	/**
	 * Language UI.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function control_language() {
		$key = 'language';
		$saved = self::get_setting( $key );

		print "\n" . '<select name="mfields_plus_one[' . $key . ']">';
		foreach( self::get_languages() as $code => $label ) {
			print "\n" . '<option' . selected( $saved, $code ) . ' value="' . esc_attr( $code ) . '">' . esc_html( $label ) . '</option>';
		}
		print "\n" . '</select>';
	}

	/**
	 * Markup Type UI.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function control_markup() {
		$key = 'markup';
		$saved = self::get_setting( $key );

		$id = 'mfields_plus_one_' . $key . '_html';
		print "\n" . '<label for="' . esc_attr( $id ) . '"><input' . checked( $saved, 'html', false ) . ' id="' . esc_attr( $id ) . '" type="radio" name="mfields_plus_one[' . $key . ']" value="html" /> ' . __( 'html', self::$domain ) . '</label>';

		$id = 'mfields_plus_one_' . $key . '_xhtml';
		print "\n" . '<label for="' . esc_attr( $id ) . '"><input' . checked( $saved, 'xhtml', false ) . ' id="' . esc_attr( $id ) . '" type="radio" name="mfields_plus_one[' . $key . ']" value="xhtml" /> ' . __( 'xhtml', self::$domain ) . '</label>';
	}

	/**
	 * Display Count UI.
	 *
	 * @todo Rename 'show_count' tp "count" and use dynamic value for class names.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function control_count() {
		$key = 'show_count';
		$saved = self::get_setting( $key );

		$id = 'mfields_plus_one_count_true';
		print "\n" . '<label for="' . esc_attr( $id ) . '"><input' . checked( $saved, 'true', false ) . ' id="' . esc_attr( $id ) . '" type="radio" class="mfields_plus_one_count" name="mfields_plus_one[' . $key . ']" value="true" /> ' . __( 'Yes', self::$domain ) . '</label>';

		$id = 'mfields_plus_one_count_false';
		print "\n" . '<label for="' . esc_attr( $id ) . '"><input' . checked( $saved, 'false', false ) . ' id="' . esc_attr( $id ) . '" type="radio" class="mfields_plus_one_count" name="mfields_plus_one[' . $key . ']" value="false" /> ' . __( 'No', self::$domain ) . '</label>';
	}

	/**
	 * Button Size UI.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function control_size() {
		$key = 'size';
		$saved = self::get_setting( $key );
		print '<div id="mfields_plus_one_' . $key . '_wrap">';
		foreach ( self::get_sizes() as $size => $label ) {
			$id = 'mfields_plus_one_' . $key . '_' . $size;
			print "\n" . '<label for="' . esc_attr( $id ) . '"><input' . checked( $size, $saved, false ) . ' id="' . esc_attr( $id ) . '" type="radio" class="mfields_plus_one_' . $key . '" name="mfields_plus_one[' . $key . ']" value="' . esc_attr( $size ) . '" /> ' . esc_html( $label ) . '</label>';
		}
		$count = ( 'true' == self::get_setting( 'show_count' ) ) ? ' count' : '';
		print '<div id="' . esc_attr( self::$domain . '_preview' ) . '"><div class="' . esc_attr( $saved . $count ) . '"></div></div>';
		print '</div>';
	}

	/**
	 * Button Alignment UI.
	 *
	 * @since      2011-06-08
	 * @access     private
	 */
	static function control_alignment() {
		$key = 'alignment';
		$saved = self::get_setting( $key );
		print '<div id="mfields_plus_one_' . $key . '_wrap">';
		foreach ( self::get_alignments() as $alignment => $label ) {
			$id = 'mfields_plus_one_' . $key . '_' . $alignment;
			print "\n" . '<label for="' . esc_attr( $id ) . '"><input' . checked( $alignment, $saved, false ) . ' id="' . esc_attr( $id ) . '" type="radio" class="mfields_plus_one_' . $key . '" name="mfields_plus_one[' . $key . ']" value="' . esc_attr( $alignment ) . '" /> ' . esc_html( $label ) . '</label>';
		}
	}

	/**
	 * Singular Template UI.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function control_singular() {
		$key = 'singular';
		$saved = self::get_setting( $key );
		foreach ( self::get_locations_singular() as $location => $label ) {
			$id = 'mfields_plus_one_' . $key . '_' . $location;
			$checked = ( in_array( $location, $saved ) ) ? ' checked="checked"' : '';
			print "\n" . '<label for="' . esc_attr( $id ) . '"><input' . $checked . ' id="' . esc_attr( $id ) . '" type="checkbox" name="mfields_plus_one[' . $key . '][]" value="' . esc_attr( $location ) . '" /> ' . esc_html( $label ) . '</label>';
		}
	}

	/**
	 * Multiple Template UI.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function control_multiple() {
		$key = 'multiple';
		$saved = self::get_setting( $key );
		foreach ( self::get_locations_multiple() as $location => $label ) {
			$id = 'mfields_plus_one_' . $key . '_' . $location;
			$checked = ( in_array( $location, $saved ) ) ? ' checked="checked"' : '';
			print "\n" . '<label for="' . esc_attr( $id ) . '"><input' . $checked . ' id="' . esc_attr( $id ) . '" type="checkbox" name="mfields_plus_one[' . $key . '][]" value="' . esc_attr( $location ) . '" /> ' . esc_html( $label ) . '</label>';
		}
	}

	/**
	 * Post Type UI.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function control_post_types() {
		$key = 'post_types';
		$saved = self::get_setting( $key );
		foreach ( self::get_post_types() as $post_type => $label ) {
			$id = 'mfields_plus_one_' . $key . '_' . $post_type;
			$checked = ( in_array( $post_type, $saved ) ) ? ' checked="checked"' : '';
			print "\n" . '<label for="' . esc_attr( $id ) . '"><input' . $checked . ' id="' . esc_attr( $id ) . '" type="checkbox" name="mfields_plus_one[' . $key .'][]" value="' . esc_attr( $post_type ) . '" /> ' . esc_html( $label ) . '</label>';
		}
	}

	/**
	 * Get Settings.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function get_settings() {
		return wp_parse_args( (array) get_option( 'mfields_plus_one' ), self::get_defaults() );
	}

	/**
	 * Get Setting.
	 *
	 * Gets and individual key stored in the custom settings array.
	 * In the event that an unrecognized key is asked for, boolean
	 * false will be returned.
	 *
	 * @param      string       Key as stored
	 * @return     mixed
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function get_setting( $key ) {
		$settings = self::get_settings();
		if ( isset( $settings[$key] ) ) {
			return $settings[$key];
		}
		return false;
	}

	/**
	 * Sanitize Settings.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function settings_sanitize( $dirty ) {
		$clean = array(
			'alignment'  => 'none',
			'language'   => '',
			'show_count' => 'true',
			'size'       => '',
			'singular'   => array(),
			'markup'     => 'html',
			'multiple'   => array(),
			'post_types' => array(),
			);

		if ( isset( $dirty['show_count'] ) && 'false' == $dirty['show_count'] ) {
			$clean['show_count'] = 'false';
		}

		if ( isset( $dirty['markup'] ) && 'xhtml' == $dirty['markup'] ) {
			$clean['markup'] = 'xhtml';
		}

		if ( isset( $dirty['language'] ) && array_key_exists( $dirty['language'], self::get_languages() ) ) {
			$clean['language'] = $dirty['language'];
		}

		if ( isset( $dirty['size'] ) && array_key_exists( $dirty['size'], self::get_sizes() ) ) {
			$clean['size'] = $dirty['size'];
		}

		if ( isset( $dirty['alignment'] ) && array_key_exists( $dirty['alignment'], self::get_alignments() ) ) {
			$clean['alignment'] = $dirty['alignment'];
		}

		if ( isset( $dirty['singular'] ) ) {
			$locations = self::get_locations_singular();
			foreach ( (array) $dirty['singular'] as $location ) {
				if ( array_key_exists( $location, $locations ) ) {
					$clean['singular'][] = $location;
				}
			}
		}

		if ( isset( $dirty['multiple'] ) ) {
			$locations = self::get_locations_multiple();
			foreach ( (array) $dirty['multiple'] as $location ) {
				if ( array_key_exists( $location, $locations ) ) {
					$clean['multiple'][] = $location;
				}
			}
		}

		if ( isset( $dirty['post_types'] ) ) {
			$post_types = self::get_post_types();
			foreach ( (array) $dirty['post_types'] as $post_type ) {
				if ( array_key_exists( $post_type, $post_types ) ) {
					$clean['post_types'][] = $post_type;
				}
			}
		}

		return $clean;
	}

	/**
	 * Default Values.
	 *
	 * @return     array     Default settings.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function get_defaults() {
		return array(
			'alignment'  => 'none',
			'language'   => 'en-US',
			'markup'     => 'html',
			'multiple'   => array(),
			'post_types' => array( 'post', 'page' ),
			'show_count' => 'true',
			'singular'   => array(),
			'size'       => 'standard',
			);
	}

	/**
	 * Get Post Type.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function get_post_types() {
		$public = array();
		$post_types = get_post_types();
		foreach ( (array) $post_types as $post_type ) {
			$obj = get_post_type_object( $post_type );
			if ( ! isset( $obj->public ) ) {
				continue;
			}
			if ( empty( $obj->public ) ) {
				continue;
			}
			if ( empty( $obj->labels->singular_name ) ) {
				continue;
			}
			$public[$post_type] = $obj->labels->singular_name;
		}
		return $public;
	}

	/**
	 * Get Alignments.
	 *
	 * @since      2011-06-08
	 * @access     private
	 */
	static function get_alignments() {
		return array(
			'none'        => __( 'None', self::$domain ),
			'alignleft'   => __( 'Left', self::$domain ),
			'aligncenter' => __( 'Center', self::$domain ),
			'alignright'  => __( 'Right', self::$domain ),
			);
	}

	/**
	 * Count Settings.
	 *
	 * @return     array     List of supported count settings.
	 *
	 * @since      2011-06-03
	 * @access     private
	 */
	static function get_counts() {
		return array(
			'true'  => __( 'Yes', self::$domain ),
			'false' => __( 'No', self::$domain ),
			);
	}

	/**
	 * Singular Locations.
	 *
	 * @return     array     List of supported theme locations.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function get_locations_singular() {
		return array(
			'before_content' => __( 'Before the Content', self::$domain ),
			'after_content'  => __( 'After the Content', self::$domain ),
			);
	}

	/**
	 * Multiple Locations.
	 *
	 * @return     array     List of supported theme locations.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function get_locations_multiple() {
		return array(
			'before_content' => __( 'Before the Content', self::$domain ),
			'after_content'  => __( 'After the Content', self::$domain ),
			'before_excerpt' => __( 'Before the Excerpt', self::$domain ),
			'after_excerpt'  => __( 'After the Excerpt', self::$domain ),
			);
	}

	/**
	 * Sizes.
	 *
	 * @see        http://code.google.com/apis/+1button/#button-sizes
	 * @return     array     List of supported sizes.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function get_sizes() {
		return array(
			'small'    => __( 'Small', self::$domain ),
			'medium'   => __( 'Medium', self::$domain ),
			'standard' => __( 'Standard', self::$domain ),
			'tall'     => __( 'Tall', self::$domain ),
			);
	}

	/**
	 * Languages.
	 *
	 * @see        http://code.google.com/apis/+1button/#languages
	 * @return     array     List of supported languages.
	 *
	 * @since      2011-06-02
	 * @access     private
	 */
	static function get_languages() {
		return array( 
			'ar'     => __( 'Arabic', self::$domain ),
			'bg'     => __( 'Bulgarian', self::$domain ),
			'ca'     => __( 'Catalan', self::$domain ),
			'zh-CN'  => __( 'Chinese (Simplified)', self::$domain ),
			'zh-TW'  => __( 'Chinese (Traditional)', self::$domain ),
			'hr'     => __( 'Croatian', self::$domain ),
			'cs'     => __( 'Czech', self::$domain ),
			'da'     => __( 'Danish', self::$domain ),
			'nl'     => __( 'Dutch', self::$domain ),
			'en-GB'  => __( 'English (UK)', self::$domain ),
			'en-US'  => __( 'English (US)', self::$domain ),
			'et'     => __( 'Estonian', self::$domain ),
			'fil'    => __( 'Filipino', self::$domain ),
			'fi'     => __( 'Finnish', self::$domain ),
			'fr'     => __( 'French', self::$domain ),
			'de'     => __( 'German', self::$domain ),
			'el'     => __( 'Greek', self::$domain ),
			'iw'     => __( 'Hebrew', self::$domain ),
			'hi'     => __( 'Hindi', self::$domain ),
			'hu'     => __( 'Hungarian', self::$domain ),
			'id'     => __( 'Indonesian', self::$domain ),
			'it'     => __( 'Italian', self::$domain ),
			'ja'     => __( 'Japanese', self::$domain ),
			'ko'     => __( 'Korean', self::$domain ),
			'lv'     => __( 'Latvian', self::$domain ),
			'lt'     => __( 'Lithuanian', self::$domain ),
			'ms'     => __( 'Malay', self::$domain ),
			'no'     => __( 'Norwegian', self::$domain ),
			'fa'     => __( 'Persian', self::$domain ),
			'pl'     => __( 'Polish', self::$domain ),
			'pt-BR'  => __( 'Portuguese (Brazil)', self::$domain ),
			'pt-PT'  => __( 'Portuguese (Portugal)', self::$domain ),
			'ro'     => __( 'Romanian', self::$domain ),
			'ru'     => __( 'Russian', self::$domain ),
			'sr'     => __( 'Serbian', self::$domain ),
			'sk'     => __( 'Slovak', self::$domain ),
			'sl'     => __( 'Slovenian', self::$domain ),
			'es'     => __( 'Spanish', self::$domain ),
			'es-419' => __( 'Spanish (Latin America)', self::$domain ),
			'sv'     => __( 'Swedish', self::$domain ),
			'th'     => __( 'Thai', self::$domain ),
			'tr'     => __( 'Turkish', self::$domain ),
			'uk'     => __( 'Ukrainian', self::$domain ),
			'vi'     => __( 'Vietnamese', self::$domain ),
		);
	}
}