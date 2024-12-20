<?php
/**
 * Date and Time Utilities
 *
 * This class provides utility functions for handling date and time operations,
 * including conversions, formatting, and specific date/time component retrieval.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       2.1.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

use DateTimeInterface;
use DateTime;

class Date {

	/**
	 * Convert various date formats to a timestamp.
	 *
	 * @param mixed $datetime The date/time to convert.
	 *
	 * @return int|false Timestamp on success, false on failure.
	 */
	public static function to_timestamp( $datetime ) {
		if ( $datetime instanceof DateTimeInterface ) {
			return $datetime->getTimestamp();
		}
		if ( is_numeric( $datetime ) ) {
			return (int) $datetime;
		}

		return strtotime( $datetime );
	}


	/**
	 * Convert a date string to UTC format.
	 *
	 * @param string $date   Date string in any valid format.
	 * @param string $format The format to return the date in. Default 'Y-m-d H:i:s'.
	 *
	 * @return string Date in UTC format.
	 */
	private static function to_utc( string $date, string $format = 'Y-m-d H:i:s' ): string {
		return gmdate( $format, strtotime( $date ) );
	}

	/**
	 * Format a date.
	 *
	 * @param mixed  $date   Date to format. Can be a timestamp, date string, or DateTime object.
	 * @param string $format Optional. PHP date format. Default is WordPress date_format option.
	 *
	 * @return string Formatted date.
	 */
	public static function format( $date = null, string $format = '' ): string {
		$timestamp = $date ? self::to_timestamp( $date ) : current_time( 'timestamp' );
		$format    = $format ?: get_option( 'date_format' );

		return date_i18n( $format, $timestamp );
	}

	/**
	 * Get the weekday.
	 *
	 * @param mixed $date Date to get weekday from. Default is current time.
	 *
	 * @return string Full weekday name.
	 */
	public static function get_weekday( $date = null ): string {
		return self::format( $date, 'l' );
	}

	/**
	 * Get the numeric day of the month.
	 *
	 * @param mixed $date Date to get day from. Default is current time.
	 *
	 * @return int Day of the month (1-31).
	 */
	public static function get_weekday_number( $date = null ): int {
		return (int) self::format( $date, 'j' );
	}

	/**
	 * Get the month.
	 *
	 * @param mixed $date Date to get month from. Default is current time.
	 *
	 * @return string Full month name.
	 */
	public static function get_month( $date = null ): string {
		return self::format( $date, 'F' );
	}

	/**
	 * Get the numeric representation of the month (1-12).
	 *
	 * @param mixed $date Date to get month from. Default is current time.
	 *
	 * @return int Numeric representation of the month (1-12).
	 */
	public static function get_month_number( $date = null ): int {
		return (int) self::format( $date, 'n' );
	}

	/**
	 * Get the time.
	 *
	 * @param mixed $date Date to get time from. Default is current time.
	 *
	 * @return string Formatted time (H:i).
	 */
	public static function get_time( $date = null ): string {
		return self::format( $date, 'H:i' );
	}

	/**
	 * Get the year.
	 *
	 * @param mixed $date Date to get year from. Default is current time.
	 *
	 * @return string Four-digit year.
	 */
	public static function get_year( $date = null ): string {
		return self::format( $date, 'Y' );
	}

	/**
	 * Get the day of the month.
	 *
	 * @param mixed $date Date to get day from. Default is current time.
	 *
	 * @return string Day of the month (1 to 31).
	 */
	public static function get_day_of_month( $date = null ): string {
		return self::format( $date, 'j' );
	}

	/**
	 * Get the quarter.
	 *
	 * @param mixed $date Date to get quarter from. Default is current time.
	 *
	 * @return string Quarter (Q1, Q2, Q3, or Q4).
	 */
	public static function get_quarter( $date = null ): string {
		$month = (int) self::format( $date, 'n' );

		return 'Q' . ceil( $month / 3 );
	}

	/**
	 * Get human-readable time difference between two dates.
	 *
	 * @param mixed $from Start date.
	 * @param mixed $to   End date. Default is current time.
	 *
	 * @return string Human-readable time difference.
	 */
	public static function time_diff( $from, $to = null ): string {
		$from_timestamp = self::to_timestamp( $from );
		$to_timestamp   = $to ? self::to_timestamp( $to ) : current_time( 'timestamp' );

		return human_time_diff( $from_timestamp, $to_timestamp );
	}

	/**
	 * Check if a given date is in the future.
	 *
	 * @param mixed $date Date to check.
	 *
	 * @return bool True if the date is in the future, false otherwise.
	 */
	public static function is_future( $date ): bool {
		return self::to_timestamp( $date ) > current_time( 'timestamp' );
	}

	/**
	 * Get the difference between two dates in a specified unit.
	 *
	 * @param mixed  $date1 First date.
	 * @param mixed  $date2 Second date.
	 * @param string $unit  Unit of time (years, months, days, hours, minutes, seconds).
	 *
	 * @return int The difference in the specified unit.
	 */
	public static function diff( $date1, $date2, string $unit = 'days' ): int {
		$datetime1 = new DateTime( date( 'Y-m-d H:i:s', self::to_timestamp( $date1 ) ) );
		$datetime2 = new DateTime( date( 'Y-m-d H:i:s', self::to_timestamp( $date2 ) ) );
		$interval  = $datetime1->diff( $datetime2 );

		switch ( $unit ) {
			case 'years':
				return $interval->y;
			case 'months':
				return $interval->y * 12 + $interval->m;
			case 'hours':
				return $interval->days * 24 + $interval->h;
			case 'minutes':
				return ( $interval->days * 24 + $interval->h ) * 60 + $interval->i;
			case 'seconds':
				return ( ( $interval->days * 24 + $interval->h ) * 60 + $interval->i ) * 60 + $interval->s;
			default:
				return $interval->days;
		}
	}

	/**
	 * Add a specified time interval to a date.
	 *
	 * @param mixed  $date   The starting date.
	 * @param int    $amount The amount to add.
	 * @param string $unit   The unit (years, months, days, hours, minutes, seconds).
	 *
	 * @return string The new date after adding the interval.
	 */
	public static function add( $date, int $amount, string $unit ): string {
		$datetime = new DateTime( date( 'Y-m-d H:i:s', self::to_timestamp( $date ) ) );
		$datetime->modify( "+{$amount} {$unit}" );

		return $datetime->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Subtract a specified time interval from a date.
	 *
	 * @param mixed  $date   The starting date.
	 * @param int    $amount The amount to subtract.
	 * @param string $unit   The unit (years, months, days, hours, minutes, seconds).
	 *
	 * @return string The new date after subtracting the interval.
	 */
	public static function subtract( $date, int $amount, string $unit ): string {
		$datetime = new DateTime( date( 'Y-m-d H:i:s', self::to_timestamp( $date ) ) );
		$datetime->modify( "-{$amount} {$unit}" );

		return $datetime->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Get the start and end timestamps of a given week.
	 *
	 * @param int $week_number Week number (1-53).
	 * @param int $year        Optional. The year. Default is current year.
	 *
	 * @return array Associative array with 'start' and 'end' timestamps.
	 */
	public static function week_range( int $week_number, int $year = 0 ): array {
		$year       = $year ?: date( 'Y' );
		$start_date = new DateTime();
		$start_date->setISODate( $year, $week_number );
		$end_date = clone $start_date;
		$end_date->modify( '+6 days' );

		return [
			'start' => $start_date->getTimestamp(),
			'end'   => $end_date->getTimestamp()
		];
	}

	/**
	 * Check if a given date falls within a specified range.
	 *
	 * @param mixed $date  The date to check.
	 * @param mixed $start The start of the range.
	 * @param mixed $end   The end of the range.
	 *
	 * @return bool True if the date is within the range, false otherwise.
	 */
	public static function in_range( $date, $start, $end ): bool {
		$date_timestamp  = self::to_timestamp( $date );
		$start_timestamp = self::to_timestamp( $start );
		$end_timestamp   = self::to_timestamp( $end );

		return ( $date_timestamp >= $start_timestamp && $date_timestamp <= $end_timestamp );
	}

	/**
	 * Get a range of years.
	 *
	 * @param int      $start   Start year. Default 2000.
	 * @param int|null $end     End year. Default current year.
	 * @param bool     $reverse Whether to reverse the order of years. Default false.
	 *
	 * @return array An array of years from start to end.
	 */
	public static function year_range( int $start = 2000, ?int $end = null, bool $reverse = false ): array {
		$end   = $end ?: (int) date( 'Y' );
		$years = range( min( $start, $end ), max( $start, $end ) );
		$years = array_map( function ( $year ) {
			return [
				'value' => (string) $year,
				'label' => esc_html( (string) $year ),
			];
		}, $years );

		return $reverse ? array_reverse( $years ) : $years;
	}

	/**
	 * Converts a time period to seconds.
	 *
	 * @param string $period The period type.
	 * @param int    $count  The count of the periods.
	 *
	 * @return int The number of seconds.
	 */
	public static function to_seconds( string $period, int $count ): int {
		$seconds_map = [
			'second' => 1,
			'minute' => 60,
			'hour'   => 3600,
			'day'    => 86400,
			'week'   => 604800,
			'month'  => 2592000,  // Approximate
			'year'   => 31536000, // Approximate
		];

		return ( $seconds_map[ strtolower( $period ) ] ?? 0 ) * $count;
	}

	/**
	 * Converts a time period to minutes.
	 *
	 * @param string $period The period type.
	 * @param int    $count  The count of the periods.
	 *
	 * @return float The number of minutes.
	 */
	public static function to_minutes( string $period, int $count ): float {
		$minutes_map = [
			'second' => 1 / 60,
			'minute' => 1,
			'hour'   => 60,
			'day'    => 1440,
			'week'   => 10080,
			'month'  => 43200,   // Approximate (30 days)
			'year'   => 525600,  // Approximate (365 days)
		];

		return ( $minutes_map[ strtolower( $period ) ] ?? 0 ) * $count;
	}

	/**
	 * Converts a time period to hours.
	 *
	 * @param string $period The period type.
	 * @param int    $count  The count of the periods.
	 *
	 * @return float The number of hours.
	 */
	public static function to_hours( string $period, int $count ): float {
		$hours_map = [
			'second' => 1 / 3600,
			'minute' => 1 / 60,
			'hour'   => 1,
			'day'    => 24,
			'week'   => 168,
			'month'  => 720,    // Approximate (30 days)
			'year'   => 8760,   // Approximate (365 days)
		];

		return ( $hours_map[ strtolower( $period ) ] ?? 0 ) * $count;
	}

	/**
	 * Check if a date is a weekend.
	 *
	 * @param string $date Date string.
	 *
	 * @return bool True if weekend, false otherwise.
	 */
	public static function is_weekend( string $date ): bool {
		return in_array( date( 'N', strtotime( $date ) ), array( 6, 7 ) );
	}

	/**
	 * Check if a given date is a weekday.
	 *
	 * @param string $date Date string.
	 *
	 * @return bool True if weekday, false if weekend.
	 */
	public static function is_weekday( string $date ): bool {
		return ! self::is_weekend( $date );
	}

	/**
	 * Check if a given date is the start of the week based on WordPress settings.
	 *
	 * @param mixed $date The date to check. Default is current time.
	 *
	 * @return bool True if the date is the start of the week, false otherwise.
	 */
	public static function is_start_of_week( $date = null ): bool {
		$timestamp = $date ? self::to_timestamp( $date ) : current_time( 'timestamp' );

		// Get the day of the week (0 for Sunday, 6 for Saturday)
		$day_of_week = (int) date( 'w', $timestamp );

		// Get WordPress start of week setting (0 for Sunday, 1-6 for Monday-Saturday)
		$wp_start_of_week = (int) get_option( 'start_of_week', 0 );

		// Check if the day of the week matches the WordPress setting
		return $day_of_week === $wp_start_of_week;
	}

	/**
	 * Get the number of days in a given month and year.
	 *
	 * @param int $month Month number (1-12).
	 * @param int $year  Year (4 digits).
	 *
	 * @return int Number of days in the month.
	 */
	public static function days_in_month( int $month, int $year ): int {
		return cal_days_in_month( CAL_GREGORIAN, $month, $year );
	}

	/**
	 * Check if a given year is a leap year.
	 *
	 * @param int $year The year to check.
	 *
	 * @return bool True if it's a leap year, false otherwise.
	 */
	public static function is_leap_year( int $year ): bool {
		return ( ( ( $year % 4 ) == 0 ) && ( ( ( $year % 100 ) != 0 ) || ( ( $year % 400 ) == 0 ) ) );
	}

	/**
	 * Check if the given string is empty or an invalid date.
	 *
	 * @param string|null $date The string to be checked.
	 *
	 * @return bool True if the string is empty or an invalid date, False otherwise.
	 */
	public static function is_empty( ?string $date = null ): bool {
		if ( empty( $date ) || $date === '0000-00-00 00:00:00' ) {
			return true;
		}

		$timestamp = strtotime( $date );

		return $timestamp === false || $timestamp < 0;
	}

	/**
	 * Get an array of dates between two dates.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @param string $format     Output date format.
	 *
	 * @return array Array of dates.
	 */
	public static function get_date_range( string $start_date, string $end_date, string $format = 'Y-m-d' ): array {
		$dates   = array();
		$current = strtotime( $start_date );
		$end     = strtotime( $end_date );

		while ( $current <= $end ) {
			$dates[] = date( $format, $current );
			$current = strtotime( '+1 day', $current );
		}

		return $dates;
	}

	/**
	 * Generate a tooltip with date-related information.
	 *
	 * @param string $type Type of tooltip (e.g., 'date', 'time', 'day', 'month', 'quarter').
	 * @param mixed  $date Optional. Date to use for tooltip. Default is current time.
	 *
	 * @return string Formatted tooltip text.
	 */
	public static function tooltip( string $type, $date = null ): string {
		switch ( $type ) {
			case 'date':
				$value = self::format( $date, 'F j, Y' );

				return sprintf( __( 'Current date is: %s. Select a date to compare.', 'arraypress' ), $value );
			case 'time':
				$value = self::get_time( $date );

				return sprintf( __( 'Current time is: %s.', 'arraypress' ), $value );
			case 'day':
				$value = self::get_weekday( $date );

				return sprintf( __( 'Current weekday is: %s', 'arraypress' ), $value );
			case 'month':
				$value = self::get_month( $date );

				return sprintf( __( 'Current month is: %s.', 'arraypress' ), $value );
			case 'quarter':
				$month   = self::get_month( $date );
				$quarter = self::get_quarter( $date );

				return sprintf( __( 'Current month is: %s. Current quarter is: %s.', 'arraypress' ), $month, $quarter );
			default:
				return __( 'Invalid tooltip type.', 'arraypress' );
		}
	}

	/**
	 * Format a date for display in the WordPress admin area.
	 *
	 * @param string $date   The date to format.
	 * @param string $format Optional. The format to use.
	 *
	 * @return string The formatted date.
	 */
	public static function format_for_admin( string $date, string $format = '' ): string {
		if ( empty( $format ) ) {
			$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		}

		return self::format( $date, $format );
	}

	/**
	 * Get the WordPress timezone string.
	 *
	 * @return string
	 */
	public static function get_wp_timezone(): string {
		return wp_timezone_string();
	}

	/**
	 * Format a date using WordPress i18n functions.
	 *
	 * @param mixed  $date   Date to format. Can be a timestamp, date string, or DateTime object.
	 * @param string $format The format to use (WordPress date format string).
	 *
	 * @return string Formatted date string.
	 */
	public static function wp_date_i18n( $date, string $format ): string {
		$timestamp = self::to_timestamp( $date );

		return date_i18n( $format, $timestamp );
	}

	/**
	 * Get the current time, either in the site's timezone or UTC.
	 *
	 * @param string $type    Optional. Type of time to retrieve. Accepts 'mysql',
	 *                        'timestamp', or PHP date format string. Default 'mysql'.
	 * @param bool   $gmt     Optional. Whether to use GMT timezone. Default false.
	 *
	 * @return int|string Integer if $type is 'timestamp', string otherwise.
	 */
	public static function get_current_time( string $type = 'mysql', bool $gmt = false ) {
		$timestamp = current_time( 'timestamp', $gmt );

		switch ( $type ) {
			case 'timestamp':
				return $timestamp;
			case 'mysql':
				return date( 'Y-m-d H:i:s', $timestamp );
			default:
				return date( $type, $timestamp );
		}
	}

}