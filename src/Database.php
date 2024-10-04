<?php
/**
 * Database Utilities for WordPress
 *
 * This class provides utility functions for working with SQL queries and database operations in WordPress.
 * It includes methods for generating SQL clauses like WHERE, IN, JOIN, ORDER BY, and LIMIT, as well as executing
 * custom SQL queries, handling placeholders, and sanitizing input. The class simplifies constructing SQL queries
 * and executing them safely within WordPress's $wpdb environment.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

/**
 * Check if the class `Database` is defined, and if not, define it.
 */
if ( ! class_exists( 'Database' ) ) :

	/**
	 * Database Utility Class
	 *
	 * This class provides a range of utility functions for constructing and executing SQL queries in WordPress.
	 * It includes methods for generating SQL patterns, preparing IN clauses, handling numeric and string comparisons,
	 * building complex query conditions, and safely executing SQL queries. The class is designed to facilitate
	 * common database interactions and enhance the readability and maintainability of SQL query generation in WordPress.
	 */
	class Database {

		/**
		 * Safely check if a row exists in a specified table.
		 *
		 * @param string $table  The name of the table to check (without prefix).
		 * @param string $column The column to check against.
		 * @param mixed  $value  The value to look for.
		 *
		 * @return bool True if the row exists, false otherwise.
		 */
		public static function row_exists( string $table, string $column, ?int $value ): bool {
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
		public static function table_exists( string $table ): bool {
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
		public static function column_exists( string $table, string $column ): bool {
			global $wpdb;

			$table  = sanitize_key( $table );
			$column = sanitize_key( $column );

			$query = $wpdb->prepare(
				"SHOW COLUMNS FROM {$wpdb->prefix}{$table} LIKE %s",
				$column
			);

			return (bool) $wpdb->get_var( $query );
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

			if ( ! self::table_exists( $table ) ) {
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

			// If still no table is found, use WordPress's get_metadata_table() function if available
			if ( function_exists( 'get_metadata_table' ) ) {
				$table_name = get_metadata_table( $meta_type );
				if ( $table_name ) {
					return $table_name;
				}
			}

			return null;
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
			if ( self::column_exists( self::get_table_name( $object_type ), $custom_key ) ) {
				return $custom_key;
			}

			// Check for 'id' column
			if ( self::column_exists( self::get_table_name( $object_type ), 'id' ) ) {
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
		 * Check if the current query is for the main site in a multisite network.
		 *
		 * @return bool True if it's the main site query, false otherwise.
		 */
		public static function is_main_site_query(): bool {
			return is_main_site();
		}

		/**
		 * Get the blog prefix for a specific blog ID in a multisite network.
		 *
		 * @param int|null $blog_id The blog ID. Null for the current blog.
		 *
		 * @return string The blog prefix.
		 */
		public static function get_blog_prefix( ?int $blog_id = null ): string {
			global $wpdb;

			return $wpdb->get_blog_prefix( $blog_id );
		}

	}

	// Create an alias 'DB' for the Database class
	class_alias( 'ArrayPress\Utils\Database', 'ArrayPress\Utils\DB' );

endif;