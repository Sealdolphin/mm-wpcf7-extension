<?php
/**
 * Represents a row in a database
 *
 * @package modules/admin
 */

namespace mm_wpcf7;

/**
 * Represents a row in a database
 */
class Database_Row {

	/**
	 * Basic constructor
	 *
	 * @param string $table_name is the name of the table.
	 * @param string $row is the raw unfiltered row data.
	 */
	public function __construct( $table_name, $row ) {
		global $wpdb;
		$data = explode( ';', $row, 3 );

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
}
