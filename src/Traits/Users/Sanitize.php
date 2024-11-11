<?php

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Users;

use ArrayPress\Utils\Common\Sanitize as CommonSanitize;
use WP_User;

/**
 * Users Sanitize Trait
 */
trait Sanitize {
	use Core;

	/**
	 * Sanitize and validate a list of user IDs.
	 *
	 * Ensures all users exist and returns an array of unique, valid user IDs.
	 * Returns an empty array if no valid users are found.
	 *
	 * @param array|mixed $user_ids The input array of user IDs to be sanitized.
	 *
	 * @return array<int, int> Array of sanitized and validated user IDs.
	 */
	public static function sanitize( $user_ids ): array {
		// Convert to array and ensure we have values
		$user_ids = CommonSanitize::object_ids( $user_ids );
		if ( empty( $user_ids ) ) {
			return [];
		}

		// Filter for valid users only
		$valid_users = array_filter( $user_ids, function ( $user_id ) {
			return get_userdata( (int) $user_id ) instanceof WP_User;
		} );

		// Return unique values as integers
		return array_unique( array_map( 'intval', $valid_users ) );
	}

	/**
	 * Sanitize and validate a list of user identifiers (IDs, emails, usernames, or user objects).
	 *
	 * This method ensures all users exist and returns an array of unique, valid user IDs
	 * or user objects. Returns an empty array if no valid users are found.
	 *
	 * @param array|mixed $identifiers    The input array of user identifiers (IDs, emails, usernames, or user objects).
	 * @param bool        $return_objects Optional. Whether to return user objects instead of IDs. Default false.
	 *
	 * @return array<int|WP_User> Array of sanitized user IDs or user objects.
	 */
	public static function sanitize_by_identifiers( $identifiers, bool $return_objects = false ): array {
		// Convert to array if not already
		$identifiers = is_array( $identifiers ) ? $identifiers : [ $identifiers ];

		// Remove empty values and sanitize strings
		$identifiers = array_filter( $identifiers, function ( $identifier ) {
			return ! empty( $identifier ) || $identifier === '0';
		} );

		$identifiers = array_map( function ( $identifier ) {
			if ( is_string( $identifier ) ) {
				// Sanitize emails
				if ( is_email( $identifier ) ) {
					return sanitize_email( $identifier );
				}

				// Sanitize usernames and other strings
				return sanitize_user( $identifier, true );
			}

			return $identifier;
		}, $identifiers );

		if ( empty( $identifiers ) ) {
			return [];
		}

		// Use get_by_identifiers to validate and retrieve users
		return self::get_by_identifiers( $identifiers, $return_objects );
	}

}