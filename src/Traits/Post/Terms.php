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
use WP_Term;
use WP_Error;
use WP_Query;

trait Terms {

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
	 * Get terms for a post.
	 *
	 * Retrieves the terms of the specified taxonomy attached to the given post.
	 *
	 * @param int    $post_id  The ID of the post.
	 * @param string $taxonomy Taxonomy name.
	 * @param bool   $term_ids Whether to return term IDs instead of term objects. Default true.
	 *
	 * @return int[]|WP_Term[]|false|WP_Error Array of term IDs or WP_Term objects on success,
	 *                                        false if no terms or post doesn't exist,
	 *                                        WP_Error on failure.
	 */
	public static function get_terms( int $post_id, string $taxonomy, bool $term_ids = true ) {
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
	 * Get term amounts for a post.
	 *
	 * Retrieves amounts stored in term meta for a post's terms.
	 *
	 * @param int    $post_id  The ID of the post.
	 * @param string $taxonomy The taxonomy to retrieve terms from.
	 * @param string $meta_key The meta key to retrieve amounts from.
	 *
	 * @return array An array of amounts for the terms.
	 */
	public static function get_term_amounts( int $post_id, string $taxonomy, string $meta_key ): array {
		$amounts = [];
		$terms   = get_the_terms( $post_id, $taxonomy );

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
	 * Get highest/lowest term amount.
	 *
	 * Retrieves the highest or lowest amount from term meta for a post's terms.
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
	 * Get term meta values with processing.
	 *
	 * Retrieves and optionally processes term meta values for a post's terms.
	 *
	 * @param int                  $post_id  The ID of the post.
	 * @param string               $taxonomy The taxonomy to retrieve terms from.
	 * @param string               $meta_key The meta key to retrieve.
	 * @param callable|string|null $callback Optional. Processing callback. Default 'floatval'.
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
	 * Get shared term count across posts.
	 *
	 * Calculates the maximum number of times any term appears across multiple posts.
	 *
	 * @param array  $post_ids Array of post IDs to check.
	 * @param string $taxonomy The taxonomy to check terms against.
	 *
	 * @return int The highest count of shared terms, or 0 if no matches.
	 */
	public static function get_shared_term_count( array $post_ids, string $taxonomy ): int {
		if ( empty( $post_ids ) || ! taxonomy_exists( $taxonomy ) ) {
			return 0;
		}

		$term_ids = [];
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

		if ( empty( $term_ids ) ) {
			return 0;
		}

		$term_counts = array_count_values( $term_ids );
		$max_count   = max( $term_counts );

		return $max_count > 1 ? $max_count : 0;
	}

}