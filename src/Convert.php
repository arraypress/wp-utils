<?php
/**
 * Convert Helper Utilities
 *
 * This class provides utility functions primarily for converting and processing strings,
 * including handling names (prefixes, suffixes, and splitting full names into components),
 * transforming version numbers, converting unit values from strings, and mapping human-readable
 * comparison operators to their symbolic counterparts. The class also includes methods to
 * modify and add custom prefixes and suffixes, as well as general utility functions for
 * converting arrays and formatting values.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

/**
 * Check if the class `Convert` is defined, and if not, define it.
 */
if ( ! class_exists( 'Convert' ) ) :

	/**
	 * Convert Helper Utilities
	 *
	 * Provides a collection of utility functions for converting and handling various types
	 * of data, primarily focused on string operations. This includes methods for parsing
	 * and transforming version numbers, splitting full names into their components, handling
	 * unit values from strings, converting human-readable comparison operators into symbols,
	 * and more. Additionally, the class offers flexible operations for working with arrays,
	 * such as transforming value-label pairs into key-value arrays, and prettifying version
	 * numbers for better readability.
	 */
	class Convert {

		/**
		 * Array of common name prefixes.
		 *
		 * @var array
		 */
		private static array $prefixes = [ 'mr', 'mrs', 'ms', 'miss', 'dr', 'prof', 'rev' ];

		/**
		 * Array of common name suffixes.
		 *
		 * @var array
		 */
		private static array $suffixes = [ 'jr', 'sr', 'i', 'ii', 'iii', 'iv' ];

		/** Name Handling **********************************************************/

		/**
		 * Returns major version from version number.
		 *
		 * @param string $v Version number.
		 *
		 * @return string
		 */
		public static function major_version( string $v ): string {
			$version = explode( '.', $v );
			if ( count( $version ) > 1 ) {
				return $version[0] . '.' . $version[1];
			} else {
				return $v;
			}
		}

		/**
		 * Split a full name into its components.
		 *
		 * @param string $full_name The full name to split.
		 *
		 * @return array An array containing 'prefix', 'first_name', 'middle_name', 'last_name', and 'suffix'.
		 */
		public static function split_name( string $full_name ): array {
			$name_parts = preg_split( '/\s+/', trim( $full_name ) );
			$result     = [
				'prefix'      => '',
				'first_name'  => '',
				'middle_name' => '',
				'last_name'   => '',
				'suffix'      => ''
			];

			// Check for prefix
			$first_part = strtolower( str_replace( '.', '', $name_parts[0] ?? '' ) );
			if ( in_array( $first_part, self::$prefixes, true ) ) {
				$result['prefix'] = array_shift( $name_parts );
			}

			// Check for suffix
			$last_part = strtolower( str_replace( '.', '', end( $name_parts ) ?: '' ) );
			if ( in_array( $last_part, self::$suffixes, true ) ) {
				$result['suffix'] = array_pop( $name_parts );
			}

			// Assign remaining parts
			$count = count( $name_parts );
			if ( $count > 0 ) {
				$result['first_name'] = $name_parts[0];
			}
			if ( $count > 2 ) {
				$result['last_name']   = array_pop( $name_parts );
				$result['middle_name'] = implode( ' ', array_slice( $name_parts, 1 ) );
			} elseif ( $count == 2 ) {
				$result['last_name'] = $name_parts[1];
			}

			return $result;
		}

		/**
		 * Add a custom prefix to the list of recognized prefixes.
		 *
		 * @param string $prefix The prefix to add.
		 */
		public static function add_prefix( string $prefix ): void {
			$prefix = strtolower( trim( $prefix ) );
			if ( ! in_array( $prefix, self::$prefixes, true ) ) {
				self::$prefixes[] = $prefix;
			}
		}

		/**
		 * Add a custom suffix to the list of recognized suffixes.
		 *
		 * @param string $suffix The suffix to add.
		 */
		public static function add_suffix( string $suffix ): void {
			$suffix = strtolower( trim( $suffix ) );
			if ( ! in_array( $suffix, self::$suffixes, true ) ) {
				self::$suffixes[] = $suffix;
			}
		}

		/** Utility Methods ********************************************************/

		/**
		 * Split the number and period from the input string.
		 *
		 * @param string $string The input string in the format '7day', '8minutes', etc.
		 *
		 * @return array An array with 'number' and 'period' keys.
		 */
		public static function to_unit_value( string $string ): array {
			$matches = [];
			preg_match( '/^(\d+)(\D+)$/', $string, $matches );

			if ( count( $matches ) !== 3 ) {
				return [
					'number' => 0,
					'period' => '',
				];
			}

			return [
				'number' => (int) $matches[1],
				'period' => strtolower( trim( $matches[2] ) ),
			];
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

		/**
		 * Prettify a version number.
		 *
		 * @param string $version Version number to prettify.
		 *
		 * @return string Prettified version number.
		 */
		public static function prettify_version( string $version ): string {
			return preg_replace( '/(\d+\.\d+)\.0+$/', '$1', $version );
		}

		/**
		 * Get the ordinal suffix for a number (st, nd, rd, th).
		 *
		 * @param int $number The number to get the suffix for.
		 *
		 * @return string The ordinal suffix.
		 */
		public static function ordinal_suffix( int $number ): string {
			$suffixes = [ 'th', 'st', 'nd', 'rd' ];
			$mod100   = $number % 100;

			return $number . ( $mod100 >= 11 && $mod100 <= 13 ? 'th' : $suffixes[ $number % 10 ] ?? 'th' );
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
			$unit_value = self::to_unit_value( $value );
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