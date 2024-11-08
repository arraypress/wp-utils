<?php
/**
 * Post Type Functions
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

use ArrayPress\Utils\PostTypes\Search;

if ( ! function_exists( 'search_post_types' ) ) {
	/**
	 * Search WordPress post types based on provided arguments.
	 *
	 * @param array $args Arguments for post type search.
	 *
	 * @return array Array of post type objects or formatted search results.
	 */
	function search_post_types( array $args = [] ): array {
		$defaults = [
			'show_internal'      => false,
			'public_only'        => true,
			'show_ui'            => true,
			'publicly_queryable' => null,
			'capability_type'    => null,
			'hierarchical'       => null,
			'supports'           => null,
			's'                  => '', // Search parameter
			'return_objects'     => false, // Whether to return post type objects or formatted results
		];

		$args = wp_parse_args( $args, $defaults );

		// Extract and remove custom parameters
		$search         = $args['s'] ?? '';
		$return_objects = $args['return_objects'];
		$show_internal  = $args['show_internal'];
		$public_only    = $args['public_only'];
		unset( $args['s'], $args['return_objects'], $args['show_internal'], $args['public_only'] );

		// Initialize PostTypes class
		$search_query = new Search(
			$show_internal,
			$public_only,
			$args
		);

		// Always use get_results, even if search is empty
		return $search_query->get_results( $search, $args, $return_objects );
	}
}