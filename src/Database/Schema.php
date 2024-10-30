<?php
/**
 * Database Schema Utilities for WordPress
 *
 * This class provides utility functions for working with database schema and structure in WordPress.
 * It includes methods for retrieving table schemas, column information, database version details,
 * and other schema-related operations. The class simplifies interactions with the WordPress database
 * structure and provides a centralized set of utilities for schema-related queries and information retrieval.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Database;

/**
 * Check if the class `Schema` is defined, and if not, define it.
 */
if ( ! class_exists( 'Schema' ) ) :

	/**
	 * Database Schema Utility Class
	 *
	 * This class provides a set of static methods for interacting with and retrieving information
	 * about the WordPress database schema. It focuses on:
	 * 1. Retrieving table schemas and column information.
	 * 2. Getting database version and configuration details.
	 * 3. Identifying primary keys for different object types.
	 * 4. Retrieving database character set and collation information.
	 *
	 * The class is designed to simplify database schema operations and provide developers
	 * with easy access to structural information about the WordPress database. It enhances
	 * the ability to work with database schemas across different WordPress configurations
	 * and versions.
	 */
	class Schema {

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
			if ( Exists::column( Table::get_table_name( $object_type ), $custom_key ) ) {
				return $custom_key;
			}

			// Check for 'id' column
			if ( Exists::column( Table::get_table_name( $object_type ), 'id' ) ) {
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

endif;