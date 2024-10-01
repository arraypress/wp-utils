<?php
/**
 * Date and Time Utilities
 *
 * This class provides utility functions for handling date and time operations,
 * including conversions, formatting, and specific date/time component retrieval.
 *
 * @package       ArrayPress/Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       2.1.0
 * @author        David Sherlock
 */

namespace ArrayPress\Utils;

/**
 * Check if the class `Date` is defined, and if not, define it.
 */
if ( ! class_exists( 'Date' ) ) :

	class Date {

		/**
		 * Convert various date formats to a timestamp.
		 *
		 * @param mixed $time The date/time to convert.
		 *
		 * @return int|false Timestamp on success, false on failure.
		 */
		public static function to_timestamp( $time ) {
			if ( $time instanceof \DateTimeInterface ) {
				return $time->getTimestamp();
			}
			if ( is_numeric( $time ) ) {
				return (int) $time;
			}

			return strtotime( $time );
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
			$datetime1 = new \DateTime( date( 'Y-m-d H:i:s', self::to_timestamp( $date1 ) ) );
			$datetime2 = new \DateTime( date( 'Y-m-d H:i:s', self::to_timestamp( $date2 ) ) );
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
			$datetime = new \DateTime( date( 'Y-m-d H:i:s', self::to_timestamp( $date ) ) );
			$datetime->modify( "+{$amount} {$unit}" );

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
			$start_date = new \DateTime();
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
		public static function is_in_range( $date, $start, $end ): bool {
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
	}

endif;