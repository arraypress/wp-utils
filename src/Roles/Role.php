<?php
/**
 * WordPress Role Utilities
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Roles;

use WP_Roles;
use ArrayPress\Utils\Users\User;

/**
 * Class Role
 *
 * Utility functions for working with a specific role.
 */
class Role {

	/**
	 * Get capabilities for a specific role.
	 *
	 * @param string $role Role name.
	 *
	 * @return array|false Array of capabilities for the role, or false if role doesn't exist.
	 */
	public static function get_capabilities( string $role ) {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		if ( ! isset( $wp_roles->roles[ $role ] ) ) {
			return false;
		}

		return array_keys( $wp_roles->roles[ $role ]['capabilities'] );
	}

	/**
	 * Get primitive capabilities that are assigned to a role.
	 *
	 * @param string $role Role name.
	 *
	 * @return array Array of primitive capabilities.
	 */
	public static function get_primitive_capabilities( string $role ): array {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$role_obj = $wp_roles->get_role( $role );

		if ( ! $role_obj ) {
			return [];
		}

		return array_keys( array_filter( $role_obj->capabilities, function ( $val ) {
			return (bool) $val;
		} ) );
	}

	/**
	 * Check if a user has a specific role.
	 *
	 * @param string $role    The role to check for.
	 * @param int    $user_id Optional. User ID. Default is the current logged-in user.
	 *
	 * @return bool Whether the user has the role.
	 */
	public static function user_has( string $role, int $user_id = 0 ): bool {
		$user = User::get( $user_id );

		return $user && in_array( $role, $user->roles );
	}

	/**
	 * Sets a role for a user, replacing any existing roles.
	 *
	 * @param string $role    The role to set.
	 * @param int    $user_id Optional. User ID. Default is the current logged-in user.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function set_for_user( string $role, int $user_id = 0 ): bool {
		$user = User::get( $user_id );

		if ( ! $user ) {
			return false;
		}

		$user->set_role( $role );

		return self::user_has( $role, $user_id );
	}

	/**
	 * Add a role to a user.
	 *
	 * @param string $role    The role to add.
	 * @param int    $user_id Optional. User ID. Default is the current logged-in user.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function add_to_user( string $role, int $user_id = 0 ): bool {
		$user = User::get( $user_id );

		if ( ! $user ) {
			return false;
		}

		$user->add_role( $role );

		return self::user_has( $role, $user_id );
	}

	/**
	 * Remove a role from a user.
	 *
	 * @param string $role    The role to remove.
	 * @param int    $user_id Optional. User ID. Default is the current logged-in user.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function remove_from_user( string $role, int $user_id = 0 ): bool {
		$user = User::get( $user_id );

		if ( ! $user ) {
			return false;
		}

		$user->remove_role( $role );

		return ! self::user_has( $role, $user_id );
	}

	/**
	 * Get all roles assigned to a user.
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 *
	 * @return array Array of role slugs or empty array if user doesn't exist.
	 */
	public static function get_for_user( int $user_id = 0 ): array {
		$user = User::get( $user_id );

		return $user ? $user->roles : [];
	}

	/**
	 * Check if a user has any of the specified roles.
	 *
	 * @param array|string $roles   Single role or array of roles to check.
	 * @param int          $user_id Optional. User ID. Default is the current user.
	 *
	 * @return bool True if user has any of the roles.
	 */
	public static function user_has_any( $roles, int $user_id = 0 ): bool {
		$user_roles  = self::get_for_user( $user_id );
		$check_roles = (array) $roles;

		return ! empty( array_intersect( $check_roles, $user_roles ) );
	}

	/**
	 * Check if a user has all the specified roles.
	 *
	 * @param array|string $roles   Single role or array of roles to check.
	 * @param int          $user_id Optional. User ID. Default is the current user.
	 *
	 * @return bool True if user has all the roles.
	 */
	public static function user_has_all( $roles, int $user_id = 0 ): bool {
		$user_roles  = self::get_for_user( $user_id );
		$check_roles = (array) $roles;

		return empty( array_diff( $check_roles, $user_roles ) );
	}

}