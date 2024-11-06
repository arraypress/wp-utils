<?php
/**
 * Trait: User Spam
 *
 * Provides functionality for managing user spam status and account suspension,
 * including methods to check, set, and remove spam/suspension flags.
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

trait Spam {

	/**
	 * Required trait method for getting user data.
	 *
	 * @param int  $user_id       Optional. User ID. Default is 0.
	 * @param bool $allow_current Optional. Whether to allow fallback to current user. Default true.
	 *
	 * @return WP_User|null
	 */
	abstract protected static function get( int $user_id = 0, bool $allow_current = true ): ?WP_User;

	/**
	 * Required trait method for checking if a user exists.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return bool True if the user exists, false otherwise.
	 */
	abstract protected static function exists( int $user_id ): bool;

	/**
	 * Check if user is suspended.
	 *
	 * @param int         $user_id  The user ID.
	 * @param string|null $meta_key Optional. Custom meta key for suspension status.
	 *
	 * @return bool True if user is suspended, false otherwise.
	 */
	public static function is_suspended( int $user_id, ?string $meta_key = null ): bool {
		$key = $meta_key ?? self::get_meta_key( 'suspended' );

		return (bool) get_user_meta( $user_id, $key, true );
	}

	/**
	 * Suspend a user account.
	 *
	 * @param int         $user_id  The user ID.
	 * @param string|null $meta_key Optional. Custom meta key for suspension status.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function suspend( int $user_id, ?string $meta_key = null ): bool {
		if ( ! static::exists( $user_id ) ) {
			return false;
		}

		$key = $meta_key ?? self::get_meta_key( 'suspended' );

		return update_user_meta( $user_id, $key, true );
	}

	/**
	 * Unsuspend a user account.
	 *
	 * @param int         $user_id  The user ID.
	 * @param string|null $meta_key Optional. Custom meta key for suspension status.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function unsuspend( int $user_id, ?string $meta_key = null ): bool {
		if ( ! static::exists( $user_id ) ) {
			return false;
		}

		$key = $meta_key ?? self::get_meta_key( 'suspended' );

		return delete_user_meta( $user_id, $key );
	}

	/**
	 * Check if a user is spam.
	 *
	 * @param int $user_id The user ID.
	 *
	 * @return bool True if user is marked as spam, false otherwise.
	 */
	public static function is_spam( int $user_id ): bool {
		$user = self::get( $user_id );

		return $user && $user->spam;
	}

	/**
	 * Mark a user as spam.
	 *
	 * @param int $user_id The user ID.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function mark_as_spam( int $user_id ): bool {
		if ( ! self::exists( $user_id ) ) {
			return false;
		}

		return (bool) wp_update_user( [
			'ID'   => $user_id,
			'spam' => '1'
		] );
	}

	/**
	 * Remove spam status from a user.
	 *
	 * @param int $user_id The user ID.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function unmark_as_spam( int $user_id ): bool {
		if ( ! self::exists( $user_id ) ) {
			return false;
		}

		return (bool) wp_update_user( [
			'ID'   => $user_id,
			'spam' => '0'
		] );
	}

	/**
	 * Get suspension timestamp if user is suspended.
	 *
	 * @param int         $user_id  The user ID.
	 * @param string|null $meta_key Optional. Custom meta key for suspension timestamp.
	 *
	 * @return int|null Timestamp when user was suspended, or null if not suspended.
	 */
	public static function get_suspension_time( int $user_id, ?string $meta_key = null ): ?int {
		if ( ! self::is_suspended( $user_id ) ) {
			return null;
		}

		$key       = ( $meta_key ? rtrim( $meta_key, '_time' ) : self::get_meta_key( 'suspended' ) ) . '_time';
		$timestamp = get_user_meta( $user_id, $key, true );

		return $timestamp ? (int) $timestamp : null;
	}

	/**
	 * Suspend a user account with timestamp.
	 *
	 * @param int         $user_id  The user ID.
	 * @param string|null $meta_key Optional. Custom meta key for suspension status.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function suspend_with_timestamp( int $user_id, ?string $meta_key = null ): bool {
		if ( self::suspend( $user_id, $meta_key ) ) {
			$key = ( $meta_key ? rtrim( $meta_key, '_time' ) : self::get_meta_key( 'suspended' ) ) . '_time';

			return update_user_meta( $user_id, $key, time() );
		}

		return false;
	}

}