<?php
/**
 * MySQL database handler
 *
 * @package modules/admin
 */

/**
 * Handles database options.
 */
class Database_Module extends MM_WPCF7_Admin {

	/**
	 * Creates an options page in the WordPress Control Panel
	 */
	public function render_admin_page() {

		?>
		<div class='wrap'>
			<h1><?php esc_attr_e( 'Handling Databases' ); ?></h1>
		</div>
		<?php
	}

}
