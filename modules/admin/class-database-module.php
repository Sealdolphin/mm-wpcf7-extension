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
				<tr id="db-id" class="iedit">
					<th scope="row" class="check-column">
						<input type="checkbox" id="cb-select-db-n" name="db[]" value="n"/>
					</th>
					<td class="column-name column-primary" data-colname="<?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?>">
						<strong>Universities</strong>
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
						This database holds all the universities of Hungary. 
					</td>
					<td class="column-records" data-colname="<?php echo esc_html( __( 'Records', 'contact-form-7' ) ); ?>">
						56 rows
					</td>
				</tr>
				</tbody>
			</table>
			<h1><?php esc_attr_e( 'Import new databases from CSV' ); ?></h1>
			<form method="post" enctype="multipart/form-data" class="wp-upload-form" action="">
				<input type="hidden" id="_wpnonce" name="_wpnonce" value="28ad7da86d"><input type="hidden" name="_wp_http_referer" value="">
				<input type="file" id="csv" name="csv" accept=".csv">
				<input type="submit" name="upload-db-submit" id="upload-db-submit" class="button" value="Feltöltés" disabled>
			</form>	
		</div>
		<?php
	}

}
