<?php
/**
 * Custom validation rules / blocks for CF7
 *
 * @package modules
 */

/**
 * Enables custom validation rules. Currently available:
 * - phone number
 */
class Custom_Validation {

	/**
	 * Regular expression for phone numbers in Hungary
	 *
	 * @var phone_regex_hun
	 */
	private static $phone_regex_hun = '/\\+36-\\d{2}-\\d{3}-\\d{4}\$/';

	/**
	 * Registering special validation fields
	 */
	public function __construct() {
		if ( class_exists( 'WPCF7_Validation' ) ) {
			add_filter( 'wpcf7_validate_tel*', array( $this, 'apply_phone_validation' ), 20, 2 );
			add_filter( 'wpcf7_validate_custom_list*', array( $this, 'apply_custom_list_validation' ), 20, 2 );
			// Add custom-select filter.
		}
	}

	/**
	 * This functions validates the special phone field type.
	 * Applies a regular expression and evaluates it.
	 *
	 * @param object $result the result being checked.
	 * @param array  $tag any applied tags.
	 */
	public function apply_phone_validation( $result, $tag ) {
		wp_verify_nonce( $_REQUEST );
		$phone_number = isset( $_POST[ $tag->name ] ) ? trim( sanitize_text_field( wp_unslash( $_POST[ $tag->name ] ) ) ) : '';

		if ( ! preg_match( self::$phone_regex_hun, $phone_number ) ) {
			$result->invalidate( $tag, __( 'Kérlek kövesd a formátumot!' ) );
		}

		return $result;
	}

}
