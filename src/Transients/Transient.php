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

use ArrayPress\Utils\Common\Convert;

/**
 * Check if the class `Transient` is defined, and if not, define it.
 */
if ( ! class_exists( 'Transient' ) ) :

	/**
	 * Transients Utilities
	 *
	 * Provides utility functions for managing WordPress transients, including
	 * methods for getting transients with defaults, incrementing/decrementing values,
	 * and handling transients based on patterns.
	 */
	class Transient {

		/**
		 * Check if a transient exists.
		 *
		 * @param string $transient Transient name.
		 *
		 * @return bool True if the transient exists, false otherwise.
		 */
		public static function exists( string $transient ): bool {
			return get_transient( $transient ) !== false;
		}

		/**
		 * Retrieves a specific option for the current site.
		 *
		 * @param string $transient Name of the transient to retrieve.
		 *
		 * @return mixed Value of the option or the default value.
		 */
		public static function get( string $transient ) {
			return get_transient( $transient );
		}

		/**
		 * Get transient with a default if not set.
		 *
		 * @param string $transient Name of the transient to retrieve.
		 * @param mixed  $default   Default value to return if the transient does not exist.
		 *
		 * @return mixed The transient value or default.
		 */
		public static function get_with_default( string $transient, $default ) {
			$value = get_transient( $transient );

			return $value !== false ? $value : $default;
		}

		/**
		 * Get transient value with type casting.
		 *
		 * @param string $transient Transient name.
		 * @param string $cast_type The type to cast to ('int', 'float', 'bool', 'array', 'string').
		 * @param mixed  $default   Default value to return if transient doesn't exist.
		 *
		 * @return mixed The transient value cast to the specified type, or default.
		 */
		public static function get_cast( string $transient, string $cast_type, $default = null ) {
			$value = get_transient( $transient );

			if ( $value === false && $default !== null ) {
				return Convert::value( $default, $cast_type );
			}

			return Convert::value( $value, $cast_type );
		}

		/**
		 * Set a transient with error handling.
		 *
		 * @param string $transient  Transient name.
		 * @param mixed  $value      Transient value.
		 * @param int    $expiration Time until expiration in seconds.
		 *
		 * @return bool True if successful, false otherwise.
		 */
		public static function set( string $transient, $value, int $expiration = 0 ): bool {
			return set_transient( $transient, $value, $expiration );
		}

		/**
		 * Delete a transient.
		 *
		 * @param string $transient Transient name.
		 *
		 * @return bool True if successful, false otherwise.
		 */
		public static function delete( string $transient ): bool {
			return delete_transient( $transient );
		}

		/**
		 * Increment or decrement a numeric transient value.
		 *
		 * @param string $transient  Name of the transient to update.
		 * @param int    $amount     Amount to increment (positive) or decrement (negative).
		 * @param int    $expiration Time until expiration in seconds.
		 *
		 * @return int|bool The new transient value on success, false on failure.
		 */
		public static function increment_value( string $transient, int $amount = 1, int $expiration = 0 ) {
			$current_value = (int) self::get_with_default( $transient, 0 );
			$new_value     = $current_value + $amount;

			return self::set( $transient, $new_value, $expiration ) ? $new_value : false;
		}

		/**
		 * Decrement a numeric transient value.
		 *
		 * @param string $transient  Name of the transient to update.
		 * @param int    $amount     Amount to decrement (positive number).
		 * @param int    $expiration Time until expiration in seconds.
		 *
		 * @return int|bool The new transient value on success, false on failure.
		 */
		public static function decrement_value( string $transient, int $amount = 1, int $expiration = 0 ) {
			$current_value = (int) self::get_with_default( $transient, 0 );
			$new_value     = $current_value - abs( $amount ); // Ensure we always subtract the absolute value

			return self::set( $transient, $new_value, $expiration ) ? $new_value : false;
		}

		/**
		 * Add a value to a transient array.
		 *
		 * @param string $transient  Transient name.
		 * @param mixed  $value      Value to add to the array.
		 * @param int    $expiration Time until expiration in seconds.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function add_to_array( string $transient, $value, int $expiration = 0 ): bool {
			$current_array = self::get_with_default( $transient, [] );
			if ( ! is_array( $current_array ) ) {
				$current_array = [];
			}
			$current_array[] = $value;

			return self::set( $transient, $current_array, $expiration );
		}

		/**
		 * Remove a value from a transient array.
		 *
		 * @param string $transient  Transient name.
		 * @param mixed  $value      Value to remove from the array.
		 * @param int    $expiration Time until expiration in seconds.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function remove_from_array( string $transient, $value, int $expiration = 0 ): bool {
			$current_array = self::get_with_default( $transient, [] );
			if ( ! is_array( $current_array ) ) {
				return false;
			}
			$current_array = array_diff( $current_array, [ $value ] );

			return self::set( $transient, array_values( $current_array ), $expiration );
		}

		/**
		 * Remove a specific value from a transient that contains an array.
		 *
		 * @param string $transient  Transient name.
		 * @param mixed  $value      The value to remove from the array.
		 * @param int    $expiration Time until expiration in seconds.
		 *
		 * @return bool True if the value was found and removed, false otherwise.
		 */
		public static function remove_value_from_array( string $transient, $value, int $expiration = 0 ): bool {
			$current_array = self::get( $transient );

			if ( ! is_array( $current_array ) ) {
				return false;
			}

			$key = array_search( $value, $current_array, true );

			if ( $key === false ) {
				return false;
			}

			unset( $current_array[ $key ] );

			return self::set( $transient, array_values( $current_array ), $expiration );
		}

		/**
		 * Get the remaining time of a transient in seconds.
		 *
		 * @param string $transient Transient name.
		 *
		 * @return int|false Remaining time in seconds, or false if the transient doesn't exist.
		 */
		public static function get_expiration( string $transient ) {
			$option_timeout = '_transient_timeout_' . $transient;
			$timeout        = get_option( $option_timeout );
			if ( $timeout === false ) {
				return false;
			}
			$now = time();

			return $timeout > $now ? $timeout - $now : 0;
		}

		/**
		 * Extend the expiration of an existing transient.
		 *
		 * @param string $transient Transient name.
		 * @param int    $extend_by Time to extend by in seconds.
		 *
		 * @return bool True if successful, false otherwise.
		 */
		public static function extend_expiration( string $transient, int $extend_by ): bool {
			$value = get_transient( $transient );
			if ( $value === false ) {
				return false;
			}
			$current_expiration = self::get_expiration( $transient );
			if ( $current_expiration === false ) {
				return false;
			}
			$new_expiration = $current_expiration + $extend_by;

			return set_transient( $transient, $value, $new_expiration );
		}

	}
endif;