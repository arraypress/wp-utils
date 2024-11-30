<?php
/**
 * Data Conversion Utilities
 *
 * This class provides utility functions for common data conversions, such as converting
 * arrays, handling name components, monetary values, units of time, and general value manipulation.
 * It offers flexible type conversions, including string manipulation, boolean handling, and
 * numeric conversions, ensuring robust handling of various input types.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

class Cast {

	/**
	 * Convert meta value to a specific type or apply a custom callback.
	 *
	 * @param mixed $value   The meta value to convert.
	 * @param mixed $type    The type to convert to ('int', 'float', 'bool', 'array', 'string'), or a callback function
	 *                       name.
	 * @param mixed $default Optional. Default value to return if conversion fails.
	 *
	 * @return mixed The converted value.
	 */
	public static function value( $value, $type, $default = null ) {
		if ( is_callable( $type ) ) {
			return $type( $value );
		} elseif ( function_exists( $type ) ) {
			return $type( $value );
		}

		// Proceed with type conversion if not a callback
		switch ( strtolower( $type ) ) {
			case 'int':
				return self::to_int( $value );
			case 'float':
				return self::to_float( $value );
			case 'bool':
				return self::to_bool( $value );
			case 'array':
				return self::to_array( $value );
			case 'string':
				return self::to_string( $value );
			default:
				return $default !== null ? $default : $value;
		}
	}

	/**
	 * Convert any value to a boolean representation.
	 *
	 * @param mixed $value The value to convert.
	 *
	 * @return bool The boolean representation.
	 */
	public static function to_bool( $value ): bool {
		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Convert any value to an integer, with an option for absolute value.
	 *
	 * @param mixed $value    The value to convert.
	 * @param bool  $absolute Whether to return the absolute value. Default is false.
	 *
	 * @return int The integer representation.
	 */
	public static function to_int( $value, bool $absolute = false ): int {
		$result = (int) $value;

		return $absolute ? abs( $result ) : $result;
	}

	/**
	 * Convert any value to a float, with an option for absolute value.
	 *
	 * @param mixed $value    The value to convert.
	 * @param bool  $absolute Whether to return the absolute value. Default is false.
	 *
	 * @return float The float representation.
	 */
	public static function to_float( $value, bool $absolute = false ): float {
		$result = (float) $value;

		return $absolute ? abs( $result ) : $result;
	}

	/**
	 * Convert any value to a number (int or float), with an option for absolute value.
	 *
	 * @param mixed $value    The value to convert.
	 * @param bool  $absolute Whether to return the absolute value. Default is false.
	 *
	 * @return int|float The numeric representation.
	 */
	public static function to_number( $value, bool $absolute = false ) {
		$result = is_numeric( $value ) ? $value + 0 : 0; // Adding 0 to convert numeric strings to int or float

		return $absolute ? abs( $result ) : $result;
	}

	/**
	 * Convert any value to an array.
	 *
	 * @param mixed $value The value to convert.
	 *
	 * @return array The array representation.
	 */
	public static function to_array( $value ): array {
		if ( is_array( $value ) ) {
			return $value;
		}

		if ( is_object( $value ) ) {
			return (array) $value;
		}

		if ( is_string( $value ) ) {
			$unserialized = maybe_unserialize( $value );
			if ( is_array( $unserialized ) ) {
				return $unserialized;
			}

			$json = json_decode( $value, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				return $json;
			}

			if ( strpos( $value, ',' ) !== false ) {
				return array_map( 'trim', explode( ',', $value ) );
			}
		}

		// For all other cases, wrap the value in an array
		return [ $value ];
	}

	/**
	 * Convert value to string.
	 *
	 * @param mixed $value The value to convert.
	 *
	 * @return string
	 */
	private static function to_string( $value ): string {
		if ( is_array( $value ) || is_object( $value ) ) {
			return json_encode( $value );
		}

		return (string) $value;
	}

}