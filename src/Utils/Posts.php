<?php
/**
 * Post Functions
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

use ArrayPress\Utils\Posts\Search;

if ( ! function_exists( 'search_posts' ) ) {
	/**
	 * Search WordPress posts based on provided arguments.
	 *
	 * @param array $args Arguments for post query.
	 *
	 * @return array Array of post objects or formatted search results.
	 */
	function search_posts( array $args = [] ): array {
		$defaults = [
			'post_types'     => [ 'post' ],
			'post_status'    => [ 'publish' ],
			'number'         => 30,
			'orderby'        => 'title',
			'order'          => 'ASC',
			's'              => '', // Search parameter
			'tax_query'      => [],
			'meta_query'     => [],
			'return_objects' => false, // Whether to return post objects or formatted results
		];

		$args = wp_parse_args( $args, $defaults );

		// Extract and remove custom parameters
		$search         = $args['s'] ?? '';
		$return_objects = $args['return_objects'];
		$tax_query      = $args['tax_query'];
		$meta_query     = $args['meta_query'];
		unset( $args['s'], $args['return_objects'], $args['tax_query'], $args['meta_query'] );

		// Initialize Posts class
		$search_query = new Search(
			$args['post_types'],
			$args['post_status'],
			$args['number'],
			$args['orderby'],
			$args['order']
		);

		// Add taxonomy queries if provided
		if ( ! empty( $tax_query ) ) {
			foreach ( $tax_query as $query ) {
				$search_query->add_tax_query(
					$query['taxonomy'],
					$query['terms'],
					$query['field'] ?? 'slug',
					$query['operator'] ?? 'IN'
				);
			}
		}

		// Add meta queries if provided
		if ( ! empty( $meta_query ) ) {
			foreach ( $meta_query as $query ) {
				$search_query->add_meta_query(
					$query['key'],
					$query['value'] ?? '',
					$query['compare'] ?? '=',
					$query['type'] ?? 'CHAR'
				);
			}
		}

		// Always use get_results, even if search is empty
		return $search_query->get_results( $search, $args, $return_objects );
	}
}