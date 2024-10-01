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
		 * @param string $object_type The type of object (e.g., 'post', 'user', 'term', 'comment').
		 *
		 * @return string The table name.
		 * @throws \InvalidArgumentException If an invalid object type is provided.
		 */
		public static function get_table_name( string $object_type ): string {
			global $wpdb;

			switch ( $object_type ) {
				case 'post':
					return $wpdb->posts;
				case 'user':
					return $wpdb->users;
				case 'term':
					return $wpdb->terms;
				case 'comment':
					return $wpdb->comments;
				default:
					throw new \InvalidArgumentException( "Invalid object type: $object_type" );
			}
		}

		/**
		 * Get the name of the metadata table for a given object type.
		 *
		 * @param string $meta_type The type of object metadata is for (e.g., 'post', 'user', 'term').
		 *
		 * @return string The name of the metadata table.
		 * @throws \InvalidArgumentException If an invalid meta type is provided.
		 */
		public static function get_meta_table_name( string $meta_type ): string {
			global $wpdb;

			switch ( $meta_type ) {
				case 'post':
					return $wpdb->postmeta;
				case 'user':
					return $wpdb->usermeta;
				case 'term':
					return $wpdb->termmeta;
				case 'comment':
					return $wpdb->commentmeta;
				default:
					throw new \InvalidArgumentException( "Invalid meta type: $meta_type" );
			}
		}

		/**
		 * Get the primary key column name for a given object type.
		 *
		 * @param string $object_type The type of object (e.g., 'post', 'user', 'term', 'comment').
		 *
		 * @return string The primary key column name.
		 * @throws \InvalidArgumentException If an invalid object type is provided.
		 */
		public static function get_primary_key_column( string $object_type ): string {
			switch ( $object_type ) {
				case 'user':
				case 'post':
					return 'ID';
				case 'term':
					return 'term_id';
				case 'comment':
					return 'comment_ID';
				default:
					throw new \InvalidArgumentException( "Invalid object type: $object_type" );
			}
		}

		/**
		 * Check if a table exists in the database.
		 *
		 * @param string $table_name The name of the table to check.
		 *
		 * @return bool True if the table exists, false otherwise.
		 */
		public static function table_exists( string $table_name ): bool {
			global $wpdb;
			$result = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

			return $result === $table_name;
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
		 * Check if a column exists in a table.
		 *
		 * @param string $table_name  The name of the table.
		 * @param string $column_name The name of the column.
		 *
		 * @return bool True if the column exists, false otherwise.
		 */
		public static function column_exists( string $table_name, string $column_name ): bool {
			global $wpdb;
			$result = $wpdb->get_results( $wpdb->prepare(
				"SHOW COLUMNS FROM `$table_name` LIKE %s",
				$column_name
			) );

			return ! empty( $result );
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

		/**
		 * Convert a database error to a WP_Error object.
		 *
		 * @param string $error The database error message.
		 *
		 * @return \WP_Error The WP_Error object.
		 */
		public static function db_error_to_wp_error( string $error ): \WP_Error {
			return new \WP_Error( 'database_error', $error );
		}



	}
endif;