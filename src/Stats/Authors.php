<?php
/**
 * Users Utilities
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Stats;

/**
 * Class Authors
 *
 * Utility functions for working with multiple users.
 */
class Authors {

	/**
	 * Get top authors for a specific post type.
	 *
	 * @param string $post_type Post type to get authors for
	 * @param array  $roles     Roles to filter authors by
	 * @param int    $limit     Maximum number of authors to return
	 *
	 * @return array Array of author objects with post counts
	 */
	public static function get_top( string $post_type, array $roles = [], int $limit = 5 ): array {
		$authors = get_users( [
			'role__in'            => $roles,
			'orderby'             => 'post_count',
			'order'               => 'DESC',
			'number'              => $limit,
			'fields'              => [ 'ID', 'display_name' ],
			'has_published_posts' => [ $post_type ],
		] );

		return array_map( function ( $author ) use ( $post_type ) {
			return (object) [
				'name'       => $author->display_name,
				'post_count' => count_user_posts( $author->ID, [ $post_type ], true )
			];
		}, $authors );
	}

}