<?php
/**
 * Extension Plugin for Contact Form 7
 *
 * @package mm-wpcf7-extension
 */

/**
 * Plugin Name: Contact Form 7 extension
 * Plugin URI:
 * Description: Plugin created to boost Contact Form 7. It can boost JS and CSS loads and it also expands CF7 to WordPress REST API. This plugin is not made by the creator of CF7. It requires Contact Form 7 and Flamingo for the full features.
 * Author: Mihalovits Márk
 * Author URI: https://github.com/Sealdolphin
 * Version: 1.6.3-post5
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /Languages
 * Text Domain: wpcf7-extension
 */

require_once dirname( __FILE__ ) . '/modules/class-optimization-module.php';
require_once dirname( __FILE__ ) . '/modules/class-flamingo-rest-module.php';
require_once dirname( __FILE__ ) . '/modules/class-custom-validation.php';
require_once dirname( __FILE__ ) . '/modules/custom-blocks/class-custom-select-block.php';

if ( ! class_exists( 'MM_WPCF7_Extension_Plugin' ) ) {
	/**
	 * The main class of the plugin
	 */
	class MM_WPCF7_Extension_Plugin {

		/**
		 * The instance of this plugin
		 *
		 * @var object $instance the instance of this plugin
		 */
		private static $instance;

		/**
		 * This is prefix of this plugin
		 *
		 * @var string $text_domain_str the name of the plugin
		 */
		private static $text_domain_str = 'wpcf7-extension';

		/**
		 * Plugin directory
		 *
		 * @var string $wpcf7_plugin the path to the plugin dir
		 */
		private static $wpcf7_plugin = 'contact-form-7/wp-contact-form-7.php';

		/**
		 * Példány getter
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Konstruktor
		 */
		public function __construct() {
			add_action( 'update_option_active_plugins', 'MM_WPCF7_Extension_Plugin::check_required_plugins_silent' );
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

			$this->opt        = new Optimization_Module();
			$this->rest       = new Flamingo_REST_Module();
			$this->validation = new Custom_Validation();
		}

		/**
		 * Loads the necessary modules.
		 */
		public function load_modules() {
			$this->custom_select = new Custom_Select_Block();
		}

		/**
		 * Loads the text domain
		 */
		public function load_textdomain() {
			// modified slightly from https://gist.github.com/grappler/7060277#file-plugin-name-php.

			$domain = self::$text_domain_str;
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

			load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
			load_plugin_textdomain( $domain, false, basename( dirname( __FILE__ ) ) . '/lang/' );
		}

		/**
		 * Checks if the required plugins are installed.
		 */
		public static function check_required_plugins() {
			if ( ! is_plugin_active( self::$wpcf7_plugin ) ) {
				show_message( __( "This plugin requires another plugin: 'Contact Form 7' to operate. Please download, install and enable WPCF7. The plugin shall deactivate" ) );
				self::shut_down( true );
			}
		}

		/**
		 * Checks for required plugins, and fails silently
		 */
		public static function check_required_plugins_silent() {
			if ( ! is_plugin_active( self::$wpcf7_plugin ) ) {
				self::shut_down();
			}
		}

		/**
		 * Shuts down the plugin
		 *
		 * @param type $silent if set to true, it shuts down without an error.
		 */
		public static function shut_down( $silent = false ) {
			deactivate_plugins( __FILE__, $silent );
		}

	}
}

MM_WPCF7_Extension_Plugin::get_instance();
register_activation_hook( __FILE__, 'MM_WPCF7_Extension_Plugin::check_required_plugins' );
