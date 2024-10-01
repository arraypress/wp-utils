<?php
/**
 * Options Utilities for WordPress
 *
 * This class provides utility functions for handling WordPress options, offering
 * methods for safely retrieving, updating, incrementing, and deleting options.
 * It includes functionality for type casting, working with arrays, and deleting options
 * by patterns, prefixes, or suffixes.
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
 * Check if the class `Options` is defined, and if not, define it.
 */
if ( ! class_exists( 'Options' ) ) :

	/**
	 * Options Utilities
	 *
	 * Provides utility functions for managing WordPress options, including
	 * methods for getting options with defaults, incrementing/decrementing values,
	 * and handling array-based options.
	 */
	class Options {

		/**
		 * Delete options based on a pattern.
		 *
		 * @param string $pattern The pattern to match against option names.
		 * @param string $type    The type of pattern matching: 'prefix', 'suffix', 'substring', or 'exact'.
		 *
		 * @return int The number of options deleted.
		 */
		public static function delete_by_pattern( string $pattern, string $type = 'exact' ): int {
			global $wpdb;

			$sql_pattern = Database::generate_like_pattern( $pattern, $type );

			$options = $wpdb->get_col( $wpdb->prepare(
				"SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",
				$sql_pattern
			) );

			return self::delete_multiple( $options );
		}

		/**
		 * Delete all options that are prefixed with a specific string.
		 *
		 * @param string $prefix The prefix to search for.
		 *
		 * @return int The number of options deleted.
		 */
		public static function delete_by_prefix( string $prefix ): int {
			return self::delete_by_pattern( $prefix, 'prefix' );
		}

		/**
		 * Delete all options that are suffixed with a specific string.
		 *
		 * @param string $suffix The suffix to search for.
		 *
		 * @return int The number of options deleted.
		 */
		public static function delete_by_suffix( string $suffix ): int {
			return self::delete_by_pattern( $suffix, 'suffix' );
		}

		/**
		 * Delete all options that contain a specific string.
		 *
		 * @param string $substring The substring to search for in option names.
		 *
		 * @return int The number of options deleted.
		 */
		public static function delete_by_substring( string $substring ): int {
			return self::delete_by_pattern( $substring, 'substring' );
		}

		/**
		 * Delete multiple options by their keys.
		 *
		 * @param array $keys An array of option keys to delete.
		 *
		 * @return int The number of options successfully deleted.
		 */
		public static function delete_multiple( array $keys ): int {
			return array_reduce( $keys, function ( $count, $key ) {
				return $count + ( delete_option( $key ) ? 1 : 0 );
			}, 0 );
		}

	}
endif;