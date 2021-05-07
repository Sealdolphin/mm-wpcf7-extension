<?php 

class OptimizationModule {

    private static $option_group = "ext4wpcf7_ext_scripts";
    private static $option_page_name = "ext4wpcf7_script_optimization";

    private static $setting_enable_js = "ext4wpcf7_enable_js";
    private static $setting_enable_css = "ext4wpcf7_enable_css";
    private static $setting_enabled_pages = "ext4wpcf7_enabled_pages";

    public function __construct() {
        //Init actions and filters for settings API
        add_action( 'admin_init', array( $this, 'create_settings'));
        add_action( 'admin_menu', array( $this, 'admin_menu'));
        add_action( 'init', array( $this, 'regulate_script_load' ));
        add_action( 'wp_enqueue_scripts', array( $this, 'enable_scripts' ));
    }

    public function regulate_script_load()
    {
        if(!get_option( self::$setting_enable_css )) {
            add_filter( 'wpcf7_load_css', '__return_false' );
        }
        if(!get_option( self::$setting_enable_js )) {
            add_filter( 'wpcf7_load_js', '__return_false' );
        }
    }

    function create_settings()
    {
        $this->register_settings();
        $section_enable_all = "enable_all";
        $section_enabled_pages = "enabled_pages";

        add_settings_section(
            $section_enable_all,
            "Enable JavaScript and CSS scripts on all pages",
            "",
            self::$option_page_name
        );

        add_settings_field(
            self::$setting_enable_js,
            "Javascript is enabled in all pages",
            array($this, "render_checkbox"),
            self::$option_page_name,
            $section_enable_all,
            array(
                "label" => "Javascript is enabled",
                "id" => self::$setting_enable_js
            )
        );

        add_settings_field(
            self::$setting_enable_css,
            "CSS is enabled in all pages",
            array($this, "render_checkbox"),
            self::$option_page_name,
            $section_enable_all,
            array(
                "label" => "CSS is enabled",
                "id" => self::$setting_enable_css
            )
        );

        add_settings_section(
            $section_enabled_pages,
            "Enable JavaScript and CSS scripts on chosen pages",
            "",
            self::$option_page_name
        );

        add_settings_field(
            self::$setting_enable_css,
            "Select pages to enable JS and CSS",
            array($this, "render_enabled_pages"),
            self::$option_page_name,
            $section_enabled_pages
        );

    }

    private function register_settings()
    {
        register_setting(
            self::$option_group,
            self::$setting_enable_js,
            array(
                'type' => 'boolean',
                'default' => true
            )
        );

        register_setting(
            self::$option_group,
            self::$setting_enable_css,
            array(
                'type' => 'boolean',
                'default' => true
            )
        );

        register_setting(
            self::$option_group,
            self::$setting_enabled_pages,
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'save_enabled_pages')
            )
        );

    }

    function create_options_page() {

        $pageTitle = __( 'Script optimization');
        ?>
        <div class="wrap">
            <h1><?php _e($pageTitle) ?></h1>
            <form method="post" action="options.php">
        <?php
        settings_fields( self::$option_group );
        do_settings_sections( self::$option_page_name );

        submit_button();
        ?>
            </form>
        </div>
        <?php
    }

    public function admin_menu()
    {
        add_submenu_page(
            "wpcf7",
            "Optimize JS and CSS scripts",
            "Script optimization",
            "manage_options",
            self::$option_page_name,
            array($this, "create_options_page")
        );
    }
    
    function render_checkbox($args) {
        $option_label = $args["label"];
        $option_id = $args["id"];
        $checked = get_option($option_id);

        ?>
        <input type="checkbox" name=<?php _e($option_id) ?> id=<?php _e($option_id) ?> <?php _e($checked ? "checked" : "") ?>/>
        <label for=<?php _e($option_id) ?>><?php _e($option_label) ?></label>
        <?php
    }

    function render_enabled_pages() {
        ?>
        <table>
            <thead>
                <tr>
                    <th><?php _e(__("Page id")) ?></th>
                    <th><?php _e(__("Page title")) ?></th>
                    <th><?php _e(__("Scripts enabled")) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach (get_pages() as $page) {
                        $this->render_one_page($page->ID, $page->post_title);
                    }
                ?>
            </tbody>
        </table>
        <?php
    }

    function render_one_page($page_id, $title) {
        $pages = get_option(self::$setting_enabled_pages);
        if($pages) {
            $checked = in_array($page_id, $pages);
        } else $checked = false;

        ?>
        <tr>
            <td><?php _e($page_id) ?></td>
            <td><?php _e($title) ?></td>
            <td><input type="checkbox" name=<?php _e(self::$setting_enabled_pages ."[" . $page_id ."]") ?> id=<?php _e($page_id) ?> <?php _e($checked ? "checked" : "") ?>/></td>
        </tr>
        <?php
    }

    function save_enabled_pages($opts) {
        $enabled_pages = [];
        if($opts !== NULL) {
            foreach($opts as $id => $value) {
                if($value === "on") {
                    $enabled_pages[] = $id;
                }
            }
        }
        return $enabled_pages;
    }

    function enable_scripts()
    {
        $enabled = get_option(self::$setting_enabled_pages);
        $need_scripts = !(get_option(self::$setting_enable_js) && get_option(self::$setting_enable_css));
        $page_id = get_the_ID();

        if($need_scripts && in_array($page_id, $enabled)) {
            if ( function_exists( 'wpcf7_enqueue_scripts' ) ) {
                wpcf7_enqueue_scripts();
            }
            
            if ( function_exists( 'wpcf7_enqueue_styles' ) ) {
                wpcf7_enqueue_styles();
            }
        }
    }

}