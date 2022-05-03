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
		$meta_table = self::$meta_db;
		$databases  = self::list_databases();

		foreach ( $databases as $database ) {
			$database->destroy();
		}

		$wpdb->query( "DROP TABLE IF EXISTS `wordpress`.`$meta_table`;" );
	}

	/**
	 * Constructor
	 *
	 * @param string $id the id of the database.
	 * @param string $description the description of the database.
	 */
	public function __construct( $id, $description = '' ) {
		$this->name        = $id;
		$this->description = $description;
		$this->records     = 0;
	}

	/**
	 * Uploads a database to the SQL
	 *
	 * @param array $data the data to upload.
	 */
	public function upload( $data ) {
		global $wpdb;
		$table_name = self::$db_prefix . $this->name;
		$meta_table = self::$meta_db;

		$query = $wpdb->query(
			"CREATE TABLE IF NOT EXISTS `wordpress`.`$table_name` (
				`id` VARCHAR(45) NOT NULL,
				`name` VARCHAR(45) CHARACTER SET 'utf8' NOT NULL,
				`other` VARCHAR(45) CHARACTER SET 'utf8' NOT NULL,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `id_UNIQUE` (`id` ASC));
			"
		);

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO `wordpress`.`$meta_table` (`db_id`, `db_description`)
				VALUES (%s, %s);",
				$this->name,
				$this->description
			)
		);

		foreach ( $data as $row ) {
			$this->upload_row( $row );
		}
	}

	/**
	 * Uploads a row to the database
	 *
	 * @param string $row a row in the data.
	 */
	private function upload_row( $row ) {
		global $wpdb;
		$data       = explode( ';', $row, 3 );
		$table_name = self::$db_prefix . $this->name;

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO `wordpress`.`$table_name` (`id`, `name`, `other`)
				VALUES (%s, %s, %s);",
				$data[0],
				$data[1],
				$data[2]
			)
		);
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
	 * Deletes this from the SQL database.
	 */
	public function destroy() {
		global $wpdb;
		$table_name = self::$db_prefix . $this->name;

		$wpdb->query( "DROP TABLE IF EXISTS `wordpress`.`$table_name`;" );
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

	/**
	 * Determines if this database exists.
	 */
	public function exists(): bool {
		global $wpdb;

		$query_cache_id = 'mm_wpcf7_db_exists';
		$exists         = wp_cache_get( $query_cache_id );
		$table_name     = self::$db_prefix . $this->name;

		if ( false === $exists ) {
			$tables = $wpdb->query(
				$wpdb->prepare(
					'SHOW TABLES LIKE %s;',
					$table_name
				)
			);
			$exists = $tables > 0;
			wp_cache_set( $query_cache_id, $exists );
		}

		return $exists;
	}

	/**
	 * Create options from the record rows.
	 */
	public function create_options_array() {
		global $wpdb;

		$query_cache_id = 'mm_wpcf7_db_get';
		$record_array   = wp_cache_get( $query_cache_id );
		$table_name     = self::$db_prefix . $this->name;

		if ( false === $record_array ) {
			$record_array = array();
			$records      = $wpdb->get_results( "SELECT * FROM `wordpress`.`$table_name`;" );

			foreach ( $records as $record ) {
				$record_array[ $record->id ] = $record->name . ' - ' . $record->other;
			}
			wp_cache_set( $query_cache_id, $record_array );
		}

		return $record_array;
	}
}
