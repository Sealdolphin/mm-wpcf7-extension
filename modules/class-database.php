<?php
/**
 * Custom class for Databases
 *
 * @package modules
 */

namespace mm_wpcf7;

/**
 * Class that represents uploaded database in MySQL
 */
class Database {

	/**
	 * Prefix for databases
	 *
	 * @var string $db_prefix the prefix of all db-s
	 */
	private static string $db_prefix = 'mm_wpcf7_db_';

	/**
	 * Short name
	 *
	 * @var string $name the name of the database
	 */
	private string $name;

	/**
	 * Short description
	 *
	 * @var string $description the name of the database
	 */
	private string $description;

	/**
	 * Number of records
	 *
	 * @var int $records the number of records in the database
	 */
	private int $records;

	/**
	 * Prepares the necessary databases
	 */
	public static function db_init() {
		global $wpdb;

		$query_cache_id = 'mm_wpcf7_meta_db_create';
		$query          = wp_cache_get( $query_cache_id );

		if ( false === $query ) {
			$query = $wpdb->query(
				'CREATE TABLE IF NOT EXISTS `wordpress`.`mm_wpcf7_meta` (
					`id` INT NOT NULL AUTO_INCREMENT,
					`db_id` VARCHAR(45) NOT NULL,
					`db_description` VARCHAR(45) NULL,
					PRIMARY KEY (`id`),
					UNIQUE INDEX `db_id_UNIQUE` (`db_id` ASC),
					UNIQUE INDEX `id_UNIQUE` (`id` ASC));
				'
			);
			wp_cache_set( $query_cache_id, $query );
		}
	}

	/**
	 * Lists all registered databases
	 */
	public static function list_databases() {
		global $wpdb;

		$query_cache_id = 'mm_wpcf7_db_list';
		$query          = wp_cache_get( $query_cache_id );

		if ( false === $query ) {
			$query = $wpdb->query( 'SELECT * FROM `wordpress`.`mm_wpcf7_meta`;' );
			wp_cache_set( $query_cache_id, $query );
		}

		return $query;
	}

	/**
	 * Destroys the database and deletes all data
	 */
	public static function db_destroy() {
		global $wpdb;

		$wpdb->query( 'DROP TABLE IF EXISTS `wordpress`.`mm_wpcf7_meta`;' );
	}

	/**
	 * Constructor
	 *
	 * @param string $id the id of the database.
	 */
	public function __construct( $id ) {

	}
}
