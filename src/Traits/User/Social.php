<?php
/**
 * Trait: User Social
 *
 * Provides functionality for managing user social features including
 * online status tracking and social media profiles.
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

trait Social {

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
	 * Check if the user is online (active within the last 15 minutes).
	 *
	 * @param int         $user_id  Optional. User ID. Default is the current user.
	 * @param string|null $meta_key Optional. Custom meta key for last active timestamp.
	 *
	 * @return bool True if user is online, false otherwise.
	 */
	public static function is_online( int $user_id = 0, ?string $meta_key = null ): bool {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return false;
		}

		$key         = $meta_key ?? self::get_meta_key( 'last_active' );
		$last_active = get_user_meta( $user->ID, $key, true );

		if ( ! $last_active ) {
			return false;
		}

		return ( time() - $last_active ) < ( 15 * MINUTE_IN_SECONDS );
	}

	/**
	 * Update the user's last active timestamp.
	 *
	 * @param int         $user_id  Optional. User ID. Default is the current user.
	 * @param string|null $meta_key Optional. Custom meta key for last active timestamp.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function update_last_active( int $user_id = 0, ?string $meta_key = null ): bool {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return false;
		}

		$key = $meta_key ?? self::get_meta_key( 'last_active' );

		return update_user_meta( $user->ID, $key, time() );
	}

	/**
	 * Get social media profiles for a user.
	 *
	 * @param int        $user_id     Optional. User ID. Default is the current user.
	 * @param array|null $custom_keys Optional. Custom meta keys for social profiles.
	 *
	 * @return array Array of social profile URLs.
	 */
	public static function get_social_profiles( int $user_id = 0, ?array $custom_keys = null ): array {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return [];
		}

		$profiles      = [];
		$social_fields = $custom_keys ?? static::get_meta_keys()['social'];

		foreach ( $social_fields as $network => $meta_key ) {
			$value = get_user_meta( $user->ID, $meta_key, true );
			if ( $value ) {
				$profiles[ $network ] = $value;
			}
		}

		return $profiles;
	}

	/**
	 * Set a social media profile URL for a user.
	 *
	 * @param int         $user_id  User ID.
	 * @param string      $network  Social network identifier.
	 * @param string      $url      Profile URL.
	 * @param string|null $meta_key Optional. Custom meta key for this social network.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function set_social_profile( int $user_id, string $network, string $url, ?string $meta_key = null ): bool {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return false;
		}

		$key = $meta_key ?? self::get_meta_key( 'social', $network );

		return update_user_meta( $user->ID, $key, $url );
	}

	/**
	 * Remove a social media profile for a user.
	 *
	 * @param int         $user_id  User ID.
	 * @param string      $network  Social network identifier.
	 * @param string|null $meta_key Optional. Custom meta key for this social network.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function remove_social_profile( int $user_id, string $network, ?string $meta_key = null ): bool {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return false;
		}

		$key = $meta_key ?? self::get_meta_key( 'social', $network );

		return delete_user_meta( $user->ID, $key );
	}

}