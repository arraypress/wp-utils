<?php
/**
 * Users Management Class
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Users;

use ArrayPress\Utils\Common\Sanitize;

/**
 * Users Management Trait
 */
trait Management {

	/**
	 * Delete users by IDs.
	 *
	 * @param int[] $user_ids An array of user IDs.
	 *
	 * @return bool True if all users were deleted successfully, false otherwise.
	 */
	public static function delete( array $user_ids ): bool {
		$user_ids = Sanitize::object_ids( $user_ids );

		if ( empty( $user_ids ) ) {
			return false;
		}

		$success = true;

		foreach ( $user_ids as $user_id ) {
			if ( ! wp_delete_user( $user_id ) ) {
				$success = false;
			}
		}

		return $success;
	}

}