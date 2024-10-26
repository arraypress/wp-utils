<?php
/**
 * Comparison Utilities
 *
 * This class provides utility functions for comparing various data types, such as booleans,
 * numbers, strings, dates, and arrays. It supports different comparison operators
 * and ensures flexible handling of values, including multi-value checks and fuzzy matching.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

/**
 * Check if the class `Compare` is defined, and if not, define it.
 */
if ( ! class_exists( 'Compare' ) ) :

	/**
	 * Comparison Utilities
	 *
	 * Provides utility functions for comparing different types of data, including
	 * boolean values, numeric values, strings, dates, and IP addresses. It supports
	 * multiple operators and handles comparisons with flexibility, offering string
	 * case sensitivity, multi-value checks, and fuzzy matching.
	 */
	class Compare {

		/**
		 * Default epsilon value for float comparisons.
		 */
		public const DEFAULT_EPSILON = 0.00001;

		/**
		 * Check if boolean passes a comparison.
		 *
		 * @param string $operator The comparison operator.
		 * @param mixed  $value    The value to compare against.
		 * @param mixed  $bool     The boolean to compare.
		 *
		 * @return bool
		 */
		public static function boolean( string $operator, $value, $bool ): bool {
			if ( ! $operator || is_null( $value ) || is_null( $bool ) ) {
				return false;
			}

			switch ( $operator ) {
				case '==':
				case '===':
				case 'is':
				case 'equal_to':
					return $bool === true;
				case '!=':
				case '!==':
				case 'is_not':
				case 'not_equal_to':
					return $bool === false;
				default:
					return false;
			}
		}

		/**
		 * Compare numeric values with optional epsilon for float precision.
		 *
		 * @param string $operator The comparison operator.
		 * @param mixed  $value1   The first value to compare.
		 * @param mixed  $value2   The second value to compare.
		 * @param float  $epsilon  Optional epsilon for float comparisons. Default is 0.00001.
		 *
		 * @return bool The result of the comparison.
		 */
		public static function numeric( string $operator, $value1, $value2, float $epsilon = self::DEFAULT_EPSILON ): bool {
			$value1 = (float) $value1;
			$value2 = (float) $value2;

			if ( empty( $operator ) ) {
				return false;
			}

			$diff = abs( $value1 - $value2 );

			switch ( $operator ) {
				case '==':
				case '===':
					return $diff < $epsilon;
				case '!=':
				case '!==':
					return $diff >= $epsilon;
				case '>':
					return ( $value1 > $value2 ) && ( $diff >= $epsilon );
				case '>=':
					return ( $value1 > $value2 ) || ( $diff < $epsilon );
				case '<':
					return ( $value1 < $value2 ) && ( $diff >= $epsilon );
				case '<=':
					return ( $value1 < $value2 ) || ( $diff < $epsilon );
				default:
					return false;
			}
		}

		/**
		 * Check if string passes a comparison.
		 *
		 * @param string  $operator       The comparison operator.
		 * @param string  $value          The value to compare against.
		 * @param ?string $string         The string to compare.
		 * @param bool    $case_sensitive Whether the comparison should be case-sensitive. Default is true.
		 *
		 * @return bool
		 * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
		 * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
		 * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
		 */
		public static function string( string $operator, string $value, ?string $string, bool $case_sensitive = true ): bool {
			if ( ! $operator || ! $value || is_null( $string ) ) {
				return false;
			}

			if ( ! $case_sensitive ) {
				$value  = strtolower( $value );
				$string = strtolower( $string );
			}

			switch ( $operator ) {
				case 'equal_to':
				case '==':
					return $string === $value;
				case 'not_equal_to':
				case '!=':
					return $string !== $value;
				case 'contains':
					return str_contains( $string, $value );
				case 'not_contains':
					return ! str_contains( $string, $value );
				case 'starts_with':
					return str_starts_with( $string, $value );
				case 'ends_with':
					return str_ends_with( $string, $value );
				default:
					return false;
			}
		}

		/**
		 * Check if date value passes a comparison.
		 *
		 * @param string $operator The comparison operator.
		 * @param mixed  $value    The value to compare against.
		 * @param mixed  $date     The date to compare.
		 *
		 * @return bool
		 */
		public static function dates( string $operator, $value, $date ): bool {
			$timestamp_value = strtotime( (string) $value );
			$timestamp_date  = strtotime( (string) $date );

			if ( $timestamp_value === false || $timestamp_date === false ) {
				return false;
			}

			return self::numeric( $operator, $timestamp_value, $timestamp_date );
		}

		/**
		 * Check if multi-value string passes a comparison.
		 *
		 * @param string $operator The comparison operator.
		 * @param ?array $values   The values to compare against.
		 * @param string $string   The string to compare.
		 *
		 * @return bool
		 * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
		 */
		public static function check_string_multi( string $operator, ?array $values, string $string ): bool {
			if ( ! $operator || is_null( $values ) || ! $string ) {
				return false;
			}

			switch ( $operator ) {
				case 'contains':
					foreach ( $values as $value ) {
						if ( str_contains( $string, $value ) ) {
							return true;
						}
					}

					return false;
				case 'contains_all':
					foreach ( $values as $value ) {
						if ( ! str_contains( $string, $value ) ) {
							return false;
						}
					}

					return true;
				case 'not_contains':
					foreach ( $values as $value ) {
						if ( str_contains( $string, $value ) ) {
							return false;
						}
					}

					return true;
				default:
					return false;
			}
		}

		/**
		 * Check if array passes a comparison.
		 *
		 * @param string $operator The comparison operator.
		 * @param mixed  $value    The value to compare against.
		 * @param ?array $array    The array to compare.
		 *
		 * @return bool
		 */
		public static function check_array( string $operator, $value, ?array $array ): bool {
			if ( ! $operator || is_null( $value ) || is_null( $array ) ) {
				return false;
			}

			switch ( $operator ) {
				case 'contains':
				case '==':
					return in_array( $value, $array, true );
				case 'not_contains':
				case '!=':
					return ! in_array( $value, $array, true );
				default:
					return false;
			}
		}

		/**
		 * Check if multi value array passes a comparison.
		 *
		 * @param string $operator The comparison operator.
		 * @param mixed  $value    The value to compare against.
		 * @param ?array $array    The array to compare.
		 *
		 * @return bool
		 */
		public static function array_multi( string $operator, $value, ?array $array ): bool {
			if ( ! $operator || is_null( $value ) || is_null( $array ) ) {
				return false;
			}

			if ( ! is_array( $value ) ) {
				$value = [ $value ];
			}

			switch ( $operator ) {
				case 'contains':
					return (bool) array_intersect( $value, $array );
				case 'not_contains':
					return ! array_intersect( $value, $array );
				case 'contains_all':
					return count( array_intersect( $value, $array ) ) === count( $value );
				default:
					return false;
			}
		}

		/**
		 * Compare IP addresses.
		 *
		 * @param string $operator The comparison operator.
		 * @param string $value    The IP address to compare against.
		 * @param string $ip       The IP address to compare.
		 *
		 * @return bool
		 */
		public static function ip_address( string $operator, string $value, string $ip ): bool {
			$value_long = ip2long( $value );
			$ip_long    = ip2long( $ip );

			if ( $value_long === false || $ip_long === false ) {
				return false;
			}

			return self::numeric( $operator, $value_long, $ip_long );
		}

		/**
		 * Compare version numbers.
		 *
		 * @param string $operator The comparison operator.
		 * @param string $value    The version number to compare against.
		 * @param string $version  The version number to compare.
		 *
		 * @return bool
		 */
		public static function version( string $operator, string $value, string $version ): bool {
			$comparison = version_compare( $version, $value );

			switch ( $operator ) {
				case '==':
				case '===':
					return $comparison === 0;
				case '!=':
				case '!==':
					return $comparison !== 0;
				case '>':
					return $comparison === 1;
				case '>=':
					return $comparison >= 0;
				case '<':
					return $comparison === - 1;
				case '<=':
					return $comparison <= 0;
				default:
					return false;
			}
		}

		/**
		 * Compare strings using Levenshtein distance for fuzzy matching.
		 *
		 * @param string $value        The value to compare against.
		 * @param string $string       The string to compare.
		 * @param int    $max_distance The maximum Levenshtein distance to consider a match.
		 *
		 * @return bool
		 */
		public static function fuzzy_string( string $value, string $string, int $max_distance = 3 ): bool {
			return levenshtein( $value, $string ) <= $max_distance;
		}

		/**
		 * Get comparison timestamps for a given value.
		 *
		 * @param string $value          The value in the format '7day', '8minutes', etc.
		 * @param string $reference_date The reference date to compare against.
		 *
		 * @return array An array with 'reference_timestamp' and 'compare_timestamp'.
		 */
		public static function get_comparison_timestamps( string $value, string $reference_date ): array {
			$unit_value = Split::unit_value( $value );
			$number     = $unit_value['number'];
			$period     = $unit_value['period'];

			// Check if the number is valid
			if ( $number === 0 ) {
				return [
					'reference_timestamp' => 0,
					'compare_timestamp'   => 0,
				];
			}

			// Build the strtotime-compatible string
			$time_string = "+$number $period";

			// Get the current time in the WordPress timezone
			$current_time = current_time( 'timestamp' );

			// Get the timestamp of the reference date
			$reference_timestamp = strtotime( $reference_date );

			// Get the timestamp of the comparison date
			$compare_timestamp = strtotime( $time_string, $current_time );

			return [
				'reference_timestamp' => $reference_timestamp,
				'compare_timestamp'   => $compare_timestamp,
			];
		}

	}
endif;