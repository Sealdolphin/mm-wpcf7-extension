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
	}

	/**
	 * Adds this block to the WPCF7 roster.
	 */
	public function add_to_wpcf7() {
		// Check first if block exist.
		wpcf7_add_form_tag(
			'custom_select',
			array( $this, 'render_object' ),
			array(
				'name-attr' => true, // TODO: check this.
			)
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

		$class = wpcf7_form_controls_class( $tag->type );

		// If validation fails place a validation class.
		if ( $validation_error ) {
			$class .= ' wpcf7-not-valid';
		}

		// The attributes of the HTML tag.
		$atts   = setup_attributes();
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

		$atts['class']    = $tag->getclass_option( $class );
		$atts['id']       = $tag->get_id_option();
		$atts['tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );

		if ( $tag->is_required() ) {
			$atts['aria-required'] = 'true';
		}

		if ( $validation_error ) {
			$atts['aria-invalid'] = 'true';
			$atts['aria-describedby'] = wpcf7_get_validation_error_reference(
				$tag->name
			);
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
		$html = '';

		$options_html = array();
		foreach ( $values as $key => $value ) {
			$options_html[] = $this->create_option( $value, $labels[ $key ] );
		}

		$atts        = wpcf7_format_atts( $atts );
		$search_html = $this->create_search_html( $atts['id'], $atts['name'], $atts['?'], $options_html );

		return $html;
	}

	/**
	 * Creates the HTML element of the search
	 *
	 * @param string $id the id of the search tag.
	 * @param string $name the name of the tag.
	 * @param string $description a small description of the search box.
	 * @param string $options_html the html body of the options.
	 */
	public function create_search_html( $id, $name, $description, $options_html ) {
		$html_body = <<<EOD
		<div class="custom-select-block-wpcf7">
			<label for="$id">$description</label>
			<div>
				<input id="$id" name="$name" type="text" style="width: 100%;">
			</div>
			<div class="custom-select-wrapper>
				<div class="custom-select-list">
					<ul>$options_html</ul>
				</div>
			</div>
		</div>
		EOD;

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
}
