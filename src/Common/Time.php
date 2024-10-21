<?php
/**
 * Date and Time Utilities
 *
 * This class provides utility functions for handling date and time operations,
 * including timezone conversions, formatting, parsing, and calculations. It offers
 * methods for working with request times, human-readable time differences, UTC
 * and local time conversions, week ranges, and date differencing.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       2.1.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

use DateTime;
use DateTimeZone;
use function gmdate;

/**
 * Check if the class `Time` is defined, and if not, define it.
 */
if ( ! class_exists( 'Time' ) ) :

	/**
	 * Time Utility Class
	 *
	 * This class provides utility methods for handling time-related operations.
	 */
	class Time {

		/**
		 * Get the current request time.
		 *
		 * Returns the time according to the very beginning of the current page load.
		 *
		 * @param string $type     The type of time to retrieve. Accepts 'mysql', 'timestamp', or any valid PHP date format.
		 * @param string $timezone The timezone to use. Default is 'UTC'.
		 *
		 * @return int|string Integer if $type is 'timestamp', string otherwise.
		 */
		public static function get_request_time( string $type = 'timestamp', string $timezone = 'UTC' ) {
			global $timestart;

			if ( empty( $timestart ) ) {
				$timestart = microtime( true );
			}

			$offset = ( $timezone !== 'UTC' ) ? self::get_timezone_offset( (int) $timestart, $timezone ) : 0;

			switch ( $type ) {
				case 'mysql':
				case 'Y-m-d H:i:s':
					return ( $timezone === 'UTC' )
						? gmdate( 'Y-m-d H:i:s' )
						: gmdate( 'Y-m-d H:i:s', (int) ( $timestart + $offset ) );
				case 'timestamp':
				default:
					return ( $timezone === 'UTC' ) ? $timestart : ( $timestart + $offset );
			}
		}

		/**
		 * Get the timezone offset.
		 *
		 * @param int    $timestamp The timestamp to calculate the offset for.
		 * @param string $timezone  The timezone to get the offset for.
		 *
		 * @return int The timezone offset in seconds.
		 * @throws DateMalformedStringException
		 * @throws DateInvalidTimeZoneException
		 */
		public static function get_timezone_offset( int $timestamp, string $timezone ): int {
			$dateTime = new DateTime( '@' . $timestamp, new DateTimeZone( 'UTC' ) );
			$dateTime->setTimezone( new DateTimeZone( $timezone ) );

			return $dateTime->getOffset();
		}

		/**
		 * Format a human-readable time difference.
		 *
		 * @param int|string $from     The start time (Unix timestamp or MySQL datetime).
		 * @param int|string $to       The end time (Unix timestamp or MySQL datetime). Default is current time.
		 * @param bool       $detailed Whether to return a detailed difference. Default is false.
		 *
		 * @return string A human-readable time difference.
		 */
		public static function human_time_diff( $from, $to = '', bool $detailed = false ): string {
			$from = is_numeric( $from ) ? $from : strtotime( $from );
			$to   = $to ? ( is_numeric( $to ) ? $to : strtotime( $to ) ) : time();

			$diff = (int) abs( $to - $from );

			if ( $diff < MINUTE_IN_SECONDS ) {
				$secs = $diff;

				return $detailed ? sprintf( _n( '%s second', '%s seconds', $secs ), $secs ) : __( 'less than a minute' );
			}

			$minutes = round( $diff / MINUTE_IN_SECONDS );
			if ( $minutes < 60 ) {
				return sprintf( _n( '%s minute', '%s minutes', $minutes ), $minutes );
			}

			$hours = round( $diff / HOUR_IN_SECONDS );
			if ( $hours < 24 ) {
				return sprintf( _n( '%s hour', '%s hours', $hours ), $hours );
			}

			$days = round( $diff / DAY_IN_SECONDS );
			if ( $days < 7 ) {
				return sprintf( _n( '%s day', '%s days', $days ), $days );
			}

			$weeks = round( $diff / WEEK_IN_SECONDS );
			if ( $weeks < 4 ) {
				return sprintf( _n( '%s week', '%s weeks', $weeks ), $weeks );
			}

			$months = round( $diff / ( 30 * DAY_IN_SECONDS ) );
			if ( $months < 12 ) {
				return sprintf( _n( '%s month', '%s months', $months ), $months );
			}

			$years = round( $diff / YEAR_IN_SECONDS );

			return sprintf( _n( '%s year', '%s years', $years ), $years );
		}

		/**
		 * Convert a UTC timestamp to a local timezone.
		 *
		 * @param int    $utc_timestamp The UTC timestamp to convert.
		 * @param string $timezone      The timezone to convert to.
		 *
		 * @return int The local timestamp.
		 */
		public static function utc_to_local( int $utc_timestamp, string $timezone ): int {
			$dt = new DateTime( '@' . $utc_timestamp, new DateTimeZone( 'UTC' ) );
			$dt->setTimezone( new DateTimeZone( $timezone ) );

			return $dt->getTimestamp();
		}

		/**
		 * Convert a local timestamp to UTC.
		 *
		 * @param int    $local_timestamp The local timestamp to convert.
		 * @param string $timezone        The timezone of the local timestamp.
		 *
		 * @return int The UTC timestamp.
		 */
		public static function local_to_utc( int $local_timestamp, string $timezone ): int {
			$dt = new DateTime( '@' . $local_timestamp, new DateTimeZone( $timezone ) );
			$dt->setTimezone( new DateTimeZone( 'UTC' ) );

			return $dt->getTimestamp();
		}

		/**
		 * Format a timestamp into a specified format.
		 *
		 * @param string   $format    The desired format (default is 'Y-m-d H:i:s').
		 * @param int|null $timestamp The timestamp to format (default is current time).
		 * @param string   $timezone  The timezone to use for formatting (default is 'UTC').
		 *
		 * @return string The formatted date and time.
		 */
		public static function format( string $format = 'Y-m-d H:i:s', int $timestamp = null, string $timezone = 'UTC' ): string {
			$timestamp = $timestamp ?? time();
			$dateTime  = new DateTime( '@' . $timestamp, new DateTimeZone( 'UTC' ) );
			$dateTime->setTimezone( new DateTimeZone( $timezone ) );

			return $dateTime->format( $format );
		}

		/**
		 * Parse a date string into a timestamp.
		 *
		 * @param string $date     The date string to parse.
		 * @param string $timezone The timezone of the date string (default is 'UTC').
		 *
		 * @return int|false The parsed timestamp, or false on failure.
		 */
		public static function parse_date( string $date, string $timezone = 'UTC' ) {
			$dateTime = DateTime::createFromFormat( '!Y-m-d H:i:s', $date, new DateTimeZone( $timezone ) );

			return $dateTime ? $dateTime->getTimestamp() : false;
		}

		/**
		 * Get the start and end timestamps of a given week.
		 *
		 * @param int    $week     The week number (1-53).
		 * @param int    $year     The year.
		 * @param string $timezone The timezone to use (default is 'UTC').
		 *
		 * @return array An array with 'start' and 'end' timestamps.
		 */
		public static function get_week_range( int $week, int $year, string $timezone = 'UTC' ): array {
			$dto = new DateTime();
			$dto->setISODate( $year, $week );
			$dto->setTime( 0, 0, 0 );
			$dto->setTimezone( new DateTimeZone( $timezone ) );

			$start = $dto->getTimestamp();
			$dto->modify( '+6 days' );
			$dto->setTime( 23, 59, 59 );
			$end = $dto->getTimestamp();

			return [ 'start' => $start, 'end' => $end ];
		}

		/**
		 * Calculate the difference between two dates.
		 *
		 * @param string|int $date1    The first date (timestamp or date string).
		 * @param string|int $date2    The second date (timestamp or date string).
		 * @param string     $interval The interval to return ('y' for years, 'm' for months, 'd' for days).
		 *
		 * @return int The difference in the specified interval.
		 */
		public static function date_diff( $date1, $date2, string $interval = 'd' ): int {
			$datetime1 = is_numeric( $date1 ) ? ( new DateTime() )->setTimestamp( $date1 ) : new DateTime( $date1 );
			$datetime2 = is_numeric( $date2 ) ? ( new DateTime() )->setTimestamp( $date2 ) : new DateTime( $date2 );

			$diff = $datetime1->diff( $datetime2 );

			switch ( $interval ) {
				case 'y':
					return $diff->y;
				case 'm':
					return $diff->y * 12 + $diff->m;
				case 'd':
				default:
					return $diff->days;
			}
		}



	}
endif;