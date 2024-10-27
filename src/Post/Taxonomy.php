<?php
/**
 * Post Utilities for WordPress
 *
 * This class provides utility functions for working with WordPress posts, including
 * methods for checking post existence, retrieving post data, working with post types,
 * checking for shortcodes and blocks, handling post metadata, and managing post content.
 * It also offers functions to work with taxonomy terms, post thumbnails, and scheduling posts.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Post;

use WP_Term;
use WP_Error;
use WP_Query;

/**
 * Check if the class `Taxonomy` is defined, and if not, define it.
 */
if ( ! class_exists( 'Taxonomy' ) ) :

	/**
	 * Post Utilities
	 *
	 * Provides utility functions for managing WordPress posts, such as checking post existence,
	 * retrieving post data by various identifiers, working with post meta, taxonomy terms, and
	 * handling post content. It also supports managing post thumbnails, scheduling posts, and
	 * extracting content-related information like links and word count.
	 */
	class Taxonomy {

		/**
		 * Check if a post is excluded based on its terms' meta.
		 *
		 * @param int    $post_id            The ID of the post to check.
		 * @param string $taxonomy           The taxonomy to check.
		 * @param string $term_exclusion_key The term meta key that indicates exclusion.
		 * @param string $meta_value         Optional. The meta value to check for. Default 'on'.
		 *
		 * @return bool True if any of the post's terms are excluded, false otherwise.
		 */
		public static function is_excluded_by_terms( int $post_id, string $taxonomy, string $term_exclusion_key, string $meta_value = 'on' ): bool {
			$terms = wp_get_post_terms( $post_id, $taxonomy, [
				'meta_key'   => $term_exclusion_key,
				'meta_value' => $meta_value,
				'fields'     => 'ids'
			] );

			return ! is_wp_error( $terms ) && ! empty( $terms );
		}

		/**
		 * Retrieve the amounts for terms in a specific taxonomy for a post.
		 *
		 * @param int    $post_id  The ID of the post.
		 * @param string $taxonomy The taxonomy to retrieve terms from.
		 * @param string $meta_key The meta key to retrieve amounts from.
		 *
		 * @return array An array of amounts for the terms.
		 */
		public static function get_term_amounts( int $post_id, string $taxonomy, string $meta_key ): array {
			$amounts = [];

			$terms = get_the_terms( $post_id, $taxonomy );

			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				return $amounts;
			}

			foreach ( $terms as $term ) {
				$amount = get_term_meta( $term->term_id, $meta_key, true );

				if ( ! empty( $amount ) ) {
					$amounts[ $term->term_id ] = floatval( $amount );
				}
			}

			return $amounts;
		}

		/**
		 * Retrieve the highest or lowest amount for terms in a specific taxonomy for a post.
		 *
		 * @param int    $post_id     The ID of the post.
		 * @param string $taxonomy    The taxonomy to retrieve terms from.
		 * @param string $meta_key    The meta key to retrieve amounts from.
		 * @param bool   $use_highest Whether to use the highest amount. Default true.
		 *
		 * @return float|null The highest or lowest amount, or null if no amounts found.
		 */
		public static function get_term_amount( int $post_id, string $taxonomy, string $meta_key, bool $use_highest = true ): ?float {
			$amounts = self::get_term_amounts( $post_id, $taxonomy, $meta_key );

			if ( empty( $amounts ) ) {
				return null;
			}

			$amounts = array_values( $amounts );

			return $use_highest ? max( $amounts ) : min( $amounts );
		}

		/**
		 * Retrieve the term meta values for a specific taxonomy and post, and process them.
		 *
		 * @param int                  $post_id  The ID of the post.
		 * @param string               $taxonomy The taxonomy to retrieve terms from.
		 * @param string               $meta_key The meta key to retrieve from the terms.
		 * @param callable|string|null $callback Optional. A callback function or function name to process the meta value. Default is 'floatval'.
		 *
		 * @return array The processed term meta values.
		 */
		public static function get_term_meta_values( int $post_id, string $taxonomy, string $meta_key, $callback = 'floatval' ): array {
			$terms = get_the_terms( $post_id, $taxonomy );

			if ( ! $terms || is_wp_error( $terms ) ) {
				return [];
			}

			$values = [];

			foreach ( $terms as $term ) {
				$meta_value = get_term_meta( $term->term_id, $meta_key, true );

				if ( $meta_value !== '' ) {
					if ( is_callable( $callback ) ) {
						$values[] = $callback( $meta_value );
					} elseif ( is_string( $callback ) && function_exists( $callback ) ) {
						$values[] = $callback( $meta_value );
					} else {
						$values[] = $meta_value;
					}
				}
			}

			return array_values( $values );
		}

		/**
		 * Retrieve a single processed term meta value for a specific taxonomy and post.
		 *
		 * @param int                  $post_id     The ID of the post.
		 * @param string               $taxonomy    The taxonomy to retrieve terms from.
		 * @param string               $meta_key    The meta key to retrieve from the terms.
		 * @param callable|string|null $callback    Optional. A callback function or function name to process the meta value. Default is 'floatval'.
		 * @param bool                 $use_highest Optional. Whether to use the highest or lowest value. Default is true (highest).
		 *
		 * @return float The single processed term meta value.
		 */
		public static function get_single_term_meta_value( int $post_id, string $taxonomy, string $meta_key, $callback = 'floatval', bool $use_highest = true ): float {
			$values = self::get_term_meta_values( $post_id, $taxonomy, $meta_key, $callback );

			if ( empty( $values ) ) {
				return 0.0;
			}

			return $use_highest ? max( $values ) : min( $values );
		}

		/**
		 * Retrieves the terms of the specified taxonomy attached to the given post.
		 *
		 * @param int    $post_id  The ID of the post.
		 * @param string $taxonomy Taxonomy name.
		 * @param bool   $term_ids Whether to return term IDs instead of term objects. Default is true.
		 *
		 * @return int[]|WP_Term[]|false|WP_Error Array of term IDs or WP_Term objects on success,
		 *                                        false if there are no terms or the post does not exist,
		 *                                        WP_Error on failure.
		 */
		public static function get_terms( int $post_id, string $taxonomy, bool $term_ids = true ): ?array {
			if ( empty( $post_id ) || ! taxonomy_exists( $taxonomy ) ) {
				return false;
			}

			$post = get_post( $post_id );

			if ( empty( $post ) || ! isset( $post->ID ) ) {
				return false;
			}

			$terms = get_the_terms( $post->ID, $taxonomy );

			if ( $terms && ! is_wp_error( $terms ) ) {
				return $term_ids ? wp_list_pluck( $terms, 'term_id' ) : $terms;
			}

			return false;
		}

		/**
		 * Get related posts based on shared terms in specified taxonomies.
		 *
		 * @param int   $post_id    The post ID.
		 * @param array $args       Optional. An array of arguments. Default is an empty array.
		 * @param array $taxonomies Optional. An array of taxonomy names to consider. Default is ['post_tag', 'category'].
		 *
		 * @return array An array of related post objects.
		 */
		public static function get_related_posts(
			int $post_id, array $args = [], array $taxonomies = [
			'post_tag',
			'category'
		]
		): array {
			$default_args = [
				'posts_per_page'      => 5,
				'post__not_in'        => [ $post_id ],
				'ignore_sticky_posts' => 1,
				'orderby'             => 'relevance',
				'post_type'           => get_post_type( $post_id ),
			];

			$args = wp_parse_args( $args, $default_args );

			// Get all terms for the current post across specified taxonomies
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

			// Perform the query
			$related_query = new WP_Query( $args );

			return $related_query->posts;
		}

		/**
		 * Get the highest count of shared terms across multiple posts.
		 *
		 * This method finds the maximum number of times any term appears across the provided posts
		 * in the specified taxonomy. A count of 1 is considered as no match and returns 0.
		 *
		 * @param array  $post_ids Array of post IDs to check.
		 * @param string $taxonomy The taxonomy to check terms against.
		 *
		 * @return int The highest count of shared terms, or 0 if no matches or invalid input.
		 */
		public static function get_shared_term_count( array $post_ids, string $taxonomy ): int {
			if ( empty( $post_ids ) || ! taxonomy_exists( $taxonomy ) ) {
				return 0;
			}

			$term_ids = [];

			// Collect all term IDs for each post
			foreach ( $post_ids as $post_id ) {
				$post = get_post( $post_id );
				if ( empty( $post ) || ! isset( $post->ID ) ) {
					continue;
				}

				$terms = get_the_terms( $post->ID, $taxonomy );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$term_ids = array_merge(
						$term_ids,
						wp_list_pluck( $terms, 'term_id' )
					);
				}
			}

			// If no terms found, return 0
			if ( empty( $term_ids ) ) {
				return 0;
			}

			// Count occurrences of each term
			$term_counts = array_count_values( $term_ids );

			// Get the highest count
			$max_count = max( $term_counts );

			// Return 0 if highest count is 1 (no actual sharing)
			return $max_count > 1 ? $max_count : 0;
		}

	}
endif;