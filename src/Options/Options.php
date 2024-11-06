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

namespace ArrayPress\Utils\Options;

use ArrayPress\Utils\Database\Generate;

/**
 * Class Options
 *
 * Utility functions for working with multiple options.
 */
class Options {

	/**
	 * Check if multiple options exist.
	 *
	 * @param array $option_names An array of option names to check.
	 *
	 * @return array An array of existing option names.
	 */
	public static function exists( array $option_names ): array {
		if ( empty( $option_names ) ) {
			return [];
		}

		return array_filter( $option_names, function ( $option_name ) {
			return get_option( $option_name, null ) !== null;
		} );
	}

	/**
	 * Get multiple options at once.
	 *
	 * @param array $option_names        An array of option names to retrieve.
	 * @param bool  $include_nonexistent Whether to include non-existent options in the result. Default false.
	 * @param mixed $default             Default value for non-existent options if $include_nonexistent is true.
	 *
	 * @return array Array of options with option names as keys and their values, or empty array on failure.
	 */
	public static function get( array $option_names, bool $include_nonexistent = false, $default = false ): array {
		if ( empty( $option_names ) ) {
			return [];
		}

		$options = [];
		foreach ( $option_names as $option_name ) {
			$value = get_option( $option_name, null );

			if ( $value !== null ) {
				$options[ $option_name ] = $value;
			} elseif ( $include_nonexistent ) {
				$options[ $option_name ] = $default;
			}
		}

		return $options;
	}

	/**
	 * Update multiple options at once.
	 *
	 * @param array $options        Array of option names and their values.
	 * @param bool  $skip_unchanged Whether to skip updating options that haven't changed. Default true.
	 *
	 * @return array Array of successfully updated option names.
	 */
	public static function update_multiple( array $options, bool $skip_unchanged = true ): array {
		if ( empty( $options ) ) {
			return [];
		}

		$updated = [];
		foreach ( $options as $option_name => $value ) {
			if ( $skip_unchanged ) {
				$current_value = get_option( $option_name );
				if ( $current_value === $value ) {
					continue;
				}
			}

			if ( update_option( $option_name, $value ) ) {
				$updated[] = $option_name;
			}
		}

		return $updated;
	}

	/**
	 * Compare multiple options against expected values.
	 *
	 * @param array $expected_values Array of option names and their expected values.
	 * @param bool  $strict          Whether to use strict comparison. Default true.
	 *
	 * @return array Array of options that don't match expected values.
	 */
	public static function compare_values( array $expected_values, bool $strict = true ): array {
		if ( empty( $expected_values ) ) {
			return [];
		}

		$mismatches = [];
		foreach ( $expected_values as $option_name => $expected_value ) {
			$actual_value = get_option( $option_name );

			if ( $strict ? $actual_value !== $expected_value : $actual_value != $expected_value ) {
				$mismatches[ $option_name ] = [
					'expected' => $expected_value,
					'actual'   => $actual_value
				];
			}
		}

		return $mismatches;
	}

	/**
	 * Filter options by their values using a callback function.
	 *
	 * @param array    $option_names Array of option names to filter.
	 * @param callable $callback     Function to filter options (receives option value as argument).
	 *
	 * @return array Array of option names and values that match the filter.
	 */
	public static function filter_by_value( array $option_names, callable $callback ): array {
		if ( empty( $option_names ) ) {
			return [];
		}

		$filtered = [];
		foreach ( $option_names as $option_name ) {
			$value = get_option( $option_name );
			if ( $callback( $value ) ) {
				$filtered[ $option_name ] = $value;
			}
		}

		return $filtered;
	}

	/**
	 * Get the total size of multiple options in bytes.
	 *
	 * @param array $option_names Array of option names to check.
	 *
	 * @return int Total size in bytes.
	 */
	public static function get_size( array $option_names ): int {
		if ( empty( $option_names ) ) {
			return 0;
		}

		$total_size = 0;
		foreach ( $option_names as $option_name ) {
			$value = get_option( $option_name );
			if ( $value !== false ) {
				$total_size += strlen( maybe_serialize( $value ) );
			}
		}

		return $total_size;
	}

	/**
	 * Find options that might be orphaned (no longer used by any active plugins or themes).
	 *
	 * @param array $option_names Array of option names to check.
	 * @param array $prefixes     Optional array of known plugin/theme prefixes to check against.
	 *
	 * @return array Array of potentially orphaned option names.
	 */
	public static function find_orphaned( array $option_names, array $prefixes = [] ): array {
		if ( empty( $option_names ) ) {
			return [];
		}

		// Get all active plugins and theme
		$active_plugins = get_option( 'active_plugins', [] );
		$current_theme  = get_stylesheet();

		// Build array of active plugin prefixes if none provided
		if ( empty( $prefixes ) ) {
			$prefixes   = array_map( function ( $plugin ) {
				return strtok( $plugin, '/' );
			}, $active_plugins );
			$prefixes[] = $current_theme;
		}

		// Find options that don't match any known prefixes
		return array_filter( $option_names, function ( $option_name ) use ( $prefixes ) {
			foreach ( $prefixes as $prefix ) {
				if ( strpos( $option_name, $prefix ) === 0 ) {
					return false;
				}
			}

			return true;
		} );
	}

	/**
	 * Backup multiple options to an array.
	 *
	 * @param array $option_names Array of option names to backup.
	 *
	 * @return array Array containing backup data with values and metadata.
	 */
	public static function backup( array $option_names ): array {
		if ( empty( $option_names ) ) {
			return [];
		}

		$backup = [
			'timestamp' => current_time( 'timestamp' ),
			'options'   => []
		];

		foreach ( $option_names as $option_name ) {
			$value = get_option( $option_name );
			if ( $value !== false ) {
				$backup['options'][ $option_name ] = [
					'value' => $value,
					'size'  => strlen( maybe_serialize( $value ) ),
					'type'  => gettype( $value )
				];
			}
		}

		return $backup;
	}

	/**
	 * Restore multiple options from a backup array.
	 *
	 * @param array $backup Backup array created by backup() method.
	 *
	 * @return array Array of restored option names.
	 */
	public static function restore( array $backup ): array {
		if ( empty( $backup['options'] ) ) {
			return [];
		}

		$restored = [];
		foreach ( $backup['options'] as $option_name => $data ) {
			if ( update_option( $option_name, $data['value'] ) ) {
				$restored[] = $option_name;
			}
		}

		return $restored;
	}

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

		$sql_pattern = Generate::like_pattern( $pattern, $type );

		$query = $wpdb->prepare( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s", $sql_pattern );

		$options = $wpdb->get_col( $query );

		return self::delete( $options );
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
	public static function delete( array $keys ): int {
		return array_reduce( $keys, function ( $count, $key ) {
			return $count + ( delete_option( $key ) ? 1 : 0 );
		}, 0 );
	}

}