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
 * Class Capabilities
 *
 * Utility functions for working with multiple capabilities.
 */
class Capabilities {

	/**
	 * Check which capabilities exist from a given list.
	 *
	 * @param array $capabilities Array of capabilities to check.
	 *
	 * @return array Array of existing capabilities.
	 */
	public static function exists( array $capabilities ): array {
		$all_caps = self::get();

		return array_intersect( $capabilities, $all_caps );
	}

	/**
	 * Get all registered capabilities in WordPress.
	 *
	 * @return array Array of all registered capabilities.
	 */
	public static function get(): array {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$capabilities = [];

		foreach ( $wp_roles->roles as $role ) {
			if ( ! empty( $role['capabilities'] ) ) {
				$capabilities = array_merge( $capabilities, array_keys( $role['capabilities'] ) );
			}
		}

		return array_unique( $capabilities );
	}

	/**
	 * Get all capabilities for multiple users.
	 *
	 * @param array $user_ids Array of user IDs to get capabilities for.
	 *
	 * @return array Array of user IDs and their capabilities.
	 */
	public static function get_for_users( array $user_ids ): array {
		$results = [];
		foreach ( $user_ids as $user_id ) {
			$user = User::get( $user_id );
			if ( $user ) {
				$results[ $user_id ] = array_keys( $user->allcaps );
			}
		}

		return $results;
	}

	/**
	 * Get capabilities by type.
	 *
	 * @param string $type Type of capabilities to get.
	 *
	 * @return array Array of capabilities of the specified type.
	 */
	public static function get_by_type( string $type ): array {
		return array_filter( self::get(), function ( $capability ) use ( $type ) {
			return Capability::get_type( $capability ) === $type;
		} );
	}

	/**
	 * Get a user's capabilities.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return array Array of capabilities or empty array if user doesn't exist.
	 */
	public static function get_for_user( int $user_id ): array {
		$user = User::get( $user_id );

		return $user ? array_keys( $user->allcaps ) : [];
	}

	/**
	 * Check if a user has any of the specified capabilities.
	 *
	 * @param array|string $capabilities Single capability or array of capabilities to check.
	 * @param int          $user_id      User ID.
	 *
	 * @return bool True if user has any of the capabilities.
	 */
	public static function user_has_any( $capabilities, int $user_id ): bool {
		$user = User::get( $user_id );
		if ( ! $user ) {
			return false;
		}

		$check_capabilities = (array) $capabilities;
		foreach ( $check_capabilities as $capability ) {
			if ( $user->has_cap( $capability ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a user has all of the specified capabilities.
	 *
	 * @param array|string $capabilities Single capability or array of capabilities to check.
	 * @param int          $user_id      User ID.
	 *
	 * @return bool True if user has all of the capabilities.
	 */
	public static function user_has_all( $capabilities, int $user_id ): bool {
		$user = User::get( $user_id );
		if ( ! $user ) {
			return false;
		}

		$check_capabilities = (array) $capabilities;
		foreach ( $check_capabilities as $capability ) {
			if ( ! $user->has_cap( $capability ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get primitive capabilities (non-mapped).
	 *
	 * @return array Array of primitive capabilities.
	 */
	public static function get_primitive(): array {
		global $wp_roles;
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$primitive_caps = [];
		foreach ( $wp_roles->roles as $role ) {
			foreach ( $role['capabilities'] as $cap => $granted ) {
				if ( $granted && ! in_array( $cap, $primitive_caps, true ) ) {
					$primitive_caps[] = $cap;
				}
			}
		}

		return $primitive_caps;
	}

	/**
	 * Compare capabilities between users.
	 *
	 * @param int $user_id_1 First user ID.
	 * @param int $user_id_2 Second user ID.
	 *
	 * @return array Array containing common and different capabilities.
	 */
	public static function compare_users( int $user_id_1, int $user_id_2 ): array {
		$user1_caps = User::get_capabilities( $user_id_1 );
		$user2_caps = User::get_capabilities( $user_id_2 );

		return [
			'common'     => array_intersect( $user1_caps, $user2_caps ),
			'only_user1' => array_diff( $user1_caps, $user2_caps ),
			'only_user2' => array_diff( $user2_caps, $user1_caps ),
		];
	}

	/**
	 * Get capabilities common to multiple users.
	 *
	 * @param array $user_ids Array of user IDs.
	 *
	 * @return array Array of capabilities common to all specified users.
	 */
	public static function get_common( array $user_ids ): array {
		$all_caps = [];
		foreach ( $user_ids as $user_id ) {
			$caps       = User::get_capabilities( $user_id );
			$all_caps[] = $caps;
		}

		return array_reduce( $all_caps, function ( $carry, $item ) {
			return empty( $carry ) ? $item : array_intersect( $carry, $item );
		}, [] );
	}

	/**
	 * Get capabilities options in label/value format.
	 *
	 * @param bool $group_by_type Optional. Whether to group capabilities by common types.
	 *
	 * @return array An array of capability options.
	 */
	public static function get_options( bool $group_by_type = false ): array {
		$capabilities = self::get();

		if ( empty( $capabilities ) ) {
			return [];
		}

		if ( ! $group_by_type ) {
			$options = [];
			foreach ( $capabilities as $cap ) {
				$options[] = [
					'value' => $cap,
					'label' => Capability::format_name( $cap ),
				];
			}

			return $options;
		}

		// Group capabilities by type
		$grouped = [];
		foreach ( $capabilities as $cap ) {
			$type = Capability::get_type( $cap );
			if ( ! isset( $grouped[ $type ] ) ) {
				$grouped[ $type ] = [];
			}
			$grouped[ $type ][] = [
				'value' => $cap,
				'label' => Capability::format_name( $cap ),
			];
		}

		return $grouped;
	}
}