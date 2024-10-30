<?php
/**
 * User Role Utilities
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Users;

use WP_Roles;
use WP_User;

/**
 * Check if the class `Roles` is defined, and if not, define it.
 */
if ( ! class_exists( 'Roles' ) ) :

	/**
	 * User Role Utility Functions
	 *
	 * Provides static utility functions for user role related operations.
	 */
	class Roles {

		/**
		 * Check if the specified user has any of the specified roles.
		 *
		 * @param array|string $user_roles A single role or an array of roles to check for.
		 * @param int          $user_id    Optional. User ID. Default is the current logged-in user.
		 *
		 * @return bool Whether the specified user has any of the specified roles.
		 */
		public static function has_role( $user_roles, int $user_id = 0 ): bool {
			if ( ! is_array( $user_roles ) ) {
				$user_roles = [ $user_roles ];
			}

			$user_roles = array_unique( $user_roles );
			$user       = User::get_data( $user_id );

			if ( ! $user ) {
				return false;
			}

			return count( array_intersect( $user_roles, $user->roles ) ) > 0;
		}

		/**
		 * Check if the specified user has the specified capability.
		 *
		 * @param string $capability The capability to check.
		 * @param int    $user_id    Optional. User ID. Default is the current logged-in user.
		 *
		 * @return bool Whether the specified user has the specified capability.
		 */
		public static function has_capability( string $capability, int $user_id = 0 ): bool {
			$user = User::get_data( $user_id );

			if ( ! $user ) {
				return false;
			}

			return $user->has_cap( $capability );
		}

		/**
		 * Retrieves the user's primary role.
		 *
		 * @param int $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return string|false The user's primary role, or false on failure.
		 */
		public static function get_role( int $user_id = 0 ) {
			$user_info = User::get_data( $user_id );

			if ( ! $user_info || empty( $user_info->roles ) ) {
				return false;
			}

			return $user_info->roles[0];
		}

		/**
		 * Checks if the specified user is an administrator.
		 *
		 * @param int $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return bool True if the user is an administrator, false otherwise.
		 */
		public static function is_admin( int $user_id = 0 ): bool {
			return self::has_role( 'administrator', $user_id );
		}

		/**
		 * Sets the user's role, replacing any existing roles.
		 *
		 * @param string $role    The role to set.
		 * @param int    $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function set_role( string $role, int $user_id = 0 ): bool {
			$user = User::get_data( $user_id );

			if ( ! $user ) {
				return false;
			}

			$user->set_role( $role );

			return in_array( $role, $user->roles );
		}

		/**
		 * Adds a role to the specified user.
		 *
		 * @param string $role    The role to add.
		 * @param int    $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function add_role( string $role, int $user_id = 0 ): bool {
			$user = User::get_data( $user_id );

			if ( ! $user ) {
				return false;
			}

			$user->add_role( $role );

			return in_array( $role, $user->roles );
		}

		/**
		 * Removes a role from the specified user.
		 *
		 * @param string $role    The role to remove.
		 * @param int    $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function remove_role( string $role, int $user_id = 0 ): bool {
			$user = User::get_data( $user_id );

			if ( ! $user ) {
				return false;
			}

			$user->remove_role( $role );

			return ! in_array( $role, $user->roles );
		}

		/**
		 * Retrieves all roles for the specified user.
		 *
		 * @param int $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return array|false An array of role names, or false on failure.
		 */
		public static function get_roles( int $user_id = 0 ) {
			$user = User::get_data( $user_id );

			if ( ! $user ) {
				return false;
			}

			return $user->roles;
		}

		/**
		 * Retrieves all capabilities for the specified user.
		 *
		 * @param int $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return array|false An array of capabilities, or false on failure.
		 */
		public static function get_capabilities( int $user_id = 0 ) {
			$user = User::get_data( $user_id );

			if ( ! $user ) {
				return false;
			}

			return $user->allcaps;
		}

		/**
		 * Retrieve role names with keys in lowercase of all roles registered in WordPress.
		 *
		 * This method fetches the role names from the global $wp_roles instance and returns an associative
		 * array containing role names where keys are role slugs converted to lowercase.
		 *
		 * @return array Associative array containing role names with lowercase role slugs as keys.
		 */
		public static function get_role_names(): array {
			global $wp_roles;

			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}

			$role_names = $wp_roles->get_names();

			return array_change_key_case( $role_names );
		}

		/**
		 * Retrieve lowercase role slugs of all roles registered in WordPress.
		 *
		 * This method fetches the role names from the global $wp_roles instance, converts them to lowercase,
		 * and returns an array containing all lowercase role slugs.
		 *
		 * @return array Array containing lowercase role slugs.
		 */
		public static function get_role_slugs(): array {
			global $wp_roles;

			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}

			$role_slugs = array_keys( $wp_roles->get_names() );

			return array_map( 'strtolower', $role_slugs );
		}

		/**
		 * Get the options for editable roles.
		 *
		 * @param bool $include_guest Optional. Whether to include the guest role option. Default false.
		 *
		 * @return array An array of role options in label/value format.
		 */
		public static function get_role_options( bool $include_guest = false ): array {
			if ( ! function_exists( 'get_editable_roles' ) ) {
				require_once ABSPATH . '/wp-admin/includes/user.php';
			}

			$roles = get_editable_roles();

			if ( empty( $roles ) || ! is_array( $roles ) ) {
				return [];
			}

			$options = [];

			if ( $include_guest ) {
				$options[] = [
					'value' => 'guest',
					'label' => esc_html__( 'Guest', 'arraypress' ),
				];
			}

			foreach ( $roles as $role => $details ) {
				if ( ! isset( $role, $details['name'] ) ) {
					continue;
				}

				$options[] = [
					'value' => esc_attr( $role ),
					'label' => esc_html( $details['name'] ),
				];
			}

			return $options;
		}

	}
endif;