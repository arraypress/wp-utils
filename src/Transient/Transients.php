<?php
/**
 * Transients Utilities for WordPress
 *
 * This class provides utility functions for handling WordPress transients, offering
 * methods for safely retrieving, setting, incrementing, and deleting transients.
 * It includes functionality for working with expiration times, patterns, and
 * managing transients by prefix, suffix, or substring.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Transient;

use ArrayPress\Utils\Database\SQL;

/**
 * Check if the class `Transients` is defined, and if not, define it.
 */
if ( ! class_exists( 'Transients' ) ) :

	/**
	 * Transients Utilities
	 *
	 * Provides utility functions for managing WordPress transients, including
	 * methods for getting transients with defaults, incrementing/decrementing values,
	 * and handling transients based on patterns.
	 */
	class Transients {

		/**
		 * Get all transients with a specified prefix.
		 *
		 * @param string $prefix         Prefix to search for in transient keys.
		 * @param bool   $include_values Whether to include the transient values. Default true.
		 *
		 * @return array An array of transients with the specified prefix.
		 */
		public static function get_prefixed( string $prefix, bool $include_values = true ): array {
			global $wpdb;

			$wildcard = $wpdb->esc_like( '_transient_' . $prefix ) . '%';

			$query = "SELECT option_name 
              FROM $wpdb->options 
              WHERE option_name LIKE %s";

			$prepared_query = $wpdb->prepare( $query, $wildcard );

			$transients = $wpdb->get_col( $prepared_query );

			if ( ! $include_values ) {
				return array_map( function ( $transient ) {
					return str_replace( '_transient_', '', $transient );
				}, $transients );
			}

			$result = [];
			foreach ( $transients as $transient ) {
				$transient_name            = str_replace( '_transient_', '', $transient );
				$result[ $transient_name ] = get_transient( $transient_name );
			}

			return $result;
		}

		/**
		 * Delete all transients that are prefixed with a specific string.
		 *
		 * @param string $prefix The prefix to search for.
		 *
		 * @return int The number of transients deleted.
		 */
		public static function delete_by_prefix( string $prefix ): int {
			return self::delete_by_pattern( $prefix, 'prefix' );
		}

		/**
		 * Delete all transients that are suffixed with a specific string.
		 *
		 * @param string $suffix The suffix to search for.
		 *
		 * @return int The number of transients deleted.
		 */
		public static function delete_by_suffix( string $suffix ): int {
			return self::delete_by_pattern( $suffix, 'suffix' );
		}

		/**
		 * Delete all transients that contain a specific string.
		 *
		 * @param string $substring The substring to search for in transient names.
		 *
		 * @return int The number of transients deleted.
		 */
		public static function delete_by_substring( string $substring ): int {
			return self::delete_by_pattern( $substring, 'substring' );
		}

		/**
		 * Delete transients based on a pattern.
		 *
		 * @param string $pattern The pattern to match against transient names.
		 * @param string $type    The type of pattern matching: 'prefix', 'suffix', 'substring', or 'exact'.
		 *
		 * @return int The number of transients deleted.
		 */
		public static function delete_by_pattern( string $pattern, string $type = 'exact' ): int {
			global $wpdb;

			$sql_pattern = SQL::generate_like_pattern( $pattern, $type );

			$query = "SELECT option_name 
              FROM {$wpdb->options} 
              WHERE option_name LIKE %s 
              AND option_name LIKE '_transient_%'";

			$prepared_query = $wpdb->prepare( $query, $sql_pattern );

			$transients = $wpdb->get_col( $prepared_query );

			$count = 0;
			foreach ( $transients as $transient ) {
				$transient_name = str_replace( '_transient_', '', $transient );
				if ( delete_transient( $transient_name ) ) {
					$count ++;
				}
			}

			return $count;
		}

	}
endif;