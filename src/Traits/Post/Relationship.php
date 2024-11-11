<?php
/**
 * Post Terms Trait
 *
 * Provides functionality for working with post taxonomies, terms,
 * and term metadata, including related posts and term relationships.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Post;

use WP_Post;
use WP_Query;

trait Relationship {

	/**
	 * Get related posts based on shared terms.
	 *
	 * Retrieves posts that share taxonomy terms with the specified post.
	 *
	 * @param int   $post_id    The post ID.
	 * @param array $args       Optional. Query arguments.
	 * @param array $taxonomies Optional. Taxonomies to consider. Default ['post_tag', 'category'].
	 *
	 * @return WP_Post[] Array of related post objects.
	 */
	public static function get_related_posts(
		int $post_id,
		array $args = [],
		array $taxonomies = [ 'post_tag', 'category' ]
	): array {
		$default_args = [
			'posts_per_page'      => 5,
			'post__not_in'        => [ $post_id ],
			'ignore_sticky_posts' => 1,
			'orderby'             => 'relevance',
			'post_type'           => get_post_type( $post_id ),
		];

		$args = wp_parse_args( $args, $default_args );

		// Get all terms for the current post
		$terms = [];
		foreach ( $taxonomies as $taxonomy ) {
			$post_terms = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'ids' ] );
			if ( ! is_wp_error( $post_terms ) && ! empty( $post_terms ) ) {
				$terms = array_merge( $terms, $post_terms );
			}
		}

		if ( empty( $terms ) ) {
			return [];
		}

		// Prepare tax query
		$tax_query = [];
		foreach ( $taxonomies as $taxonomy ) {
			$tax_query[] = [
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => $terms,
			];
		}

		if ( count( $taxonomies ) > 1 ) {
			$tax_query['relation'] = 'OR';
		}

		$args['tax_query'] = $tax_query;
		$related_query     = new WP_Query( $args );

		return $related_query->posts;
	}

}