<?php
/**
 * WordPress Database Query Utilities
 *
 * Provides utility functions for working with WordPress database queries.
 * This class offers methods for handling database prefixes, multisite operations,
 * and common database-level query tasks.
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
	 * WordPress Database Query Utility Class
	 *
	 * Provides methods for common WordPress database operations including:
	 * 1. Multisite prefix handling
	 * 2. Database query preparation
	 * 3. Network and site-specific query utilities
	 */
	class Query {

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

		/**
		 * Get the current blog ID.
		 *
		 * @return int The current blog ID.
		 */
		public static function get_current_blog_id(): int {
			return get_current_blog_id();
		}

		/**
		 * Get the last inserted ID from the database.
		 *
		 * @return int The last insert ID.
		 */
		public static function get_insert_id(): int {
			global $wpdb;

			return $wpdb->insert_id;
		}

		/**
		 * Get the number of rows affected by the last query.
		 *
		 * @return int Number of rows affected.
		 */
		public static function get_affected_rows(): int {
			global $wpdb;

			return $wpdb->rows_affected;
		}

		/**
		 * Begin a database transaction.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function begin_transaction(): bool {
			global $wpdb;

			return $wpdb->query( 'START TRANSACTION' ) !== false;
		}

		/**
		 * Commit a database transaction.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function commit(): bool {
			global $wpdb;

			return $wpdb->query( 'COMMIT' ) !== false;
		}

		/**
		 * Rollback a database transaction.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function rollback(): bool {
			global $wpdb;

			return $wpdb->query( 'ROLLBACK' ) !== false;
		}
	}

endif;