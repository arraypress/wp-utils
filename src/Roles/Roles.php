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
 * Class Roles
 *
 * Utility functions for working with multiple roles.
 */
class Roles {

	/**
	 * Get all registered role names.
	 *
	 * @return array Associative array containing role names with lowercase role slugs as keys.
	 */
	public static function get_names(): array {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		return array_change_key_case( $wp_roles->get_names() );
	}

	/**
	 * Get all registered role slugs.
	 *
	 * @return array Array of lowercase role slugs.
	 */
	public static function get_slugs(): array {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		return array_map( 'strtolower', array_keys( $wp_roles->get_names() ) );
	}

	/**
	 * Get roles for a specific user.
	 *
	 * @param int $user_id Optional. User ID. Default is the current logged-in user.
	 *
	 * @return array|false An array of role names, or false on failure.
	 */
	public static function get_user_roles( int $user_id = 0 ) {
		$user = User::get( $user_id );

		return $user ? $user->roles : false;
	}

	/**
	 * Get the options for editable roles.
	 *
	 * @param bool $include_guest Optional. Whether to include the guest role option. Default false.
	 *
	 * @return array An array of role options in label/value format.
	 */
	public static function get_options( bool $include_guest = false ): array {
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