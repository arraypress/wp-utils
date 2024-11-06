<?php
/**
 * WordPress Capability Utilities
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Capabilities;

use ArrayPress\Utils\Users\User;
use WP_Roles;

/**
 * Class Capability
 *
 * Utility functions for working with a specific capability.
 */
class Capability {

	/**
	 * Check if a capability exists in WordPress.
	 *
	 * @param string $capability The capability to check.
	 *
	 * @return bool Whether the capability exists.
	 */
	public static function exists( string $capability ): bool {
		return in_array( $capability, Capabilities::get(), true );
	}

	/**
	 * Check if the specified user has the specified capability.
	 *
	 * @param string $capability The capability to check.
	 * @param int    $user_id    Optional. User ID. Default is the current logged-in user.
	 *
	 * @return bool Whether the specified user has the specified capability.
	 */
	public static function user_has( string $capability, int $user_id = 0 ): bool {
		$user = User::get( $user_id );

		if ( ! $user ) {
			return false;
		}

		return $user->has_cap( $capability );
	}

	/**
	 * Format a capability name for display.
	 *
	 * @param string $capability The capability name to format.
	 *
	 * @return string The formatted capability name.
	 */
	public static function format_name( string $capability ): string {
		$capability = str_replace( [ 'cap_', 'wp_' ], '', $capability );
		$capability = str_replace( [ '_', '-' ], ' ', $capability );

		return ucwords( $capability );
	}

	/**
	 * Get the type/group of a capability.
	 *
	 * @param string $capability The capability to check.
	 *
	 * @return string The capability type/group.
	 */
	public static function get_type( string $capability ): string {
		$types = [
			'post'     => [ 'edit_posts', 'publish_posts', 'delete_posts' ],
			'page'     => [ 'edit_pages', 'publish_pages', 'delete_pages' ],
			'user'     => [ 'edit_users', 'create_users', 'delete_users' ],
			'plugin'   => [ 'install_plugins', 'activate_plugins', 'update_plugins' ],
			'theme'    => [ 'switch_themes', 'edit_theme_options', 'install_themes' ],
			'core'     => [ 'manage_options', 'update_core', 'export' ],
			'comment'  => [ 'moderate_comments', 'edit_comments' ],
			'taxonomy' => [ 'manage_categories', 'edit_terms', 'delete_terms' ],
			'media'    => [ 'upload_files', 'edit_files' ]
		];

		foreach ( $types as $type => $caps ) {
			foreach ( $caps as $cap ) {
				if ( strpos( $capability, $cap ) !== false ) {
					return $type;
				}
			}
		}

		return 'other';
	}

	/**
	 * Get all users who have a specific capability.
	 *
	 * @param string $capability The capability to check for.
	 *
	 * @return array Array of user IDs that have the capability.
	 */
	public static function get_users_with( string $capability ): array {
		global $wpdb;
		if ( ! self::exists( $capability ) ) {
			return [];
		}
		$meta_key = $wpdb->get_blog_prefix() . 'capabilities';
		$users    = get_users( [
			'meta_key'     => $meta_key,
			'meta_value'   => $capability,
			'meta_compare' => 'LIKE',
			'fields'       => 'ids'
		] );

		return array_filter( $users, function ( $user_id ) use ( $capability ) {
			return self::user_has( $capability, $user_id );
		} );
	}

	/**
	 * Get all users from a list who have a specific capability.
	 *
	 * @param string $capability The capability to check for.
	 * @param array  $user_ids   Array of user IDs to check.
	 *
	 * @return array Array of user IDs that have the capability.
	 */
	public static function users_have( string $capability, array $user_ids ): array {
		if ( ! self::exists( $capability ) ) {
			return [];
		}

		return array_filter( $user_ids, function ( $user_id ) use ( $capability ) {
			return self::user_has( $capability, $user_id );
		} );
	}

	/**
	 * Get all roles that have a specific capability.
	 *
	 * @param string $capability The capability to check for.
	 *
	 * @return array Array of role names that have the capability.
	 */
	public static function get_roles_with( string $capability ): array {
		global $wp_roles;
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
		$roles_with_cap = [];
		foreach ( $wp_roles->roles as $role_name => $role_data ) {
			if ( ! empty( $role_data['capabilities'][ $capability ] ) ) {
				$roles_with_cap[] = $role_name;
			}
		}

		return $roles_with_cap;
	}

}