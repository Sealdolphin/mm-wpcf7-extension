<?php
/**
 * MySQL database handler
 *
 * @package modules/admin
 */

namespace mm_wpcf7;

/**
 * Handles database options.
 */
class Database_Module extends MM_WPCF7_Admin {

	/**
	 * Constructor
	 *
	 * @param string $parent_slug the slug.
	 * @param string $page_title the title.
	 * @param string $menu_title the menu title.
	 * @param string $capability the capability.
	 * @param string $menu_slug the unique menu slug.
	 */
	public function __construct( $parent_slug, $page_title, $menu_title, $capability, $menu_slug ) {
		parent::__construct( $parent_slug, $page_title, $menu_title, $capability, $menu_slug );
		add_action( 'admin_enqueue_scripts', array( $this, 'prepare_scripts' ) );
		add_action( 'wp_ajax_form_submit_csv', array( $this, 'upload_csv' ) );
	}

	/**
	 * Prepare necessary scripts
	 */
	public function prepare_scripts() {
		$nonce = wp_create_nonce( 'csv-upload' );

		wp_register_script(
			'csv-upload-js',
			plugin_dir_url( __FILE__ ) . 'scripts/csv-upload.js',
			array(),
			MM_WPCF7_Extension_Plugin::get_css_version(),
			true
		);
		wp_enqueue_script( 'csv-upload-js' );

		wp_add_inline_script(
			'csv-upload-js',
			sprintf(
				'const WP_NONCE = %s',
				$nonce
			),
			'before'
		);
	}

	/**
	 * Handles the AJAX request from submitted form in settings
	 */
	public function upload_csv() {
		var_dump( 'CSV has arrived!' );
		if ( isset( $_POST['form_data'], $_POST['form_data_nonce'] ) && wp_verify_nonce( 'csv-upload' ) ) {
			$form_data = sanitize_text_field( wp_unslash( $_POST['form_data'] ) );

			var_dump( $form_data );
		}

		wp_die();
	}

	/**
	 * Creates an options page in the WordPress Control Panel
	 */
	public function render_admin_page() {
		$databases = Database::list_databases();

		?>
		<div class='wrap'>
			<h1><?php esc_attr_e( 'Handling Databases' ); ?></h1>
			<i><?php esc_attr_e( 'Associated to this plugin' ); ?></i>
			<table class="wp-list-table widefat fixed striped table-view-list">
				<thead>
					<tr>
						<td id="cb" class="manage-column column-cb check-column">
							<input id="cb-select-all-1" type="checkbox">
						</td>
						<th scope="col" id="name" class="manage-column column-name column-primary"><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?></th>
						<th scope="col" id="name" class="manage-column column-description"><?php echo esc_html( __( 'Description', 'contact-form-7' ) ); ?></th>
						<th scope="col" id="name" class="manage-column column-records"><?php echo esc_html( __( 'Records', 'contact-form-7' ) ); ?></th>
					</tr>
				</thead>
				<tbody id="the-list">
				<?php
				foreach ( $databases as $database ) {
					$database->update_details();
					?>
					<tr id="db-id" class="iedit">
						<th scope="row" class="check-column">
							<input type="checkbox" id="cb-select-db-n" name="db[]" value="n"/>
						</th>
						<td class="column-name column-primary" data-colname="<?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?>">
							<strong><?php echo esc_html( $database->get_name() ); ?></strong>
							<div class="row-actions">
								<span class="edit">
									<button type="button" class="button-link"><?php echo esc_html( __( 'Edit', 'contact-form-7' ) ); ?></button>  | 
								</span>
								<span class="trash">
									<button type="button" class="button-link submitdelete" style="color: #b32d2e"><?php echo esc_html( __( 'Trash', 'contact-form-7' ) ); ?></button>
								</span>
							</div>
						</td>
						<td class="column-description" data-colname="<?php echo esc_html( __( 'Description', 'contact-form-7' ) ); ?>">
							<?php echo esc_html( $database->get_description() ); ?>
						</td>
						<td class="column-records" data-colname="<?php echo esc_html( __( 'Records', 'contact-form-7' ) ); ?>">
							<?php echo esc_html( $database->get_records() ); ?> rows
						</td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>
			<h1><?php esc_attr_e( 'Import new databases from CSV' ); ?></h1>
			<form method="post" enctype="multipart/form-data" class="wp-upload-form" id="csv-submit" data-action="form_submit_csv" data-url="<?php echo esc_html( admin_url( 'admin-ajax.php' ) . '?action=form_submit_csv' ); ?>">
				<input type="hidden" id="_wpnonce" name="_wpnonce" value="28ad7da86d"><input type="hidden" name="_wp_http_referer" value="">
				<input type="file" id="csv-file" name="csv" accept=".csv">
				<input type="submit" name="upload-db-submit" id="upload-db-submit" class="button" value="Feltöltés" disabled>
			</form>	
		</div>
		<?php
	}

}
