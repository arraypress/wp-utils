<?php

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Posts;

use ArrayPress\Utils\Common\Sanitize as CommonSanitize;

trait Sanitize {
	use Core;

	/**
	 * Sanitize and validate a list of post IDs.
	 *
	 * Ensures all posts exist and returns an array of unique, valid post IDs.
	 * Returns an empty array if no valid posts are found.
	 *
	 * @param array        $post_ids  The input array of post IDs to be sanitized.
	 * @param string|array $post_type Optional. The post type(s) to validate against. Default 'any'.
	 *
	 * @return array<int, int> Array of sanitized and validated post IDs.
	 */
	public static function sanitize( array $post_ids, $post_type = 'any' ): array {
		$post_ids = CommonSanitize::object_ids( $post_ids );
		if ( empty( $post_ids ) ) {
			return [];
		}

		// Use exists method from Core trait to validate posts
		return self::exists( $post_ids, $post_type );
	}

	/**
	 * Sanitize and validate a list of post identifiers (IDs, slugs, or post objects).
	 *
	 * This method ensures all posts exist and returns an array of unique, valid post IDs
	 * or post objects. Returns an empty array if no valid posts are found.
	 *
	 * @param array|mixed  $identifiers    The input array of post identifiers (IDs, slugs, or post objects).
	 * @param string|array $post_type      Optional. The post type(s) to validate against. Default 'any'.
	 * @param bool         $return_objects Optional. Whether to return post objects instead of IDs. Default false.
	 *
	 * @return array<int|object> Array of sanitized post IDs or post objects.
	 */
	public static function sanitize_by_identifiers( $identifiers, $post_type = 'any', bool $return_objects = false ): array {
		// Convert to array if not already
		$identifiers = is_array( $identifiers ) ? $identifiers : [ $identifiers ];

		// Remove empty values and sanitize strings
		$identifiers = array_filter( $identifiers, function ( $identifier ) {
			return ! empty( $identifier ) || $identifier === '0';
		} );

		$identifiers = array_map( function ( $identifier ) {
			if ( is_string( $identifier ) ) {
				return sanitize_text_field( $identifier );
			}

			return $identifier;
		}, $identifiers );

		if ( empty( $identifiers ) ) {
			return [];
		}

		// Use get_by_identifiers from Core trait to validate and retrieve posts
		return self::get_by_identifiers( $identifiers, $post_type, $return_objects );
	}

}