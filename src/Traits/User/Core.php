<?php
/**
 * User Core Trait
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\User;

use ArrayPress\Utils\Database\Exists;
use WP_User;

trait Core {

	/**
	 * Verify a WordPress user exists by ID in the database.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return bool True if the user exists, false otherwise.
	 */
	public static function exists( int $user_id ): bool {
		return Exists::row( 'users', 'ID', $user_id );
	}

	/**
	 * Get a user object.
	 *
	 * @param int  $user_id       Optional. User ID. Default is 0.
	 * @param bool $allow_current Optional. Whether to allow fallback to current user. Default true.
	 *
	 * @return WP_User|null The user object if found, null otherwise.
	 */
	public static function get( int $user_id = 0, bool $allow_current = true ): ?WP_User {
		$user_id = self::get_validate_user_id( $user_id, $allow_current );
		if ( $user_id === null ) {
			return null;
		}

		$user = get_userdata( $user_id );

		return ( $user instanceof WP_User ) ? $user : null;
	}

	/**
	 * Check if a user exists by email.
	 *
	 * @param string $email Email address to check.
	 *
	 * @return bool True if a user with the email exists, false otherwise.
	 */
	public static function exists_by_email( string $email ): bool {
		return self::get_by_email( $email ) !== null;
	}

	/**
	 * Check if a user exists by login name.
	 *
	 * @param string $login Login name to check.
	 *
	 * @return bool True if a user with the login exists, false otherwise.
	 */
	public static function exists_by_login( string $login ): bool {
		return self::get_by_login( $login ) !== null;
	}

	/**
	 * Get a user by their email address.
	 *
	 * @param string $email The email address to look up.
	 *
	 * @return WP_User|null The user object if found, null otherwise.
	 */
	public static function get_by_email( string $email ): ?WP_User {
		if ( ! is_email( $email ) ) {
			return null;
		}

		$user = get_user_by( 'email', $email );

		return ( $user instanceof WP_User ) ? $user : null;
	}

	/**
	 * Get a user by their login name.
	 *
	 * @param string $login The user's login name.
	 *
	 * @return WP_User|null Returns the user object if found, null otherwise.
	 */
	public static function get_by_login( string $login ): ?WP_User {
		$user = get_user_by( 'login', $login );

		return ( $user instanceof WP_User ) ? $user : null;
	}

	/**
	 * Get a user by their nicename (slug).
	 *
	 * @param string $slug The user's nicename.
	 *
	 * @return WP_User|null Returns the user object if found, null otherwise.
	 */
	public static function get_by_slug( string $slug ): ?WP_User {
		$user = get_user_by( 'slug', $slug );

		return ( $user instanceof WP_User ) ? $user : null;
	}

	/**
	 * Get a user by their display name.
	 *
	 * @param string $display_name The user's display name.
	 *
	 * @return WP_User|null Returns the user object if found, null otherwise.
	 */
	public static function get_by_display_name( string $display_name ): ?WP_User {
		$users = get_users( [
			'search'         => $display_name,
			'search_columns' => [ 'display_name' ],
			'number'         => 1,
		] );

		return ! empty( $users[0] ) ? $users[0] : null;
	}

	/**
	 * Get a user's ID by their email address.
	 *
	 * @param string $email The email address to look up.
	 *
	 * @return int|null The user ID if found, null otherwise.
	 */
	public static function get_id_by_email( string $email ): ?int {
		$user = self::get_by_email( $email );

		return $user ? $user->ID : null;
	}

	/**
	 * Delete a user and optionally reassign their content.
	 *
	 * @param int      $user_id        The ID of the user to delete.
	 * @param int|null $reassign_to    Optional. User ID to reassign posts to. Default null.
	 * @param bool     $delete_content Optional. Whether to delete the user's content. Only used if $reassign_to is
	 *                                 null. Default false.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function delete( int $user_id, ?int $reassign_to = null, bool $delete_content = false ): bool {
		if ( ! self::exists( $user_id ) ) {
			return false;
		}

		// If reassign_to user is specified, verify it exists
		if ( $reassign_to !== null && ! self::exists( $reassign_to ) ) {
			return false;
		}

		if ( $reassign_to === null && $delete_content ) {
			$reassign_to = true; // WordPress uses true to indicate content deletion
		}

		return wp_delete_user( $user_id, $reassign_to );
	}

	/**
	 * Validate and normalize user ID, optionally falling back to current user.
	 *
	 * @param int  $user_id       The user ID to validate.
	 * @param bool $allow_current Whether to allow fallback to current user.
	 *
	 * @return int|null Normalized user ID or null if invalid.
	 */
	public static function get_validate_user_id( int $user_id = 0, bool $allow_current = true ): ?int {
		if ( empty( $user_id ) && $allow_current && is_user_logged_in() ) {
			$user_id = get_current_user_id();
		}

		return empty( $user_id ) ? null : $user_id;
	}

}