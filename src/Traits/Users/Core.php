<?php
/**
 * Users Core Class
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
use WP_User;

/**
 * Users Core Trait
 */
trait Core {

	/**
	 * Get an array of existing user IDs from a provided array of user IDs.
	 *
	 * @param array $user_ids An array of user IDs to check.
	 *
	 * @return array An array of existing user IDs.
	 */
	public static function exists( array $user_ids ): array {
		$user_ids = Sanitize::object_ids( $user_ids );

		if ( empty( $user_ids ) ) {
			return [];
		}

		return array_filter( $user_ids, function ( $user_id ) {
			return get_userdata( $user_id ) !== false;
		} );
	}

	/**
	 * Get an array of user objects based on provided user IDs.
	 *
	 * @param array $user_ids An array of user IDs.
	 *
	 * @return WP_User[] An array of user objects.
	 */
	public static function get( array $user_ids ): array {
		$user_ids = Sanitize::object_ids( $user_ids );

		if ( empty( $user_ids ) ) {
			return [];
		}

		return get_users( [
			'include' => $user_ids,
			'fields'  => 'all'
		] );
	}

	/**
	 * Get an array of unique user IDs or user objects based on provided identifiers.
	 *
	 * @param array $user_identifiers An array of usernames, emails, IDs, or user objects.
	 * @param bool  $return_objects   Whether to return user objects instead of user IDs.
	 *
	 * @return array An array of unique user IDs or user objects.
	 */
	public static function get_by_identifiers( array $user_identifiers, bool $return_objects = false ): array {
		if ( empty( $user_identifiers ) ) {
			return [];
		}

		$unique_users = [];

		foreach ( $user_identifiers as $identifier ) {
			if ( empty( $identifier ) ) {
				continue;
			}

			// Handle user object
			if ( is_object( $identifier ) && isset( $identifier->ID ) ) {
				$user = get_user_by( 'id', $identifier->ID );
			} // Handle numeric ID
			elseif ( is_numeric( $identifier ) ) {
				$user = get_user_by( 'id', $identifier );
			} // Handle email
			elseif ( is_email( $identifier ) ) {
				$user = get_user_by( 'email', $identifier );
			} // Handle login/username
			else {
				$user = get_user_by( 'login', $identifier );
				if ( ! $user ) {
					// Try by slug (nicename) if login fails
					$user = get_user_by( 'slug', $identifier );
				}
			}

			if ( $user instanceof WP_User ) {
				$unique_users[ $user->ID ] = $user;
			}
		}

		return $return_objects ? array_values( $unique_users ) : array_map( 'intval', array_keys( $unique_users ) );
	}

}