<?php
/**
 * Users Search Utility Class for WordPress
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.1
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

use ArrayPress\Utils\Search\Users;
use ArrayPress\Utils\Search\Posts;
use ArrayPress\Utils\Search\Terms;

use function wp_parse_args;

if ( ! function_exists( 'search_users' ) ) {
	/**
	 * Search WordPress users based on provided arguments.
	 *
	 * @param array $args Arguments for user query.
	 *
	 * @return array Array of user objects or formatted search results.
	 */
	function search_users( array $args = [] ): array {
		// Default arguments
		$defaults = [
			'roles'          => [],
			'capabilities'   => [],
			'number'         => - 1,
			'orderby'        => 'display_name',
			'order'          => 'ASC',
			'search_columns' => [ 'user_login', 'user_nicename', 'user_email', 'display_name' ],
			's'              => '', // Search parameter
			'meta_query'     => [],
			'return_objects' => false, // Whether to return user objects or formatted results
		];

		$args = wp_parse_args( $args, $defaults );

		// Extract and remove custom parameters
		$search         = $args['s'] ?? '';
		$return_objects = $args['return_objects'];
		$meta_query     = $args['meta_query'];
		unset( $args['s'], $args['return_objects'], $args['meta_query'] );

		// Initialize Users class
		$search_query = new Users(
			$args['roles'],
			$args['number'],
			$args['orderby'],
			$args['order']
		);

		// Set capabilities if provided
		if ( ! empty( $args['capabilities'] ) ) {
			$search_query->set_capabilities( $args['capabilities'] );
		}

		// Set search columns
		$search_query->set_search_columns( $args['search_columns'] );

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

if ( ! function_exists( 'search_posts' ) ) {
	/**
	 * Search WordPress posts based on provided arguments.
	 *
	 * @param array $args Arguments for post query.
	 *
	 * @return array Array of post objects or formatted search results.
	 */
	function search_posts( array $args = [] ): array {
		// Default arguments
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
		$search_query = new Posts(
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
		$search_query = new Terms(
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