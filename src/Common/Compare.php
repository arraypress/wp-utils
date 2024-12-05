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

class Compare {

	/**
	 * Default epsilon value for float comparisons.
	 */
	public const DEFAULT_EPSILON = 0.00001;

	/**
	 * Safely decode operator string.
	 */
	private static function decode_operator( string $operator ): string {
		return html_entity_decode( $operator, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	}

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
		$operator = self::decode_operator( $operator );

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
		$operator = self::decode_operator( $operator );

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
	 * @param bool    $strip_spaces   Whether to strip all whitespace. Default is false.
	 *
	 * @return bool
	 */
	public static function string( string $operator, string $value, ?string $string, bool $case_sensitive = true, bool $strip_spaces = false ): bool {
		$operator = self::decode_operator( $operator );

		if ( ! $operator || ! $value || is_null( $string ) ) {
			return false;
		}

		if ( $strip_spaces ) {
			$value  = Str::remove_whitespace( $value );
			$string = Str::remove_whitespace( $string );
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
		$operator = self::decode_operator( $operator );

		$timestamp_value = strtotime( (string) $value );
		$timestamp_date  = strtotime( (string) $date );

		if ( $timestamp_value === false || $timestamp_date === false ) {
			return false;
		}

		return self::numeric( $operator, $timestamp_value, $timestamp_date );
	}

	/**
	 * Check if time value passes a comparison.
	 *
	 * @param string $operator The comparison operator.
	 * @param mixed  $value    The time to compare against (H:i format).
	 * @param mixed  $time     The time to compare (H:i format).
	 *
	 * @return bool
	 */
	public static function times( string $operator, $value, $time ): bool {
		$operator = self::decode_operator( $operator );

		// Validate time formats
		if ( ! Validate::is_time( (string) $value ) || ! Validate::is_time( (string) $time ) ) {
			return false;
		}

		// Convert times to minutes since midnight for comparison
		list( $hours_value, $mins_value ) = explode( ':', $value );
		list( $hours_time, $mins_time ) = explode( ':', $time );

		$value_minutes = ( (int) $hours_value * 60 ) + (int) $mins_value;
		$time_minutes  = ( (int) $hours_time * 60 ) + (int) $mins_time;

		return self::numeric( $operator, $value_minutes, $time_minutes );
	}

	/**
	 * Check if multi-value string passes a comparison.
	 *
	 * @param string $operator       The comparison operator.
	 * @param ?array $values         The values to compare against.
	 * @param string $string         The string to compare.
	 * @param bool   $case_sensitive Whether the comparison should be case-sensitive. Default is true.
	 * @param bool   $strip_spaces   Whether to remove all whitespace. Default is false.
	 *
	 * @return bool
	 */
	public static function string_multi(
		string $operator,
		?array $values,
		string $string,
		bool $case_sensitive = true,
		bool $strip_spaces = false
	): bool {
		$operator = self::decode_operator( $operator );

		if ( ! $operator || is_null( $values ) || ! $string ) {
			return false;
		}

		// Format string
		if ( $strip_spaces ) {
			$string = Str::remove_whitespace( $string );
		}
		if ( ! $case_sensitive ) {
			$string = strtolower( $string );
		}

		// Format values
		if ( $strip_spaces ) {
			$values = Arr::remove_whitespace( $values );
		}
		if ( ! $case_sensitive ) {
			$values = Arr::lowercase( $values );
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
	 * @param string $operator       The comparison operator.
	 * @param mixed  $value          The value to compare against.
	 * @param ?array $array          The array to compare.
	 * @param bool   $case_sensitive Whether the comparison should be case-sensitive. Default is true.
	 * @param bool   $strip_spaces   Whether to remove all whitespace. Default is false.
	 *
	 * @return bool
	 */
	public static function array(
		string $operator,
		$value,
		?array $array,
		bool $case_sensitive = true,
		bool $strip_spaces = false
	): bool {
		$operator = self::decode_operator( $operator );

		if ( ! $operator || is_null( $value ) || is_null( $array ) ) {
			return false;
		}

		// Format value if it's a string
		if ( is_string( $value ) ) {
			if ( $strip_spaces ) {
				$value = Str::remove_whitespace( $value );
			}
			if ( ! $case_sensitive ) {
				$value = strtolower( $value );
			}
		}

		// Format array and ensure uniqueness
		$array = Arr::ensure_unique( $array );
		if ( $strip_spaces ) {
			$array = Arr::remove_whitespace( $array );
		}
		if ( ! $case_sensitive ) {
			$array = Arr::lowercase( $array );
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
	 * @param string $operator       The comparison operator.
	 * @param mixed  $value          The value to compare against.
	 * @param ?array $array          The array to compare.
	 * @param bool   $case_sensitive Whether the comparison should be case-sensitive. Default is true.
	 * @param bool   $strip_spaces   Whether to remove all whitespace. Default is false.
	 *
	 * @return bool
	 */
	public static function array_multi(
		string $operator,
		$value,
		?array $array,
		bool $case_sensitive = true,
		bool $strip_spaces = false
	): bool {
		$operator = self::decode_operator( $operator );

		if ( ! $operator || is_null( $value ) || is_null( $array ) ) {
			return false;
		}

		if ( ! is_array( $value ) ) {
			$value = [ $value ];
		}

		// Format arrays
		if ( $strip_spaces ) {
			$value = Arr::remove_whitespace( $value );
			$array = Arr::remove_whitespace( $array );
		}
		if ( ! $case_sensitive ) {
			$value = Arr::lowercase( $value );
			$array = Arr::lowercase( $array );
		}

		// Format array and ensure uniqueness
		$value = Arr::ensure_unique( $value );
		$array = Arr::ensure_unique( $array );

		switch ( $operator ) {
			case 'contains':
			case '==':
				return (bool) array_intersect( $value, $array );
			case 'not_contains':
			case '!=':
				return ! array_intersect( $value, $array );
			case 'contains_all':
				return count( array_intersect( $value, $array ) ) === count( $value );
			default:
				return false;
		}
	}

	/**
	 * Compare IP addresses with support for both exact matches and CIDR/subnet matches.
	 *
	 * @param string $operator The comparison operator ('==', '!=')
	 * @param string $value    The IP address or CIDR range to compare against
	 * @param string $ip       The IP address to check
	 *
	 * @return bool Whether the IP matches according to the operator
	 */
	public static function ip_address( string $operator, string $value, string $ip ): bool {
		// Validate IP to check
		if ( ! IP::is_valid( $ip ) ) {
			return false;
		}

		// Check if it's either an exact match or matches a CIDR range
		$is_match = $value === $ip || ( IP::is_valid_range( $value ) && IP::is_in_range( $ip, $value ) );

		switch ( $operator ) {
			case '==':
			case '===':
				return $is_match;
			case '!=':
			case '!==':
				return ! $is_match;
			default:
				return false;
		}
	}

	/**
	 * Compare IP address against multiple IPs/ranges.
	 * Supports both exact IP matches and CIDR ranges in the array.
	 *
	 * @param string   $operator The comparison operator ('contains', 'contains_all', 'not_contains')
	 * @param string[] $values   Array of IPs/ranges to compare against
	 * @param string   $ip       The IP address to check
	 *
	 * @return bool Whether the IP matches according to the operator
	 */
	public static function ip_address_multi( string $operator, array $values, string $ip ): bool {
		// Validate input IP
		if ( ! IP::is_valid( $ip ) ) {
			return false;
		}

		// Ensure array values are unique and non-empty
		$values = array_filter( array_unique( $values ) );
		if ( empty( $values ) ) {
			return false;
		}

		switch ( $operator ) {
			case 'contains':
				foreach ( $values as $value ) {
					if ( self::ip_address( '==', $value, $ip ) ) {
						return true;
					}
				}

				return false;

			case 'contains_all':
				foreach ( $values as $value ) {
					if ( ! self::ip_address( '==', $value, $ip ) ) {
						return false;
					}
				}

				return true;

			case 'not_contains':
				foreach ( $values as $value ) {
					if ( self::ip_address( '==', $value, $ip ) ) {
						return false;
					}
				}

				return true;

			default:
				return false;
		}
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

}