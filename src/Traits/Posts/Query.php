<?php
/**
 * Posts Query Trait
 *
 * Provides fundamental functionality for retrieving and querying multiple WordPress posts.
 * Includes methods for fetching posts by various criteria like IDs, titles, authors,
 * categories, dates, and other common parameters.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Posts;

use WP_Post;

/**
 * Core Trait
 *
 * Handles basic post retrieval and query operations for multiple posts.
 */
trait Query {

	/**
	 * Get posts by author.
	 *
	 * @param int    $author_id The author ID to search for.
	 * @param string $post_type The post type to query. Default 'post'.
	 * @param array  $args      Additional query arguments.
	 *
	 * @return WP_Post[] Array of post objects.
	 */
	public static function get_by_author( int $author_id, string $post_type = 'post', array $args = [] ): array {
		return get_posts( wp_parse_args( $args, [
			'author'      => $author_id,
			'post_type'   => $post_type,
			'numberposts' => - 1,
		] ) );
	}

	/**
	 * Get posts by category.
	 *
	 * @param int    $category_id The category ID to search for.
	 * @param string $post_type   The post type to query. Default 'post'.
	 * @param array  $args        Additional query arguments.
	 *
	 * @return WP_Post[] Array of post objects.
	 */
	public static function get_by_category( int $category_id, string $post_type = 'post', array $args = [] ): array {
		return get_posts( wp_parse_args( $args, [
			'category'    => $category_id,
			'post_type'   => $post_type,
			'numberposts' => - 1,
		] ) );
	}

	/**
	 * Get posts by tag.
	 *
	 * @param int    $tag_id    The tag ID to search for.
	 * @param string $post_type The post type to query. Default 'post'.
	 * @param array  $args      Additional query arguments.
	 *
	 * @return WP_Post[] Array of post objects.
	 */
	public static function get_by_tag( int $tag_id, string $post_type = 'post', array $args = [] ): array {
		return get_posts( wp_parse_args( $args, [
			'tag_id'      => $tag_id,
			'post_type'   => $post_type,
			'numberposts' => - 1,
		] ) );
	}

	/**
	 * Get posts by terms.
	 *
	 * Retrieves posts that have any or all of the provided terms across specified taxonomies.
	 *
	 * @param array        $terms    Array of term IDs or slugs.
	 * @param string|array $taxonomy Single taxonomy name or array of taxonomy names.
	 * @param array        $post_ids Optional. Array of post IDs to limit the query to.
	 * @param string       $operator Optional. The logical operation to perform when using multiple terms.
	 *                               'IN' means posts must have at least one term, 'AND' means posts must have all
	 *                               terms. Default 'IN'.
	 * @param array        $args     Optional. Additional query arguments.
	 *
	 * @return WP_Post[] Array of post objects.
	 */
	public static function get_by_terms( array $terms, $taxonomy, array $post_ids = [], string $operator = 'IN', array $args = [] ): array {
		$tax_query = [
			[
				'taxonomy' => $taxonomy,
				'field'    => is_numeric( $terms[0] ) ? 'term_id' : 'slug',
				'terms'    => $terms,
				'operator' => $operator,
			]
		];

		// If taxonomy is an array, create a tax_query for each taxonomy
		if ( is_array( $taxonomy ) ) {
			$tax_query = array_map( function ( $tax ) use ( $terms, $operator ) {
				return [
					'taxonomy' => $tax,
					'field'    => is_numeric( $terms[0] ) ? 'term_id' : 'slug',
					'terms'    => $terms,
					'operator' => $operator,
				];
			}, $taxonomy );

			// Add relation if multiple taxonomies
			if ( count( $taxonomy ) > 1 ) {
				$tax_query['relation'] = 'AND';
			}
		}

		$query_args = [
			'tax_query'   => $tax_query,
			'numberposts' => - 1,
			'post_type'   => 'any',
			'post_status' => 'publish',
		];

		// Add post IDs limitation if provided
		if ( ! empty( $post_ids ) ) {
			$query_args['post__in'] = $post_ids;
		}

		// Merge with additional arguments
		$query_args = wp_parse_args( $args, $query_args );

		return get_posts( $query_args );
	}

	/**
	 * Get posts by date range.
	 *
	 * @param string $start_date The start date (YYYY-MM-DD).
	 * @param string $end_date   The end date (YYYY-MM-DD).
	 * @param string $post_type  The post type to query. Default 'post'.
	 * @param array  $args       Additional query arguments.
	 *
	 * @return WP_Post[] Array of post objects.
	 */
	public static function get_by_date_range( string $start_date, string $end_date, string $post_type = 'post', array $args = [] ): array {
		return get_posts( wp_parse_args( $args, [
			'post_type'   => $post_type,
			'date_query'  => [
				[
					'after'     => $start_date,
					'before'    => $end_date,
					'inclusive' => true,
				],
			],
			'numberposts' => - 1,
		] ) );
	}

	/**
	 * Get recent posts.
	 *
	 * @param int    $number    The number of posts to retrieve.
	 * @param string $post_type The post type to query. Default 'post'.
	 * @param array  $args      Additional query arguments.
	 *
	 * @return WP_Post[] Array of post objects.
	 */
	public static function get_recent( int $number = 5, string $post_type = 'post', array $args = [] ): array {
		return get_posts( wp_parse_args( $args, [
			'numberposts' => $number,
			'post_type'   => $post_type,
			'orderby'     => 'date',
			'order'       => 'DESC',
		] ) );
	}

	/**
	 * Get posts with no thumbnail.
	 *
	 * @param string $post_type The post type to query. Default 'post'.
	 * @param array  $args      Additional query arguments.
	 *
	 * @return WP_Post[] Array of post objects.
	 */
	public static function get_without_thumbnail( string $post_type = 'post', array $args = [] ): array {
		return get_posts( wp_parse_args( $args, [
			'post_type'   => $post_type,
			'numberposts' => - 1,
			'meta_query'  => [
				[
					'key'     => '_thumbnail_id',
					'compare' => 'NOT EXISTS',
				],
			],
		] ) );
	}

	/**
	 * Get sticky posts.
	 *
	 * @param string $post_type The post type to query. Default 'post'.
	 * @param array  $args      Additional query arguments.
	 *
	 * @return WP_Post[] Array of post objects.
	 */
	public static function get_sticky_posts( string $post_type = 'post', array $args = [] ): array {
		return get_posts( wp_parse_args( $args, [
			'post_type'   => $post_type,
			'numberposts' => - 1,
			'post__in'    => get_option( 'sticky_posts' ),
		] ) );
	}

	/**
	 * Get upcoming scheduled posts.
	 *
	 * @param array $args Additional arguments for get_posts().
	 *
	 * @return WP_Post[] Array of upcoming scheduled posts.
	 */
	public static function get_upcoming_scheduled( array $args = [] ): array {
		return get_posts( wp_parse_args( $args, [
			'post_status' => 'future',
			'orderby'     => 'date',
			'order'       => 'ASC',
			'numberposts' => - 1,
		] ) );
	}

}