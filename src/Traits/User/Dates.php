<?php
/**
 * Trait: User Dates
 *
 * Provides functionality for working with WordPress user dates and times.
 * Includes methods for date formatting, age calculations, time differences,
 * and temporal analysis of user activities.
 *
 * @package     ArrayPress\Utils\Traits\User
 * @since       1.0.0
 * @author      David Sherlock
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\User;

use WP_User;

trait Dates {
	use Core;

	/**
	 * Get user registration date.
	 *
	 * @param int    $user_id Optional. User ID. Default is the current user.
	 * @param string $format  Optional. PHP date format. Default 'Y-m-d H:i:s'.
	 *
	 * @return string|null Formatted date or null if user not found.
	 */
	public static function get_date( int $user_id = 0, string $format = 'Y-m-d H:i:s' ): ?string {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return null;
		}

		return mysql2date( $format, $user->user_registered );
	}

	/**
	 * Get user registration date in GMT.
	 *
	 * @param int    $user_id Optional. User ID. Default is the current user.
	 * @param string $format  Optional. PHP date format. Default 'Y-m-d H:i:s'.
	 *
	 * @return string|null Formatted GMT date or null if user not found.
	 */
	public static function get_date_gmt( int $user_id = 0, string $format = 'Y-m-d H:i:s' ): ?string {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return null;
		}

		return mysql2date( $format, get_gmt_from_date( $user->user_registered ) );
	}

	/**
	 * Get user account age in days.
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 *
	 * @return int|null Number of days since registration, or null if user not found.
	 */
	public static function get_age( int $user_id = 0 ): ?int {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return null;
		}

		$registration_date = strtotime( $user->user_registered );

		return (int) floor( ( time() - $registration_date ) / DAY_IN_SECONDS );
	}

	/**
	 * Get human-readable time difference since registration.
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 *
	 * @return string|null Human-readable time difference or null if user not found.
	 */
	public static function get_time_diff( int $user_id = 0 ): ?string {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return null;
		}

		$registration_time = strtotime( $user->user_registered );

		return human_time_diff( $registration_time, time() );
	}

	/**
	 * Check if user registered within time period.
	 *
	 * @param int    $user_id Optional. User ID. Default is the current user.
	 * @param string $period  Time period to check (e.g., '24 hours', '7 days', '1 month').
	 *
	 * @return bool|null True if within period, false if not, null if user not found.
	 */
	public static function is_within_time_period( int $user_id = 0, string $period = '7 days' ): ?bool {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return null;
		}

		$registration_time = strtotime( $user->user_registered );
		$period_time       = strtotime( '-' . $period );

		return $registration_time >= $period_time;
	}

	/**
	 * Get the user's last login timestamp.
	 *
	 * @param int         $user_id  Optional. User ID. Default is the current user.
	 * @param string      $format   Optional. Date format. Default is 'Y-m-d H:i:s'.
	 * @param string|null $meta_key Optional. Custom meta key for last login.
	 *
	 * @return string|null Last login date or null if never logged in.
	 */
	public static function get_last_login( int $user_id = 0, string $format = 'Y-m-d H:i:s', ?string $meta_key = null ): ?string {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return null;
		}

		$key        = $meta_key ?? self::get_meta_key( 'last_login' );
		$last_login = get_user_meta( $user->ID, $key, true );

		return $last_login ? date( $format, $last_login ) : null;
	}

	/**
	 * Update the user's last login timestamp.
	 *
	 * @param int         $user_id  Optional. User ID. Default is the current user.
	 * @param string|null $meta_key Optional. Custom meta key for last login.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function update_last_login( int $user_id = 0, ?string $meta_key = null ): bool {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return false;
		}

		$key = $meta_key ?? self::get_meta_key( 'last_login' );

		return update_user_meta( $user->ID, $key, time() );
	}

	/**
	 * Get time since last login.
	 *
	 * @param int         $user_id  Optional. User ID. Default is the current user.
	 * @param string      $unit     Optional. Time unit ('seconds', 'minutes', 'hours', 'days'). Default 'days'.
	 * @param string|null $meta_key Optional. Custom meta key for last login.
	 *
	 * @return int|null Time difference in specified unit, null if error or never logged in.
	 */
	public static function get_time_since_last_login( int $user_id = 0, string $unit = 'days', ?string $meta_key = null ): ?int {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return null;
		}

		$key        = $meta_key ?? self::get_meta_key( 'last_login' );
		$last_login = get_user_meta( $user->ID, $key, true );
		if ( ! $last_login ) {
			return null;
		}

		$diff = time() - (int) $last_login;

		switch ( $unit ) {
			case 'seconds':
				return $diff;
			case 'minutes':
				return (int) floor( $diff / MINUTE_IN_SECONDS );
			case 'hours':
				return (int) floor( $diff / HOUR_IN_SECONDS );
			case 'days':
			default:
				return (int) floor( $diff / DAY_IN_SECONDS );
		}
	}

	/**
	 * Check if the user's last activity was within a specific time period.
	 *
	 * @param int         $user_id  Optional. User ID. Default is the current user.
	 * @param string      $period   Time period to check (e.g., '24 hours', '7 days', '1 month').
	 * @param string|null $meta_key Optional. Custom meta key for last login.
	 *
	 * @return bool|null True if active within period, false if not, null if user not found.
	 */
	public static function is_active_within( int $user_id = 0, string $period = '24 hours', ?string $meta_key = null ): ?bool {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return null;
		}

		$key        = $meta_key ?? self::get_meta_key( 'last_login' );
		$last_login = get_user_meta( $user->ID, $key, true );
		if ( ! $last_login ) {
			return false;
		}

		$period_time = strtotime( '-' . $period );

		return (int) $last_login >= $period_time;
	}

}