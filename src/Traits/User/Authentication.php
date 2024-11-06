<?php
/**
 * User Authentication Trait
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\User;

trait Authentication {

	/**
	 * Check if a user is logged in.
	 *
	 * @return bool True if a user is logged in, false otherwise.
	 */
	public static function is_logged_in(): bool {
		return is_user_logged_in();
	}

	/**
	 * Check if the current user is a guest (not logged in).
	 *
	 * @return bool True if the current user is a guest, false if logged in.
	 */
	public static function is_guest(): bool {
		return ! self::is_logged_in();
	}

	/**
	 * Check if a specific user is the current logged-in user.
	 *
	 * @param int $user_id The user ID to check.
	 *
	 * @return bool True if this is the current user, false otherwise.
	 */
	public static function is_current_user( int $user_id ): bool {
		return get_current_user_id() === $user_id;
	}

}