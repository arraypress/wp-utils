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

use DateTime;

/**
 * Check if the class `Convert` is defined, and if not, define it.
 */
if ( ! class_exists( 'Convert' ) ) :

	/**
	 * Data Conversion Utilities
	 *
	 * Provides utility functions for converting various data types, handling
	 * string manipulations, numeric conversions, and value transformations.
	 */
	class Convert {

		/**
		 * Convert meta value to a specific type or apply a custom callback.
		 *
		 * @param mixed $value   The meta value to convert.
		 * @param mixed $type    The type to convert to ('int', 'float', 'bool', 'array', 'string'), or a callback function name.
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

		/**
		 * Convert any value to a 'yes' or 'no' string.
		 *
		 * @param mixed $value      The value to convert.
		 * @param bool  $title_case Whether to return the result in title case.
		 *
		 * @return string 'yes'/'Yes' for truthy values, 'no'/'No' for falsy values.
		 */
		public static function to_yes_no( $value, bool $title_case = false ): string {
			$result = self::to_bool( $value ) ? 'yes' : 'no';

			return $title_case ? ucfirst( $result ) : $result;
		}

		/**
		 * Convert any value to an 'on' or 'off' string.
		 *
		 * @param mixed $value      The value to convert.
		 * @param bool  $title_case Whether to return the result in title case.
		 *
		 * @return string 'on'/'On' for truthy values, 'off'/'Off' for falsy values.
		 */
		public static function to_on_off( $value, bool $title_case = false ): string {
			$result = self::to_bool( $value ) ? 'on' : 'off';

			return $title_case ? ucfirst( $result ) : $result;
		}

		/**
		 * Convert any value to a '1' or '0' string.
		 *
		 * @param mixed $value The value to convert.
		 *
		 * @return string '1' for truthy values, '0' for falsy values.
		 */
		public static function to_binary( $value ): string {
			return self::to_bool( $value ) ? '1' : '0';
		}

		/**
		 * Convert any value to a 'true' or 'false' string.
		 *
		 * @param mixed $value      The value to convert.
		 * @param bool  $title_case Whether to return the result in title case.
		 *
		 * @return string 'true'/'True' for truthy values, 'false'/'False' for falsy values.
		 */
		public static function to_string_boolean( $value, bool $title_case = false ): string {
			$result = self::to_bool( $value ) ? 'true' : 'false';

			return $title_case ? ucfirst( $result ) : $result;
		}

		/**
		 * Convert a value to a specific numeric type with optional constraints.
		 *
		 * @param mixed      $value The value to convert.
		 * @param string     $type  The numeric type ('int' or 'float').
		 * @param float|null $min   Optional minimum value.
		 * @param float|null $max   Optional maximum value.
		 *
		 * @return int|float The converted and constrained value.
		 */
		public static function to_numeric( $value, string $type = 'float', ?float $min = null, ?float $max = null ) {
			$type = strtolower( $type );
			$result = $type === 'int' ? self::to_int( $value ) : self::to_float( $value );

			if ( $min !== null ) {
				$result = max( $result, $min );
			}
			if ( $max !== null ) {
				$result = min( $result, $max );
			}

			return $type === 'int' ? (int) $result : (float) $result;
		}

		/**
		 * Convert a value to a specific date/time format.
		 *
		 * @param mixed  $value  The value to convert (timestamp, date string, or DateTime object).
		 * @param string $format The desired date format (default: 'Y-m-d H:i:s').
		 *
		 * @return string|false The formatted date string or false on failure.
		 */
		public static function to_date( $value, string $format = 'Y-m-d H:i:s' ) {
			if ( $value instanceof DateTime ) {
				return $value->format( $format );
			}

			if ( is_numeric( $value ) ) {
				return date( $format, $value );
			}

			$timestamp = strtotime( $value );

			return $timestamp !== false ? date( $format, $timestamp ) : false;
		}

		/**
		 * Convert a human-readable comparison operator to a symbol
		 *
		 * @param string $operator The human-readable comparison operator.
		 *
		 * @return string|null The symbol comparison operator, or null if the operator is not recognized.
		 */
		public static function operator_to_symbol( string $operator ): ?string {
			switch ( strtolower( $operator ) ) {
				case 'more_than':
					return '>';
				case 'less_than':
					return '<';
				case 'at_least':
					return '>=';
				case 'at_most':
					return '<=';
				case 'equal_to':
					return '==';
				case 'not_equal_to':
					return '!=';
				default:
					return null;
			}
		}

	}

	// Add class alias
	class_alias( Convert::class, 'ArrayPress\Utils\Common\Cast' );

endif;