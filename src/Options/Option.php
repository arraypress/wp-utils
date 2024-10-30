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

use ArrayPress\Utils\Common\Convert;

/**
 * Check if the class `Option` is defined, and if not, define it.
 */
if ( ! class_exists( 'Option' ) ) :

	/**
	 * Options Utilities
	 *
	 * Provides utility functions for managing WordPress options, including
	 * methods for getting options with defaults, incrementing/decrementing values,
	 * and handling array-based options.
	 */
	class Option {

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
			$value = get_option( $option, $default );

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
			$value = get_option( $option, null );

			if ( $value === null && $default !== null ) {
				return Convert::value( $default, $cast_type );
			}

			return Convert::value( $value, $cast_type );
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
			$current_value = (int) get_option( $option, 0 );
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
			$current_value = (int) get_option( $option, 0 );
			$new_value     = $current_value - abs( $amount );

			return update_option( $option, $new_value ) ? $new_value : false;
		}

		/**
		 * Check if a value exists in an option array.
		 *
		 * @param string $option Name of the option.
		 * @param mixed  $value  Value to check for in the array.
		 *
		 * @return bool True if the value exists, false otherwise.
		 */
		public static function has_value( string $option, $value ): bool {
			$current_array = get_option( $option );

			return is_array( $current_array ) && in_array( $value, $current_array, true );
		}

		/**
		 * Add a value to an option array.
		 *
		 * @param string $option Name of the option.
		 * @param mixed  $value  Value to add to the array.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function add_to_array( string $option, $value ): bool {
			$current_array = get_option( $option, [] );
			if ( ! is_array( $current_array ) ) {
				$current_array = [];
			}
			$current_array[] = $value;

			return update_option( $option, $current_array );
		}

		/**
		 * Remove a value from an option array.
		 *
		 * @param string $option Name of the option.
		 * @param mixed  $value  Value to remove from the array.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function remove_from_array( string $option, $value ): bool {
			$current_array = get_option( $option );
			if ( ! is_array( $current_array ) ) {
				return false;
			}
			$current_array = array_diff( $current_array, [ $value ] );

			return update_option( $option, $current_array );
		}

		/**
		 * Remove a specific value from an option that contains an array.
		 *
		 * @param string $option Name of the option containing the array.
		 * @param mixed  $value  The value to remove from the array.
		 *
		 * @return bool True if the value was found and removed, false otherwise.
		 */
		public static function remove_value_from_array( string $option, $value ): bool {
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
			$current_value = get_option( $option );
			if ( $current_value !== $value ) {
				return update_option( $option, $value );
			}

			return false;
		}

		/**
		 * Get the count of items in an option array.
		 *
		 * @param string $option Name of the option.
		 *
		 * @return int The count of items in the array.
		 */
		public static function get_array_count( string $option ): int {
			$current_array = get_option( $option );

			return is_array( $current_array ) ? count( $current_array ) : 0;
		}

	}
endif;
