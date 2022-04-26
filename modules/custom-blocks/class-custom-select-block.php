<?php
/**
 * Custom select block for CF7
 *
 * @package modules/custom_blocks
 */

/**
 * Custom select block instead of normal block
 */
class Custom_Select_Block {

	/**
	 * Basic constructor
	 */
	public function __construct() {
		add_action( 'wpcf7_init', array( $this, 'add_to_wpcf7' ), 10, 0 );
		add_action( 'wpcf7_admin_init', array( $this, 'add_tag_generator_menu' ), 25, 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'prepare_scripts' ) );
	}

	/**
	 * Registers the CSS and JS addons for the frontend.
	 */
	public function prepare_scripts() {
		wp_register_style(
			'custom-select-block-css',
			plugin_dir_url( __FILE__ ) . 'scripts/custom-select.css',
			array(),
			MM_WPCF7_Extension_Plugin::get_css_version()
		);
		wp_enqueue_style( 'custom-select-block-css' );

		wp_register_script(
			'custom-select-block-js',
			plugin_dir_url( __FILE__ ) . 'scripts/custom-select.js',
			array(),
			MM_WPCF7_Extension_Plugin::get_css_version(),
			true
		);
		wp_enqueue_script( 'custom-select-block-js' );
	}

	/**
	 * Adds this block to the WPCF7 roster.
	 */
	public function add_to_wpcf7() {
		wpcf7_add_form_tag(
			array( 'custom_select', 'custom_select*' ),
			array( $this, 'render_object' ),
			array( 'name-attr' => true )
		);
	}

	/**
	 * Renders the HTML tag on the screen.
	 *
	 * @param object $tag the HTML object.
	 */
	public function render_object( $tag ) {
		if ( empty( $tag->name ) ) {
			return '';
		}

		$validation_error = wpcf7_get_validation_error( $tag->name );
		$class            = wpcf7_form_controls_class( $tag->type );

		// If validation fails place a validation class.
		if ( $validation_error ) {
			$class .= ' wpcf7-not-valid';
		}

		// The attributes of the HTML tag.
		$atts   = $this->setup_attributes( $tag, $class, $validation_error );
		$values = $tag->values;
		$labels = $tag->labels;

		return $this->create_html( $atts, $values, $labels );
	}

	/**
	 * Sets up the necessary attributes for the HTML
	 *
	 * @param object  $tag the HTML tag object.
	 * @param object  $class the HTML class of the object.
	 * @param boolean $validation_error true if the object is invalid.
	 */
	public function setup_attributes( $tag, $class, $validation_error ) {
		$atts = array();

		$atts['class'] = $tag->get_class_option( $class );
		$atts['id']    = $tag->get_id_option();
		$atts['name']  = $tag->name;
		$atts['desc']  = $tag->get_option( 'description', '.+', true );
		$atts['file']  = $tag->get_option( 'file', '.+', true );

		if ( $tag->is_required() ) {
			$atts['aria-required'] = 'true';
		}

		if ( $validation_error ) {
			$atts['aria-invalid']     = 'true';
			$atts['aria-describedby'] = wpcf7_get_validation_error_reference( $tag->name );
		} else {
			$atts['aria-invalid'] = 'false';
		}

		return $atts;
	}

	/**
	 * Sets up the HTML block
	 *
	 * @param array $atts the HTML attributes.
	 * @param array $values the option values.
	 * @param array $labels the option labels.
	 */
	public function create_html( $atts, $values, $labels ) {
		$options_html = '';
		foreach ( $values as $key => $value ) {
			$options_html .= $this->create_option( $value, $labels[ $key ] );
		}

		$search_html = $this->create_search_html(
			esc_html( $atts['id'] ),
			esc_html( $atts['name'] ),
			esc_html( $atts['desc'] ),
			esc_html( $atts['file'] ),
			$options_html
		);

		return $search_html;
	}

	/**
	 * Creates the HTML element of the search
	 *
	 * @param string $id the id of the search tag.
	 * @param string $name the name of the tag.
	 * @param string $description a small description of the search box.
	 * @param string $file the list file.
	 * @param string $options_html the html body of the options.
	 */
	public function create_search_html( $id, $name, $description, $file, $options_html ) {
		// TODO: why won't render this in one paragraph?

		$select_wrapper = sprintf( '<div class="wpcf7-custom-select-wrapper"><span class="wpcf7-custom-select-list"><ul id="%s-list"></ul></span></div>', $id );
		$select_input   = sprintf( '<div><input class="wpcf7-custom-select-input" id="%s-input" name="%s" type="text"/></div>', $id, $name );
		$label          = sprintf( '<label for="%s-input">%s</label>', $id, $description );

		$html_body = '<span class="wpcf7-form-control-wrap custom-select">'
			. $label
			. $select_input
			. $select_wrapper
			. '</span>';

		return $html_body;
	}

	/**
	 * Creates an option out of thin air.
	 *
	 * @param string $value is the value of the option.
	 * @param string $label is the label of the option.
	 */
	public function create_option( $value, $label ) {
		$e_label = esc_html( $label );
		return <<<EOD
		<li value="$value">$e_label</li>
		EOD;
	}

	/**
	 * Adds this tag to the generator.
	 */
	public function add_tag_generator_menu() {
		$tag_generator = WPCF7_TagGenerator::get_instance();
		$tag_generator->add(
			'custom_select',
			__( 'custom drop-down menu' ),
			array( $this, 'render_admin' )
		);
	}

	/**
	 * Renders the menu in the form generator.
	 *
	 * @param object $contact_form is the form itself.
	 * @param array  $args is the arguments.
	 */
	public function render_admin( $contact_form, $args = '' ) {
		$args = wp_parse_args( $args, array() );

		$description = __( 'This is a custom select box, where you can import bigger option table.' );
		?>
		<div class="control-box">
			<fieldset>
				<legend><?php echo esc_html( $description ); ?></legend>
				<table class="form-table">
					<tbody>
						<tr> <!--Field type header-->
							<th scope="row"><?php echo esc_html( __( 'Field type', 'contact-form-7' ) ); ?></th>
							<td>
								<fieldset>
									<legend class="screen-reader-text"><?php echo esc_html( __( 'Field type', 'contact-form-7' ) ); ?></legend>
									<label><input type="checkbox" name="required"/><?php echo esc_html( __( 'Required field', 'contact-form-7' ) ); ?></label>
								</fieldset>
							</td>
						</tr>
						<tr> <!--Name header-->
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?></label></th>
							<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>"/></td>
						</tr>
						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-description' ); ?>"><?php echo esc_html( __( 'Description', 'contact-form-7' ) ); ?></label></th>
							<td><input type="text" name="description" class="descriptionvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-description' ); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-file' ); ?>"><?php echo esc_html( __( 'File', 'contact-form-7' ) ); ?></label></th>
							<td><input type="text" name="file" class="filevalue oneline option" id="<?php echo esc_attr( $args['content'] . '-file' ); ?>" required /></td>
						</tr>
						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'contact-form-7' ) ); ?></label></th>
							<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" required /></td>
						</tr>
					</tbody>
				</table>
			</fieldset>
		</div>
		<div class="insert-box">
			<input type="text" name="custom_select" class="tag code" readonly="readonly" onfocus="this.select()" />
			<div class="submitbox">
				<input type="button" class="button button-primary insert-tag" value="<?php echo esc_html( __( 'Insert Tag', 'contact-form-7' ) ); ?>"/>
			</div>
			<br class="clear"/>
			<p class="description mail-tag">Hello mail tag description.</p>
		</div>
		<?php
	}
}
