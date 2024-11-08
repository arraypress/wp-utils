<?php
/**
 * Term Functions
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

use ArrayPress\Utils\Terms\Search;

if ( ! function_exists( 'search_terms' ) ) {
	/**
	 * Search WordPress terms based on provided arguments.
	 *
	 * @param array $args Arguments for term query.
	 *
	 * @return array Array of term objects or formatted search results.
	 */
	function search_terms( array $args = [] ): array {
		$defaults = [
			'taxonomies'     => [ 'category' ],
			'hide_empty'     => false,
			'number'         => 0,
			'orderby'        => 'name',
			'order'          => 'ASC',
			's'              => '', // Search parameter
			'meta_query'     => [],
			'return_objects' => false, // Whether to return term objects or formatted results
		];

		$args = wp_parse_args( $args, $defaults );

		// Extract and remove custom parameters
		$search         = $args['s'] ?? '';
		$return_objects = $args['return_objects'];
		$meta_query     = $args['meta_query'];
		unset( $args['s'], $args['return_objects'], $args['meta_query'] );

		// Initialize Terms class
		$search_query = new Search(
			$args['taxonomies'],
			$args['hide_empty'],
			$args['number'],
			$args['orderby'],
			$args['order']
		);

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