<?php
/**
 * Module, that has settings
 *
 * @package modules/admin
 */

namespace mm_wpcf7;

/**
 * Settings interface
 */
interface Settings {

	/**
	 * Creates and registers settings
	 */
	public function create_settings();

	/**
	 * Registers all the settings
	 */
	public function register_settings();

}
