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
	 * Prefix for meta db
	 *
	 * @var string $db_prefix the prefix of all db-s
	 */
	private static string $meta_db = 'mm_wpcf7_meta';

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
		$table_name     = self::$meta_db;

		if ( false === $query ) {
			$query = $wpdb->query(
				"CREATE TABLE IF NOT EXISTS `wordpress`.`$table_name` (
					`id` INT NOT NULL AUTO_INCREMENT,
					`db_id` VARCHAR(45) NOT NULL,
					`db_description` VARCHAR(45) NULL,
					PRIMARY KEY (`id`),
					UNIQUE INDEX `db_id_UNIQUE` (`db_id` ASC),
					UNIQUE INDEX `id_UNIQUE` (`id` ASC));
				"
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
		$database_list  = wp_cache_get( $query_cache_id );
		$table_name     = self::$meta_db;

		if ( false === $database_list ) {
			$database_list = array();
			$databases     = $wpdb->get_results( "SELECT * FROM `wordpress`.`$table_name`;" );

			foreach ( $databases as $database ) {
				$database_list[] = new Database(
					$database->db_id,
					$database->db_description
				);
			}
			wp_cache_set( $query_cache_id, $database_list );
		}

		return $database_list;
	}

	/**
	 * Destroys the database and deletes all data
	 */
	public static function db_destroy() {
		global $wpdb;
		$table_name = self::$meta_db;

		$wpdb->query( "DROP TABLE IF EXISTS `wordpress`.`$table_name`;" );
	}

	/**
	 * Constructor
	 *
	 * @param string $id the id of the database.
	 * @param string $description the description of the database.
	 */
	public function __construct( $id, $description ) {
		$this->name        = $id;
		$this->description = $description;
		$this->records     = 0;
	}

	/**
	 * Updates the record value of the db class
	 */
	public function update_details() {
		global $wpdb;

		$query_cache_id = 'mm_wpcf7_db_records' . $this->name;
		$records        = wp_cache_get( $query_cache_id );
		$table_name     = self::$db_prefix . $this->name;

		if ( false === $records ) {
			$records = $wpdb->get_var( "SELECT COUNT(*) as `records` FROM `wordpress`.`$table_name`;" );
			wp_cache_set( $query_cache_id, $records );
		}

		$this->records = $records;
	}

	/**
	 * Returns the name of the DB
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Returns the records of the DB
	 */
	public function get_records() {
		return $this->records;
	}

	/**
	 * Returns the description of the DB
	 */
	public function get_description() {
		return $this->description;
	}
}
