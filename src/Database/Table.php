<?php
/**
 * WordPress Table Name Utilities
 *
 * This class provides utility functions for retrieving WordPress table names.
 * It includes methods for getting both standard and metadata table names for
 * different object types in WordPress.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Database;

class Table {

	/**
	 * Get the table name for a given object type.
	 *
	 * @param string $object_type The type of object (e.g., 'post', 'user', 'term', 'comment', 'product', 'order').
	 *
	 * @return string|null The table name. Null on failure.
	 */
	public static function get_table_name( string $object_type ): ?string {
		global $wpdb;

		// Check for standard WordPress tables
		$standard_tables = [
			'post'    => $wpdb->posts,
			'user'    => $wpdb->users,
			'term'    => $wpdb->terms,
			'comment' => $wpdb->comments,
		];

		if ( isset( $standard_tables[ $object_type ] ) ) {
			return $standard_tables[ $object_type ];
		}

		// Array of possible variations to check
		$variations = [
			$object_type,
			rtrim( $object_type, 's' ),
			$object_type . 's',
			$object_type . 'es',
			substr( $object_type, 0, - 1 ) . 'ies', // For plurals like "category" -> "categories"
		];

		// Check for custom registered tables
		foreach ( $variations as $variation ) {
			if ( isset( $wpdb->$variation ) ) {
				return $wpdb->$variation;
			}
		}

		// If no table is found, check if there's a constant defined for this table
		$constant_name = strtoupper( $wpdb->prefix . $object_type );
		if ( defined( $constant_name ) ) {
			return constant( $constant_name );
		}

		return null;
	}

	/**
	 * Get the name of the metadata table for a given object type.
	 *
	 * @param string $meta_type The type of object metadata is for (e.g., 'post', 'user', 'term', 'comment').
	 *
	 * @return string The name of the metadata table. Null on failure.
	 */
	public static function get_meta_table_name( string $meta_type ): ?string {
		global $wpdb;

		// Check for standard WordPress metadata tables
		$standard_meta_tables = [
			'post'    => $wpdb->postmeta,
			'user'    => $wpdb->usermeta,
			'term'    => $wpdb->termmeta,
			'comment' => $wpdb->commentmeta,
		];

		if ( isset( $standard_meta_tables[ $meta_type ] ) ) {
			return $standard_meta_tables[ $meta_type ];
		}

		// Array of possible variations to check for custom meta tables
		$variations = [
			$meta_type . 'meta',
			$meta_type . '_meta',
			rtrim( $meta_type, 's' ) . 'meta',
			rtrim( $meta_type, 's' ) . '_meta',
		];

		// Check for custom registered meta tables
		foreach ( $variations as $variation ) {
			if ( isset( $wpdb->$variation ) ) {
				return $wpdb->$variation;
			}
		}

		// If no table is found, check if there's a constant defined for this meta table
		$constant_name = strtoupper( $wpdb->prefix . $meta_type . '_META' );
		if ( defined( $constant_name ) ) {
			return constant( $constant_name );
		}

		return null;
	}

	/**
	 * Get the schema (structure) of a specified table.
	 *
	 * @param string $table The name of the table (without prefix).
	 *
	 * @return array|false An array of column information, or false if the table doesn't exist.
	 */
	public static function get_schema( string $table ) {
		global $wpdb;

		$table = sanitize_key( $table );

		if ( ! Exists::table( $table ) ) {
			return false;
		}

		$query   = "DESCRIBE {$wpdb->prefix}{$table}";
		$results = $wpdb->get_results( $query, ARRAY_A );

		if ( ! $results ) {
			return false;
		}

		$schema = [];
		foreach ( $results as $row ) {
			$schema[ $row['Field'] ] = [
				'type'    => $row['Type'],
				'null'    => $row['Null'],
				'key'     => $row['Key'],
				'default' => $row['Default'],
				'extra'   => $row['Extra'],
			];
		}

		return $schema;
	}

	/**
	 * Get the data type of a column in a table.
	 *
	 * @param string $table_name  The name of the table.
	 * @param string $column_name The name of the column.
	 *
	 * @return string|null The data type of the column, or null if the column doesn't exist.
	 */
	public static function get_column_data_type( string $table_name, string $column_name ): ?string {
		global $wpdb;
		$result = $wpdb->get_row( $wpdb->prepare(
			"SHOW COLUMNS FROM `$table_name` LIKE %s",
			$column_name
		) );

		return $result ? $result->Type : null;
	}

	/**
	 * Retrieves the MySQL server version.
	 *
	 * @return array Version information.
	 */
	public static function get_version(): array {
		global $wpdb;

		if ( empty( $wpdb->is_mysql ) || ! $wpdb->use_mysqli ) {
			return [
				'string' => '',
				'number' => '',
			];
		}

		$server_info = mysqli_get_server_info( $wpdb->dbh );

		return [
			'string' => $server_info,
			'number' => preg_replace( '/([^\d.]+).*/', '', $server_info ),
		];
	}

	/**
	 * Get the primary key column name for a given object type.
	 *
	 * @param string $object_type The type of object (e.g., 'post', 'user', 'term', 'comment').
	 *
	 * @return string|null The primary key column name, or null if not found.
	 */
	public static function get_primary_key_column( string $object_type ): ?string {
		$standard_keys = [
			'user'    => 'ID',
			'post'    => 'ID',
			'term'    => 'term_id',
			'comment' => 'comment_ID',
		];

		if ( isset( $standard_keys[ $object_type ] ) ) {
			return $standard_keys[ $object_type ];
		}

		// Check for custom object types
		$custom_key = $object_type . '_id';
		if ( Exists::column( self::get_table_name( $object_type ), $custom_key ) ) {
			return $custom_key;
		}

		// Check for 'id' column
		if ( Exists::column( self::get_table_name( $object_type ), 'id' ) ) {
			return 'id';
		}

		// If no primary key is found, return null
		return null;
	}

	/**
	 * Get the charset collate string for the database.
	 *
	 * @return string The charset collate string.
	 */
	public static function get_charset_collate(): string {
		global $wpdb;

		return $wpdb->get_charset_collate();
	}

	/**
	 * Get the prefix for WordPress database tables.
	 *
	 * @return string The database table prefix.
	 */
	public static function get_table_prefix(): string {
		global $wpdb;

		return $wpdb->prefix;
	}

}