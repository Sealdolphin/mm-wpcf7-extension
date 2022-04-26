<?php
/**
 * CSS and JavaScript Optimization
 *
 * @package modules/admin
 */

/**
 * Enables or disables JavaScript and CSS loading on your pages.
 * This way you can make your website load faster.
 */
class Optimization_Module extends Admin implements Settings {

	/**
	 * All settings about script optimization
	 *
	 * @var string $option_group name of the setting group.
	 */
	private static $option_group = 'ext4wpcf7_ext_scripts';
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
		add_action( 'init', array( $this, 'regulate_script_load' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enable_scripts' ) );
		$this->create_settings();
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
			__( 'Enable JavaScript and CSS scripts on all pages' ),
			'',
			$this->$menu_slug,
		);

		add_settings_field(
			self::$setting_enable_js,
			__( 'Javascript is enabled in all pages' ),
			array( $this, 'render_checkbox' ),
			$this->menu_slug,
			$section_enable_all,
			array(
				'label' => __( 'Javascript is enabled' ),
				'id'    => self::$setting_enable_js,
			)
		);

		add_settings_field(
			self::$setting_enable_css,
			__( 'CSS is enabled in all pages' ),
			array( $this, 'render_checkbox' ),
			$this->menu_slug,
			$section_enable_all,
			array(
				'label' => __( 'CSS is enabled' ),
				'id'    => self::$setting_enable_css,
			)
		);

		add_settings_section(
			$section_enabled_pages,
			__( 'Enable JavaScript and CSS scripts on chosen pages' ),
			'',
			$this->menu_slug
		);

		add_settings_field(
			self::$setting_enable_css,
			__( 'Select pages to enable JS and CSS' ),
			array( $this, 'render_enabled_pages' ),
			$this->menu_slug,
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
	public function render_admin_page() {

		?>
		<div class='wrap'>
			<h1><?php esc_attr_e( 'Script optimization' ); ?></h1>
			<form method='post' action='options.php'>
		<?php
		settings_fields( self::$option_group );
		do_settings_sections( $this->menu_slug );

		submit_button();
		?>
			</form>
		</div>
		<?php
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
		<input type='checkbox' name=<?php echo( esc_attr( $option_id ) ); ?> id=<?php echo( esc_attr( $option_id ) ); ?> <?php echo( esc_attr( $checked ? 'checked' : '' ) ); ?>/>
		<label for=<?php echo( esc_attr( $option_id ) ); ?>><?php echo( esc_attr( $option_label ) ); ?></label>
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
					<th><?php esc_attr_e( 'Page id' ); ?></th>
					<th><?php esc_attr_e( 'Page title' ); ?></th>
					<th><?php esc_attr_e( 'Scripts enabled' ); ?></th>
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
			<td><?php echo( esc_attr( $page_id ) ); ?></td>
			<td><?php echo( esc_attr( $title ) ); ?></td>
			<td><input type='checkbox' name=<?php echo( esc_attr( self::$setting_enabled_pages . '[' . $page_id . ']' ) ); ?> id=<?php echo( esc_attr( $page_id ) ); ?> <?php echo( esc_attr( $checked ? 'checked' : '' ) ); ?>/></td>
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
