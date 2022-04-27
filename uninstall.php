<?php
/**
 * Uninstaller for this plugin
 *
 * @package mm-wpcf7-extension
 */

/**
 * Need for deleting databases
 */
require_once dirname( __FILE__ ) . '/modules/class-database.php';

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

Database::db_destroy();
