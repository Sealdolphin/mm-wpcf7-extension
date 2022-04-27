<?php
/**
 * Options for the admin page
 *
 * @package modules/admin
 */

/**
 * Options class for admin page
 */
abstract class MM_WPCF7_Admin {

	/**
	 * The slug name for the parent menu (or the file name of a standard WordPress admin page).
	 *
	 * @var string $parent_slug the slug.
	 */
	protected $parent_slug;

	/**
	 * The text to be displayed in the title tags of the page when the menu is selected.
	 *
	 * @var string $page_title the title.
	 */
	protected $page_title;

	/**
	 * The text to be used for the menu.
	 *
	 * @var string $menu_title the menu title.
	 */
	protected $menu_title;

	/**
	 * The capability required for this menu to be displayed to the user.
	 *
	 * @var string $capability the capability.
	 */
	protected $capability;

	/**
	 * The slug name to refer to this menu by.
	 *
	 * @var string $menu_slug the menu slug.
	 */
	protected $menu_slug;

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
		// Init actions and filters for settings API.
		$this->parent_slug = $parent_slug;
		$this->page_title  = $page_title;
		$this->menu_title  = $menu_title;
		$this->capability  = $capability;
		$this->menu_slug   = $menu_slug;
		add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );
	}

	/**
	 * Initializes admin functions.
	 */
	public static function init_admin() {
		$opt = new Optimization_Module(
			'wpcf7',
			'Optimize JS and CSS scripts',
			'Script optimization',
			'manage_options',
			'ext4wpcf7_script_optimization'
		);
		$db  = new Database_Module(
			'wpcf7',
			'Handle Databases',
			'Handle Databases',
			'manage_options',
			'ext4wpcf7_handle_database'
		);
	}

	/**
	 * Creates the admin page.
	 */
	abstract public function render_admin_page();

	/**
	 * Creates admin menu
	 */
	final public function create_admin_menu() {
		add_submenu_page(
			$this->parent_slug,
			$this->page_title,
			$this->menu_title,
			$this->capability,
			$this->menu_slug,
			array( $this, 'render_admin_page' )
		);
	}

}
