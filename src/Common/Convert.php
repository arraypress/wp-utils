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
use DateTimeInterface;

class Convert {

	/**
	 * Convert any value to a 'yes' or 'no' string.
	 *
	 * @param mixed $value      The value to convert.
	 * @param bool  $title_case Whether to return the result in title case.
	 *
	 * @return string 'yes'/'Yes' for truthy values, 'no'/'No' for falsy values.
	 */
	public static function to_yes_no( $value, bool $title_case = false ): string {
		$result = Cast::to_bool( $value ) ? 'yes' : 'no';

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
		$result = Cast::to_bool( $value ) ? 'on' : 'off';

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
		return Cast::to_bool( $value ) ? '1' : '0';
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
		$result = Cast::to_bool( $value ) ? 'true' : 'false';

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
		$type   = strtolower( $type );
		$result = $type === 'int' ? Cast::to_int( $value ) : Cast::to_float( $value );

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

	/**
	 * Convert a time unit to seconds.
	 *
	 * @param string $unit    The unit (day, week, month, year, etc)
	 * @param int    $number  The number of units
	 * @param bool   $use_utc Whether to use UTC time (true) or local time (false)
	 *
	 * @return int Number of seconds
	 */
	public static function unit_number_to_seconds( string $unit, int $number, bool $use_utc = false ): int {
		$current_time = current_time( 'U', $use_utc );

		switch ( $unit ) {
			case 'second':
				return $number;
			case 'minute':
				return $number * 60;
			case 'hour':
				return $number * 3600;
			case 'week':
				return $number * 604800;
			case 'month':
				$future = strtotime( "+{$number} months", $current_time );

				return $future - $current_time;
			case 'year':
				$future = strtotime( "+{$number} years", $current_time );

				return $future - $current_time;
			default:
				return $number * 86400; // default to days
		}
	}

	/**
	 * Convert a date to its age in seconds from current time.
	 *
	 * @param string|int|\DateTimeInterface $date    Date to calculate from
	 * @param bool                          $use_utc Whether to use UTC. Default false
	 *
	 * @return int Age in seconds (negative if date is in future)
	 */
	public static function date_to_seconds( $date, bool $use_utc = false ): int {
		$current = current_time( 'U', $use_utc );

		if ( $date instanceof DateTimeInterface ) {
			return $current - $date->getTimestamp();
		}

		return $current - strtotime( $date );
	}

	/**
	 * Convert a date to age in specified unit.
	 *
	 * @param string|int|DateTimeInterface $date    Date to calculate from
	 * @param string                       $unit    Optional. The unit to return age in ('second', 'minute', 'hour',
	 *                                              'day', 'week', 'month', 'year'). Default 'second'.
	 * @param bool                         $use_utc Optional. Whether to use UTC. Default false.
	 *
	 * @return int Age in specified unit (negative if date is in future)
	 */
	public static function to_age( $date, string $unit = 'second', bool $use_utc = false ): int {
		// Get age in seconds first
		$age_in_seconds = self::date_to_seconds( $date, $use_utc );

		// If seconds requested, return early
		if ( $unit === 'second' ) {
			return $age_in_seconds;
		}

		// Convert seconds to requested unit
		switch ( $unit ) {
			case 'minute':
				return (int) floor( $age_in_seconds / 60 );
			case 'hour':
				return (int) floor( $age_in_seconds / 3600 );
			case 'day':
				return (int) floor( $age_in_seconds / DAY_IN_SECONDS );
			case 'week':
				return (int) floor( $age_in_seconds / WEEK_IN_SECONDS );
			case 'month':
				$start_timestamp   = $date instanceof DateTimeInterface ? $date->getTimestamp() : strtotime( (string) $date );
				$current_timestamp = current_time( 'U', $use_utc );

				$start_year    = (int) date( 'Y', $start_timestamp );
				$start_month   = (int) date( 'm', $start_timestamp );
				$current_year  = (int) date( 'Y', $current_timestamp );
				$current_month = (int) date( 'm', $current_timestamp );

				return ( ( $current_year - $start_year ) * 12 ) + ( $current_month - $start_month );
			case 'year':
				$start_timestamp   = $date instanceof DateTimeInterface ? $date->getTimestamp() : strtotime( (string) $date );
				$current_timestamp = current_time( 'U', $use_utc );

				return (int) date( 'Y', $current_timestamp ) - (int) date( 'Y', $start_timestamp );
			default:
				return $age_in_seconds;
		}
	}

}