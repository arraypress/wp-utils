<?php
/**
 * User Roles Trait
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\User;

use ArrayPress\Utils\Roles\Role;

trait Roles {

	/**
	 * Check if the user has a specific role.
	 *
	 * @param int    $user_id User ID.
	 * @param string $role    The role to check for.
	 *
	 * @return bool Whether the user has the role.
	 */
	public static function has_role( int $user_id, string $role ): bool {
		return Role::user_has( $role, $user_id );
	}

	/**
	 * Get all roles for the user.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return array Array of role slugs.
	 */
	public static function get_roles( int $user_id ): array {
		return Role::get_for_user( $user_id );
	}

	/**
	 * Check if the user has any of the specified roles.
	 *
	 * @param int          $user_id User ID.
	 * @param array|string $roles   Single role or array of roles to check.
	 *
	 * @return bool True if user has any of the roles.
	 */
	public static function has_any_role( int $user_id, $roles ): bool {
		return Role::user_has_any( $roles, $user_id );
	}

	/**
	 * Check if the user has all the specified roles.
	 *
	 * @param int          $user_id User ID.
	 * @param array|string $roles   Single role or array of roles to check.
	 *
	 * @return bool True if user has all the roles.
	 */
	public static function has_all_roles( int $user_id, $roles ): bool {
		return Role::user_has_all( $roles, $user_id );
	}

	/**
	 * Set a role for the user, replacing any existing roles.
	 *
	 * @param int    $user_id User ID.
	 * @param string $role    The role to set.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function set_role( int $user_id, string $role ): bool {
		return Role::set_for_user( $role, $user_id );
	}

	/**
	 * Add a role to the user.
	 *
	 * @param int    $user_id User ID.
	 * @param string $role    The role to add.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function add_role( int $user_id, string $role ): bool {
		return Role::add_to_user( $role, $user_id );
	}

	/**
	 * Remove a role from the user.
	 *
	 * @param int    $user_id User ID.
	 * @param string $role    The role to remove.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function remove_role( int $user_id, string $role ): bool {
		return Role::remove_from_user( $role, $user_id );
	}

}