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

namespace ArrayPress\Utils\Transients;

use ArrayPress\Utils\Database\Generate;

/**
 * Class Transients
 *
 * Utility functions for working with multiple transients.
 */
class Transients {

	/**
	 * Check if multiple transients exist.
	 *
	 * @param array $names An array of transient names to check.
	 *
	 * @return array An array of existing transient names.
	 */
	public static function exists( array $names ): array {
		if ( empty( $names ) ) {
			return [];
		}

		return array_filter( $names, function ( $name ) {
			return Transient::exists( $name );
		} );
	}

	/**
	 * Get multiple transients at once.
	 *
	 * @param array $names               An array of transient names to retrieve.
	 * @param bool  $include_expired     Whether to include expired transients in results. Default false.
	 * @param bool  $include_nonexistent Whether to include non-existent transients in result. Default false.
	 * @param mixed $default             Default value for non-existent transients if $include_nonexistent is true.
	 *
	 * @return array Array of transients with names as keys and their values.
	 */
	public static function get( array $names, bool $include_expired = false, bool $include_nonexistent = false, $default = false ): array {
		if ( empty( $names ) ) {
			return [];
		}

		$results = [];
		foreach ( $names as $name ) {
			$value = Transient::get( $name );

			if ( $value !== false ) {
				$results[ $name ] = $value;
			} elseif ( $include_expired && Transient::is_expired( $name ) ) {
				$results[ $name ] = null; // Expired transient
			} elseif ( $include_nonexistent ) {
				$results[ $name ] = $default;
			}
		}

		return $results;
	}

	/**
	 * Set multiple transients at once.
	 *
	 * @param array $transients Array of transient names and their values.
	 * @param int   $expiration Time until expiration in seconds. Default 0 (no expiration).
	 *
	 * @return array Array of successfully set transient names.
	 */
	public static function set( array $transients, int $expiration = 0 ): array {
		if ( empty( $transients ) ) {
			return [];
		}

		$set = [];
		foreach ( $transients as $name => $value ) {
			if ( Transient::set( $name, $value, $expiration ) ) {
				$set[] = $name;
			}
		}

		return $set;
	}

	/**
	 * Delete multiple transients.
	 *
	 * @param array $names Array of transient names to delete.
	 *
	 * @return int Number of transients successfully deleted.
	 */
	public static function delete( array $names ): int {
		if ( empty( $names ) ) {
			return 0;
		}

		return array_reduce( $names, function ( $count, $name ) {
			return $count + ( Transient::delete( $name ) ? 1 : 0 );
		}, 0 );
	}

	/**
	 * Get expiration times for multiple transients.
	 *
	 * @param array $names Array of transient names.
	 *
	 * @return array Array of transient names and their expiration times in seconds.
	 */
	public static function get_expirations( array $names ): array {
		if ( empty( $names ) ) {
			return [];
		}

		$expirations = [];
		foreach ( $names as $name ) {
			$expiration = Transient::get_expiration( $name );
			if ( $expiration !== false ) {
				$expirations[ $name ] = $expiration;
			}
		}

		return $expirations;
	}

	/**
	 * Check which transients have expired.
	 *
	 * @param array $names Array of transient names to check.
	 *
	 * @return array Array of expired transient names.
	 */
	public static function get_expired( array $names ): array {
		if ( empty( $names ) ) {
			return [];
		}

		return array_filter( $names, function ( $name ) {
			return Transient::is_expired( $name );
		} );
	}

	/**
	 * Extend expiration time for multiple transients.
	 *
	 * @param array $names     Array of transient names.
	 * @param int   $extend_by Time to extend by in seconds.
	 *
	 * @return array Array of transient names successfully extended.
	 */
	public static function extend_expirations( array $names, int $extend_by ): array {
		if ( empty( $names ) ) {
			return [];
		}

		$extended = [];
		foreach ( $names as $name ) {
			if ( Transient::extend_expiration( $name, $extend_by ) ) {
				$extended[] = $name;
			}
		}

		return $extended;
	}

	/**
	 * Get the size of multiple transients in bytes.
	 *
	 * @param array $names Array of transient names.
	 *
	 * @return array Array of transient names and their sizes in bytes.
	 */
	public static function get_sizes( array $names ): array {
		if ( empty( $names ) ) {
			return [];
		}

		$sizes = [];
		foreach ( $names as $name ) {
			$size = Transient::get_size( $name );
			if ( $size > 0 ) {
				$sizes[ $name ] = $size;
			}
		}

		return $sizes;
	}

	/**
	 * Get all transients.
	 *
	 * @param bool $include_values Whether to include the transient values. Default true.
	 *
	 * @return array An array of all transients.
	 */
	public static function get_all( bool $include_values = true ): array {
		global $wpdb;

		$query = "SELECT option_name 
              FROM $wpdb->options 
              WHERE option_name LIKE '_transient_%'
              AND option_name NOT LIKE '_transient_timeout_%'";

		$transients = $wpdb->get_col( $query );

		if ( ! $include_values ) {
			return array_map( function ( $transient ) {
				return str_replace( '_transient_', '', $transient );
			}, $transients );
		}

		$result = [];
		foreach ( $transients as $transient ) {
			$transient_name = str_replace( '_transient_', '', $transient );
			$value          = Transient::get( $transient_name );
			if ( $value !== false ) {
				$result[ $transient_name ] = $value;
			}
		}

		return $result;
	}

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

		$wildcard       = $wpdb->esc_like( '_transient_' . $prefix ) . '%';
		$prepared_query = $wpdb->prepare(
			"SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",
			$wildcard
		);

		$transients = $wpdb->get_col( $prepared_query );

		if ( ! $include_values ) {
			return array_map( function ( $transient ) {
				return str_replace( '_transient_', '', $transient );
			}, $transients );
		}

		$result = [];
		foreach ( $transients as $transient ) {
			$transient_name = str_replace( '_transient_', '', $transient );
			$value          = Transient::get( $transient_name );
			if ( $value !== false ) {
				$result[ $transient_name ] = $value;
			}
		}

		return $result;
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

		$sql_pattern    = Generate::like_pattern( $pattern, $type );
		$prepared_query = $wpdb->prepare(
			"SELECT option_name FROM $wpdb->options 
             WHERE option_name LIKE %s 
             AND option_name LIKE '_transient_%'",
			$sql_pattern
		);

		$transients = $wpdb->get_col( $prepared_query );

		$count = 0;
		foreach ( $transients as $transient ) {
			$transient_name = str_replace( '_transient_', '', $transient );
			if ( Transient::delete( $transient_name ) ) {
				$count ++;
			}
		}

		return $count;
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
}