<?php
/**
 * User Avatar Trait
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\User;

use WP_User;

trait Avatar {
	use Core;

	/**
	 * Get the user's avatar (the actual HTML).
	 *
	 * @param int    $user_id Optional. User ID. Default is the current user.
	 * @param int    $size    Optional. Avatar size in pixels. Default 96.
	 * @param string $default Optional. URL for the default avatar.
	 * @param string $alt     Optional. Alt text for the avatar image.
	 * @param array  $args    Optional. Extra arguments to retrieve the avatar.
	 *
	 * @return string HTML for the user's avatar or empty string.
	 */
	public static function get_avatar( int $user_id = 0, int $size = 96, string $default = '', string $alt = '', array $args = [] ): string {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return '';
		}

		$args = array_merge( [
			'size'    => $size,
			'default' => $default,
			'alt'     => $alt
		], $args );

		return get_avatar( $user->ID, $size, $default, $alt, $args );
	}

	/**
	 * Get the user's avatar URL.
	 *
	 * @param int    $user_id Optional. User ID. Default is the current user.
	 * @param int    $size    Optional. Avatar size in pixels. Default 96.
	 * @param string $default Optional. Default avatar URL.
	 *
	 * @return string Avatar URL or default value.
	 */
	public static function get_avatar_url( int $user_id = 0, int $size = 96, string $default = '' ): string {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return $default;
		}

		$avatar_url = get_avatar_url( $user->ID, [ 'size' => $size ] );

		return $avatar_url ?: $default;
	}

	/**
	 * Check if the user has a profile picture (custom avatar).
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 *
	 * @return bool True if user has a custom avatar, false otherwise.
	 */
	public static function has_profile_picture( int $user_id = 0 ): bool {
		return ! get_avatar_data( $user_id )['found_avatar'];
	}

}