<?php
/**
 * CSS and JavaScript Optimization
 *
 * @package modules
 */

/**
 * Enables or disables JavaScript and CSS loading on your pages.
 * This way you can make your website load faster.
 */
class Optimization_Module {

	/**
	 * All settings about script optimization
	 *
	 * @var string $option_group name of the setting group.
	 */
	private static $option_group = 'ext4wpcf7_ext_scripts';

	/**
	 * Name of the options page
	 *
	 * @var string $option_page_name name of the options page.
	 */
	private static $option_page_name = 'ext4wpcf7_script_optimization';
	/**
	 * Turns the JavaScript features on and off
	 *
	 * @var string the name of the setting.
	 */
	private static $setting_enable_js = 'ext4wpcf7_enable_js';
	/**
	 * Turns the CSS features on and off
	 *
	 * @var string the name of the setting.
	 */
	private static $setting_enable_css = 'ext4wpcf7_enable_css';
	/**
	 * The list of pages where JS / CSS is enabled
	 *
	 * @var string the name of the setting.
	 */
	private static $setting_enabled_pages = 'ext4wpcf7_enabled_pages';

	/**
	 * Constructor
	 */
	public function __construct() {
		// Init actions and filters for settings API.
		add_action( 'admin_init', array( $this, 'create_settings' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'init', array( $this, 'regulate_script_load' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enable_scripts' ) );
	}

	/**
	 * This is the way to enable / disable script load
	 */
	public function regulate_script_load() {
		if ( ! get_option( self::$setting_enable_css ) ) {
			add_filter( 'wpcf7_load_css', '__return_false' );
		}
		if ( ! get_option( self::$setting_enable_js ) ) {
			add_filter( 'wpcf7_load_js', '__return_false' );
		}
	}

	/**
	 * Create settings menu
	 */
	public function create_settings() {
		$this->register_settings();
		$section_enable_all    = 'enable_all';
		$section_enabled_pages = 'enabled_pages';

		add_settings_section(
			$section_enable_all,
			'Enable JavaScript and CSS scripts on all pages',
			'',
			self::$option_page_name
		);

		add_settings_field(
			self::$setting_enable_js,
			'Javascript is enabled in all pages',
			array( $this, 'render_checkbox' ),
			self::$option_page_name,
			$section_enable_all,
			array(
				'label' => 'Javascript is enabled',
				'id'    => self::$setting_enable_js,
			)
		);

		add_settings_field(
			self::$setting_enable_css,
			'CSS is enabled in all pages',
			array( $this, 'render_checkbox' ),
			self::$option_page_name,
			$section_enable_all,
			array(
				'label' => 'CSS is enabled',
				'id'    => self::$setting_enable_css,
			)
		);

		add_settings_section(
			$section_enabled_pages,
			'Enable JavaScript and CSS scripts on chosen pages',
			'',
			self::$option_page_name
		);

		add_settings_field(
			self::$setting_enable_css,
			'Select pages to enable JS and CSS',
			array( $this, 'render_enabled_pages' ),
			self::$option_page_name,
			$section_enabled_pages
		);

	}

	/**
	 * Registers the plugin settings
	 */
	private function register_settings() {
		register_setting(
			self::$option_group,
			self::$setting_enable_js,
			array(
				'type'    => 'boolean',
				'default' => true,
			)
		);

		register_setting(
			self::$option_group,
			self::$setting_enable_css,
			array(
				'type'    => 'boolean',
				'default' => true,
			)
		);

		register_setting(
			self::$option_group,
			self::$setting_enabled_pages,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'save_enabled_pages' ),
			)
		);

	}

	/**
	 * Creates an options page in the WordPress Control Panel
	 */
	public function create_options_page() {

		$page_title = __( 'Script optimization' );
		?>
		<div class='wrap'>
			<h1><?php _esc_attr_e( $page_title ); ?></h1>
			<form method='post' action='options.php'>
		<?php
		settings_fields( self::$option_group );
		do_settings_sections( self::$option_page_name );

		submit_button();
		?>
			</form>
		</div>
		<?php
	}

	/**
	 * Creates the admin menu layout
	 */
	public function admin_menu() {
		add_submenu_page(
			'wpcf7',
			'Optimize JS and CSS scripts',
			'Script optimization',
			'manage_options',
			self::$option_page_name,
			array( $this, 'create_options_page' )
		);
	}

	/**
	 * Renders a checkbox with given arguments
	 *
	 * @param array $args the given argument list.
	 */
	public function render_checkbox( $args ) {
		$option_label = $args['label'];
		$option_id    = $args['id'];
		$checked      = get_option( $option_id );

		?>
		<input type='checkbox' name=<?php _esc_attr_e( $option_id ); ?> id=<?php _esc_attr_e( $option_id ); ?> <?php _esc_attr_e( $checked ? 'checked' : '' ); ?>/>
		<label for=<?php _esc_attr_e( $option_id ); ?>><?php _esc_attr_e( $option_label ); ?></label>
		<?php
	}

	/**
	 * Renders a list of pages, where JavaScript and CSS are enabled
	 */
	public function render_enabled_pages() {
		?>
		<table>
			<thead>
				<tr>
					<th><?php _esc_attr_e( __( 'Page id' ) ); ?></th>
					<th><?php _esc_attr_e( __( 'Page title' ) ); ?></th>
					<th><?php _esc_attr_e( __( 'Scripts enabled' ) ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( get_pages() as $page ) {
					$this->render_one_page( $page->ID, $page->post_title );
				}
				?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Renders a page based on the current settings
	 *
	 * @param string $page_id the ID of the page.
	 * @param string $title the title of the page.
	 */
	public function render_one_page( $page_id, $title ) {
		$pages = get_option( self::$setting_enabled_pages );
		if ( $pages ) {
			$checked = in_array( $page_id, $pages, true );
		} else {
			$checked = false;
		}

		?>
		<tr>
			<td><?php _esc_attr_e( $page_id ); ?></td>
			<td><?php _esc_attr_e( $title ); ?></td>
			<td><input type='checkbox' name=<?php _esc_attr_e( self::$setting_enabled_pages . '[' . $page_id . ']' ); ?> id=<?php _esc_attr_e( $page_id ); ?> <?php _esc_attr_e( $checked ? 'checked' : '' ); ?>/></td>
		</tr>
		<?php
	}

	/**
	 * Saves the changed settings
	 *
	 * @param array $opts the pending options.
	 */
	public function save_enabled_pages( $opts ) {
		$enabled_pages = array();
		if ( null !== $opts ) {
			foreach ( $opts as $id => $value ) {
				if ( 'on' === $value ) {
					$enabled_pages[] = $id;
				}
			}
		}
		return $enabled_pages;
	}

	/**
	 * Enables JavaScript and CSS features
	 */
	public function enable_scripts() {
		$enabled      = get_option( self::$setting_enabled_pages );
		$need_scripts = ! ( get_option( self::$setting_enable_js ) && get_option( self::$setting_enable_css ) );
		$page_id      = get_the_ID();

		if ( $need_scripts && in_array( $page_id, $enabled, true ) ) {
			if ( function_exists( 'wpcf7_enqueue_scripts' ) ) {
				wpcf7_enqueue_scripts();
			}

			if ( function_exists( 'wpcf7_enqueue_styles' ) ) {
				wpcf7_enqueue_styles();
			}
		}
	}

}
