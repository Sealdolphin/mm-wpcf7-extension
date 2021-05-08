<?php
/**
 * Plugin Name: Contact Form 7 extension
 * Plugin URI: 
 * Description: Plugin created to boost Contact Form 7. It can boost JS and CSS loads and it also expands CF7 to Wordpress REST API. This plugin is not made by the creator of CF7. It requires Contact Form 7 and Flamingo for the full features.
 * Author: Mihalovits Márk
 * Author URI: https://github.com/Sealdolphin
 * Version: 1.2
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /Languages
 * Text Domain: wpcf7-extension
 */

require_once dirname(__FILE__) . "/modules/optimization.php";
require_once dirname(__FILE__) . "/modules/rest.php";

if(! class_exists( 'MM_WPCF7_Extension_Plugin' )) {
    class MM_WPCF7_Extension_Plugin {

        /**
         * A Plugin osztály példánya
         */
        private static $instance;

        private static $TEXT_DOMAIN = "wpcf7-extension";
        private static $wpcf7_plugin = "contact-form-7/wp-contact-form-7.php";
    
        /**
         * Példány getter
         */
        public static function getInstance() {
            if (self::$instance == NULL) {
                self::$instance = new self();
            }
    
            return self::$instance;
        }
        
        /**
         * Konstruktor
         */
        public function __construct() {
            add_action("update_option_active_plugins", "MM_WPCF7_Extension_Plugin::check_required_plugins_silent");
            add_action("plugins_loaded", array($this, "load_textdomain"));
            //add_filter("rest_authentication_errors", array($this, "restrict_access"));
            $this->opt = new OptimizationModule();
            $this->rest = new Flamingo_REST_Module();
        }
    
        function load_textdomain() {
            // modified slightly from https://gist.github.com/grappler/7060277#file-plugin-name-php
        
            $domain = self::$TEXT_DOMAIN;
            $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
            
            load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
            load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/lang/' );
        }
        
        // private function restrict_access($errors) {
    
        //     $request_user_name = $_SERVER['PHP_AUTH_USER'];
        //     $user = get_user_by("login", $request_user_name);
        //     if(!user_can($user, "mm_custom_login_form_read")) {
        //         return new WP_Error('forbidden_access', 'Access Deined', array('status' => 403));
        //     }
    
        //     return $errors;
        // }
    
        static function check_required_plugins()
        {
            if (!is_plugin_active( self::$wpcf7_plugin )) {
                show_message(__("This plugin requires another plugin: 'Contact Form 7' to operate. Please download, install and enable WPCF7. The plugin shall deactivate"));
                MM_WPCF7_Extension_Plugin::shut_down(true);
            }
        }

        static function check_required_plugins_silent()
        {
            if (!is_plugin_active( self::$wpcf7_plugin )) {
                MM_WPCF7_Extension_Plugin::shut_down();
            }
        }

        static function shut_down($silent = false)
        {
            deactivate_plugins(__FILE__, $silent);
        }
    
    }
}

MM_WPCF7_Extension_Plugin::getInstance();
register_activation_hook(__FILE__, "MM_WPCF7_Extension_Plugin::check_required_plugins");