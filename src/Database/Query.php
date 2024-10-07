<?php
/**
 * Database Query Utilities for WordPress
 *
 * This class provides utility functions for working with database queries in WordPress.
 * It includes methods for retrieving table names, handling metadata tables, and managing
 * multisite-specific query operations. The class is designed to simplify common database
 * interactions and enhance the consistency of database operations across different
 * WordPress setups, including multisite environments.
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
 * Check if the class `Query` is defined, and if not, define it.
 */
if ( ! class_exists( 'Query' ) ) :

	/**
	 * Database Query Utility Class
	 *
	 * This class provides a set of static methods for common database query operations in WordPress.
	 * It focuses on:
	 * 1. Retrieving correct table names for various WordPress object types.
	 * 2. Handling metadata table names for different object types.
	 * 3. Managing multisite-specific query operations.
	 *
	 * The class is designed to enhance the reliability and consistency of database interactions
	 * across different WordPress configurations, including single-site and multisite setups.
	 * It aims to simplify the process of working with WordPress's database structure and
	 * provide a centralized set of utilities for database-related operations.
	 */
	class Query {

		/**
		 * Check if a specific value exists in a specified table and column.
		 *
		 * @param string $table  The name of the table to check (without prefix).
		 * @param string $column The name of the column to check.
		 * @param mixed  $value  The value to look for.
		 *
		 * @return bool True if the value exists, false otherwise.
		 */
		public static function get_value( string $table, string $column, $value ): bool {
			global $wpdb;

			// Validate input
			if ( empty( $table ) || empty( $column ) || $value === null ) {
				return false;
			}

			// Sanitize table and column names
			$table  = sanitize_key( $table );
			$column = sanitize_key( $column );

			// Prepare the query based on the value type
			if ( is_numeric( $value ) ) {
				$sql = $wpdb->prepare(
					"SELECT EXISTS(SELECT 1 FROM {$wpdb->prefix}{$table} WHERE {$column} = %d LIMIT 1) AS result",
					$value
				);
			} else {
				$sql = $wpdb->prepare(
					"SELECT EXISTS(SELECT 1 FROM {$wpdb->prefix}{$table} WHERE {$column} = %s LIMIT 1) AS result",
					$value
				);
			}

			// Execute the query
			$exists = $wpdb->get_var( $sql );

			return $exists === '1';
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
		 * Check if the current query is for the main site in a multisite network.
		 *
		 * @return bool True if it's the main site query, false otherwise.
		 */
		public static function is_main_site(): bool {
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

endif;