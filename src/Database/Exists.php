<?php
/**
 * Database Existence Checks for WordPress
 *
 * This class provides utility functions for verifying the existence of database elements in WordPress.
 * It includes methods for checking if rows, tables, columns, and specific values exist in the database.
 * The class is designed to simplify common database existence checks and enhance the safety and
 * efficiency of database operations in WordPress.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.1
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Database;

/**
 * Check if the class `Exists` is defined, and if not, define it.
 */
if ( ! class_exists( 'Exists' ) ) :

	/**
	 * Database Utility Class
	 *
	 * This class provides a range of utility functions for constructing and executing SQL queries in WordPress.
	 * It includes methods for generating SQL patterns, preparing IN clauses, handling numeric and string comparisons,
	 * building complex query conditions, and safely executing SQL queries. The class is designed to facilitate
	 * common database interactions and enhance the readability and maintainability of SQL query generation in WordPress.
	 */
	class Exists {

		/**
		 * Safely check if a row exists in a specified table.
		 *
		 * @param string $table  The name of the table to check (without prefix).
		 * @param string $column The column to check against.
		 * @param mixed  $value  The value to look for.
		 *
		 * @return bool True if the row exists, false otherwise.
		 */
		public static function row( string $table, string $column, ?int $value ): bool {
			global $wpdb;

			// Validate input
			if ( empty( $table ) || empty( $column ) || $value === null ) {
				return false;
			}

			// Sanitize table and column names
			$table  = sanitize_key( $table );
			$column = sanitize_key( $column );

			// Construct the query
			$sql = $wpdb->prepare(
				"SELECT EXISTS(SELECT 1 FROM {$wpdb->prefix}{$table} WHERE {$column} = %d LIMIT 1) AS result",
				$value
			);

			// Execute the query
			$exists = $wpdb->get_var( $sql );

			return $exists === '1';
		}

		/**
		 * Check if a table exists in the database.
		 *
		 * @param string $table The name of the table to check (without prefix).
		 *
		 * @return bool True if the table exists, false otherwise.
		 */
		public static function table( string $table ): bool {
			global $wpdb;

			$table = sanitize_key( $table );
			$query = $wpdb->prepare(
				"SHOW TABLES LIKE %s",
				$wpdb->prefix . $table
			);

			return (bool) $wpdb->get_var( $query );
		}

		/**
		 * Check if a column exists in a specified table.
		 *
		 * @param string $table  The name of the table to check (without prefix).
		 * @param string $column The name of the column to check.
		 *
		 * @return bool True if the column exists, false otherwise.
		 */
		public static function column( string $table, string $column ): bool {
			global $wpdb;

			$table  = sanitize_key( $table );
			$column = sanitize_key( $column );

			$query = $wpdb->prepare(
				"SHOW COLUMNS FROM {$wpdb->prefix}{$table} LIKE %s",
				$column
			);

			return (bool) $wpdb->get_var( $query );
		}

	}

endif;