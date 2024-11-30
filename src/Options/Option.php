<?php
/**
 * Option Utilities for WordPress
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

use ArrayPress\Utils\Common\Cast;

/**
 * Class Option
 *
 * Utility functions for working with a specific option.
 */
class Option {

	/**
	 * Check if an option exists.
	 *
	 * @param string $option Name of the option to check.
	 *
	 * @return bool True if the option exists, false otherwise.
	 */
	public static function exists( string $option ): bool {
		return get_option( $option, null ) !== null;
	}

	/**
	 * Retrieves a specific option for the current site.
	 *
	 * @param string $option  Name of the option to retrieve.
	 * @param mixed  $default Optional. Default value to return if the option does not exist.
	 *
	 * @return mixed Value of the option or the default value.
	 */
	public static function get( string $option, $default = false ) {
		return get_option( $option, $default );
	}

	/**
	 * Get option with a default if not set.
	 *
	 * @param string $option  Name of the option to retrieve.
	 * @param mixed  $default Default value to return if the option does not exist.
	 *
	 * @return mixed The option value or default.
	 */
	public static function get_with_default( string $option, $default ) {
		$value = self::get( $option, $default );

		return $value !== false ? $value : $default;
	}

	/**
	 * Get option value with type casting.
	 *
	 * @param string $option    Name of the option to retrieve.
	 * @param string $cast_type The type to cast to ('int', 'float', 'bool', 'array', 'string').
	 * @param mixed  $default   Default value to return if option doesn't exist.
	 *
	 * @return mixed The option value cast to the specified type, or default.
	 */
	public static function get_cast( string $option, string $cast_type, $default = null ) {
		$value = self::get( $option, null );

		if ( $value === null && $default !== null ) {
			return Cast::value( $default, $cast_type );
		}

		return Cast::value( $value, $cast_type );
	}

	/**
	 * Increment or decrement a numeric option value.
	 *
	 * @param string $option Name of the option to update.
	 * @param int    $amount Amount to increment (positive) or decrement (negative).
	 *
	 * @return int|bool The new option value on success, false on failure.
	 */
	public static function increment_value( string $option, int $amount = 1 ) {
		$current_value = (int) self::get( $option, 0 );
		$new_value     = $current_value + $amount;

		return update_option( $option, $new_value ) ? $new_value : false;
	}

	/**
	 * Decrement a numeric option value.
	 *
	 * @param string $option Name of the option to update.
	 * @param int    $amount Amount to decrement (positive number).
	 *
	 * @return int|bool The new option value on success, false on failure.
	 */
	public static function decrement_value( string $option, int $amount = 1 ) {
		$current_value = (int) self::get( $option, 0 );
		$new_value     = $current_value - abs( $amount );

		return update_option( $option, $new_value ) ? $new_value : false;
	}

	/**
	 * Check if an array option contains a specific value.
	 *
	 * @param string $option Name of the option.
	 * @param mixed  $value  Value to check for in the array.
	 *
	 * @return bool True if the value exists, false otherwise.
	 */
	public static function array_contains( string $option, $value ): bool {
		$current_array = self::get( $option );

		return is_array( $current_array ) && in_array( $value, $current_array, true );
	}

	/**
	 * Append a value to an array option.
	 *
	 * @param string $option Name of the option.
	 * @param mixed  $value  Value to append.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function array_append( string $option, $value ): bool {
		$current_array = self::get( $option, [] );
		if ( ! is_array( $current_array ) ) {
			$current_array = [];
		}
		$current_array[] = $value;

		return update_option( $option, $current_array );
	}

	/**
	 * Remove all occurrences of a value from an array option.
	 *
	 * @param string $option Name of the option.
	 * @param mixed  $value  Value to remove.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function array_remove_all( string $option, $value ): bool {
		$current_array = self::get( $option );
		if ( ! is_array( $current_array ) ) {
			return false;
		}
		$current_array = array_diff( $current_array, [ $value ] );

		return update_option( $option, $current_array );
	}

	/**
	 * Remove the first occurrence of a value from an array option.
	 *
	 * @param string $option Name of the option containing the array.
	 * @param mixed  $value  The value to remove.
	 *
	 * @return bool True if the value was found and removed, false otherwise.
	 */
	public static function array_remove_first( string $option, $value ): bool {
		$array = self::get( $option, [] );
		if ( ! is_array( $array ) ) {
			return false;
		}
		$key = array_search( $value, $array, true );
		if ( $key === false ) {
			return false;
		}
		unset( $array[ $key ] );

		return self::update( $option, array_values( $array ) );
	}

	/**
	 * Get the count of items in an option array.
	 *
	 * @param string $option Name of the option.
	 *
	 * @return int The count of items in the array.
	 */
	public static function get_array_count( string $option ): int {
		$current_array = self::get( $option );

		return is_array( $current_array ) ? count( $current_array ) : 0;
	}

	/**
	 * Updates a specific option for the current site.
	 *
	 * @param string $option Name of the option to update.
	 * @param mixed  $value  The new value for the option.
	 *
	 * @return bool True if the option was successfully updated, false otherwise.
	 */
	public static function update( string $option, $value ): bool {
		return update_option( $option, $value );
	}

	/**
	 * Update an option only if it's different from the current value.
	 *
	 * @param string $option Name of the option to update.
	 * @param mixed  $value  The new value for the option.
	 *
	 * @return bool True if the value was changed, false otherwise.
	 */
	public static function update_if_changed( string $option, $value ): bool {
		$current_value = self::get( $option );
		if ( $current_value !== $value ) {
			return update_option( $option, $value );
		}

		return false;
	}

	/**
	 * Get the type of an option's value.
	 *
	 * @param string $option Name of the option.
	 *
	 * @return string|null The type of the option value or null if option doesn't exist.
	 */
	public static function get_type( string $option ): ?string {
		$value = self::get( $option, null );

		return $value !== null ? gettype( $value ) : null;
	}

	/**
	 * Get the size of an option in bytes.
	 *
	 * @param string $option Name of the option.
	 *
	 * @return int Size in bytes, 0 if option doesn't exist.
	 */
	public static function get_size( string $option ): int {
		$value = self::get( $option, null );

		return $value !== null ? strlen( maybe_serialize( $value ) ) : 0;
	}

	/**
	 * Merge a value into an existing array option.
	 *
	 * @param string $option    Name of the option.
	 * @param mixed  $value     Value to merge.
	 * @param bool   $recursive Whether to merge recursively. Default true.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function merge_array( string $option, $value, bool $recursive = true ): bool {
		$current = self::get( $option, [] );
		if ( ! is_array( $current ) ) {
			return false;
		}

		$new_value = $recursive
			? array_merge_recursive( $current, (array) $value )
			: array_merge( $current, (array) $value );

		return self::update( $option, $new_value );
	}

	/**
	 * Get a nested value from an array option using "dot" notation.
	 *
	 * @param string $option  Name of the option.
	 * @param string $key     Key using dot notation (e.g., 'parent.child').
	 * @param mixed  $default Default value if key doesn't exist.
	 *
	 * @return mixed Value at the specified key or default.
	 */
	public static function get_nested( string $option, string $key, $default = null ) {
		$array = self::get( $option, [] );
		if ( ! is_array( $array ) ) {
			return $default;
		}

		$keys = explode( '.', $key );
		foreach ( $keys as $segment ) {
			if ( ! is_array( $array ) || ! array_key_exists( $segment, $array ) ) {
				return $default;
			}
			$array = $array[ $segment ];
		}

		return $array;
	}

	/**
	 * Set a nested value in an array option using "dot" notation.
	 *
	 * @param string $option Name of the option.
	 * @param string $key    Key using dot notation (e.g., 'parent.child').
	 * @param mixed  $value  Value to set.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function set_nested( string $option, string $key, $value ): bool {
		$array = self::get( $option, [] );
		if ( ! is_array( $array ) ) {
			$array = [];
		}

		$keys    = explode( '.', $key );
		$current = &$array;
		foreach ( $keys as $segment ) {
			if ( ! is_array( $current ) ) {
				$current = [];
			}
			$current = &$current[ $segment ];
		}
		$current = $value;

		return self::update( $option, $array );
	}

	/**
	 * Remove a nested key from an array option using "dot" notation.
	 *
	 * @param string $option Name of the option.
	 * @param string $key    Key using dot notation (e.g., 'parent.child').
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function remove_nested( string $option, string $key ): bool {
		$array = self::get( $option, [] );
		if ( ! is_array( $array ) ) {
			return false;
		}

		$keys     = explode( '.', $key );
		$last_key = array_pop( $keys );
		$current  = &$array;

		foreach ( $keys as $segment ) {
			if ( ! is_array( $current ) || ! array_key_exists( $segment, $current ) ) {
				return false;
			}
			$current = &$current[ $segment ];
		}

		if ( is_array( $current ) && array_key_exists( $last_key, $current ) ) {
			unset( $current[ $last_key ] );

			return self::update( $option, $array );
		}

		return false;
	}

	/**
	 * Create a backup of an option with metadata.
	 *
	 * @param string $option Name of the option.
	 *
	 * @return array|null Backup data array or null if option doesn't exist.
	 */
	public static function backup( string $option ): ?array {
		$value = self::get( $option, null );
		if ( $value === null ) {
			return null;
		}

		return [
			'timestamp' => current_time( 'timestamp' ),
			'name'      => $option,
			'value'     => $value,
			'type'      => gettype( $value ),
			'size'      => strlen( maybe_serialize( $value ) )
		];
	}

	/**
	 * Restore an option from a backup array.
	 *
	 * @param array $backup Backup array created by backup() method.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function restore( array $backup ): bool {
		if ( empty( $backup['name'] ) || ! isset( $backup['value'] ) ) {
			return false;
		}

		return self::update( $backup['name'], $backup['value'] );
	}

	/**
	 * Toggle a boolean option value.
	 *
	 * @param string $option Name of the option.
	 *
	 * @return bool|null New value on success, null on failure.
	 */
	public static function toggle( string $option ): ?bool {
		$value = self::get_cast( $option, 'bool', false );

		return self::update( $option, ! $value ) ? ! $value : null;
	}

}
