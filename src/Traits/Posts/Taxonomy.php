<?php
/**
 * Posts Taxonomy Trait
 *
 * Provides functionality for handling taxonomy and term operations across multiple posts.
 * Includes methods for retrieving, managing, and analyzing taxonomies and terms
 * for collections of posts.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Posts;

use ArrayPress\Utils\Common\Sanitize;
use WP_Post;
use WP_Term;
use WP_Error;

/**
 * Taxonomy Trait
 *
 * Handles taxonomy and term operations for multiple posts including
 * term retrieval, assignment, and relationship management.
 */
trait Taxonomy {

	/**
	 * Get terms for multiple posts.
	 *
	 * @param array  $post_ids       Array of post IDs or post objects.
	 * @param string $taxonomy       Taxonomy name.
	 * @param bool   $return_objects Whether to return term objects instead of term IDs.
	 *
	 * @return int[]|WP_Term[]|WP_Error Array of term IDs/objects, or WP_Error on failure.
	 */
	public static function get_taxonomy_terms( array $post_ids, string $taxonomy, bool $return_objects = false ) {
		if ( ! taxonomy_exists( $taxonomy ) || empty( $post_ids ) ) {
			return [];
		}

		$terms_collection = [];

		foreach ( $post_ids as $post ) {
			if ( is_numeric( $post ) ) {
				$post = get_post( (int) $post );
			}

			if ( empty( $post ) || ! isset( $post->ID ) ) {
				continue;
			}

			$terms = get_the_terms( $post->ID, $taxonomy );
			if ( $terms && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$terms_collection[ $term->term_id ] = $term;
				}
			}
		}

		return $return_objects ? array_values( $terms_collection ) : array_map( 'intval', array_keys( $terms_collection ) );
	}

	/**
	 * Get posts associated with specific terms.
	 *
	 * @param string $taxonomy  The taxonomy name.
	 * @param array  $term_ids  Array of term IDs.
	 * @param string $post_type The post type to query. Default 'any'.
	 * @param array  $args      Optional. Additional WP_Query arguments.
	 *
	 * @return WP_Post[] Array of post objects.
	 */
	public static function get_by_taxonomy_terms( string $taxonomy, array $term_ids, string $post_type = 'any', array $args = [] ): array {
		$default_args = [
			'post_type'   => $post_type,
			'numberposts' => - 1,
			'tax_query'   => [
				[
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $term_ids,
				],
			],
		];

		return get_posts( wp_parse_args( $args, $default_args ) );
	}

	/**
	 * Assign terms to multiple posts.
	 *
	 * @param array  $post_ids Array of post IDs.
	 * @param array  $term_ids Array of term IDs.
	 * @param string $taxonomy The taxonomy name.
	 * @param bool   $append   Optional. Whether to append terms. Default false.
	 *
	 * @return bool True if all assignments were successful, false otherwise.
	 */
	public static function assign_terms( array $post_ids, array $term_ids, string $taxonomy, bool $append = false ): bool {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return false;
		}

		$post_ids = Sanitize::object_ids( $post_ids );
		$term_ids = Sanitize::object_ids( $term_ids );

		if ( empty( $post_ids ) || empty( $term_ids ) ) {
			return false;
		}

		$success = true;

		foreach ( $post_ids as $post_id ) {
			$result = wp_set_object_terms( $post_id, $term_ids, $taxonomy, $append );
			if ( is_wp_error( $result ) ) {
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Remove terms from multiple posts.
	 *
	 * @param array  $post_ids Array of post IDs.
	 * @param array  $term_ids Array of term IDs to remove.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return bool True if all removals were successful, false otherwise.
	 */
	public static function remove_terms( array $post_ids, array $term_ids, string $taxonomy ): bool {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return false;
		}

		$post_ids = Sanitize::object_ids( $post_ids );
		$term_ids = Sanitize::object_ids( $term_ids );

		if ( empty( $post_ids ) || empty( $term_ids ) ) {
			return false;
		}

		$success = true;

		foreach ( $post_ids as $post_id ) {
			$current_terms = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'ids' ] );
			if ( is_wp_error( $current_terms ) ) {
				$success = false;
				continue;
			}

			$new_terms = array_diff( $current_terms, $term_ids );
			$result    = wp_set_object_terms( $post_id, $new_terms, $taxonomy, false );

			if ( is_wp_error( $result ) ) {
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Get common terms across multiple posts.
	 *
	 * @param array  $post_ids       Array of post IDs.
	 * @param string $taxonomy       The taxonomy name.
	 * @param bool   $return_objects Whether to return term objects instead of term IDs.
	 *
	 * @return array Array of term IDs or term objects that are common to all posts.
	 */
	public static function get_common_terms( array $post_ids, string $taxonomy, bool $return_objects = false ): array {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return [];
		}

		$post_ids = Sanitize::object_ids( $post_ids );
		if ( empty( $post_ids ) ) {
			return [];
		}

		$terms_per_post = [];
		foreach ( $post_ids as $post_id ) {
			$terms = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'ids' ] );
			if ( is_wp_error( $terms ) ) {
				return [];
			}
			$terms_per_post[] = $terms;
		}

		if ( empty( $terms_per_post ) ) {
			return [];
		}

		// Find intersection of all term arrays
		$common_terms = array_intersect( ...$terms_per_post );

		if ( ! $return_objects ) {
			return array_values( $common_terms );
		}

		// Convert term IDs to term objects if requested
		$term_objects = [];
		foreach ( $common_terms as $term_id ) {
			$term = get_term( $term_id, $taxonomy );
			if ( $term instanceof WP_Term ) {
				$term_objects[] = $term;
			}
		}

		return $term_objects;
	}

	/**
	 * Replace terms for multiple posts.
	 *
	 * @param array  $post_ids     Array of post IDs.
	 * @param array  $old_term_ids Array of term IDs to replace.
	 * @param array  $new_term_ids Array of new term IDs.
	 * @param string $taxonomy     The taxonomy name.
	 *
	 * @return bool True if all replacements were successful, false otherwise.
	 */
	public static function replace_terms( array $post_ids, array $old_term_ids, array $new_term_ids, string $taxonomy ): bool {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return false;
		}

		$post_ids     = Sanitize::object_ids( $post_ids );
		$old_term_ids = Sanitize::object_ids( $old_term_ids );
		$new_term_ids = Sanitize::object_ids( $new_term_ids );

		if ( empty( $post_ids ) || empty( $old_term_ids ) || empty( $new_term_ids ) ) {
			return false;
		}

		$success = true;

		foreach ( $post_ids as $post_id ) {
			$current_terms = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'ids' ] );
			if ( is_wp_error( $current_terms ) ) {
				$success = false;
				continue;
			}

			// Remove old terms and add new ones
			$updated_terms = array_diff( $current_terms, $old_term_ids );
			$updated_terms = array_merge( $updated_terms, $new_term_ids );

			$result = wp_set_object_terms( $post_id, array_unique( $updated_terms ), $taxonomy, false );
			if ( is_wp_error( $result ) ) {
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Clear all terms from multiple posts in a specific taxonomy.
	 *
	 * @param array  $post_ids Array of post IDs.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return bool True if all terms were cleared successfully, false otherwise.
	 */
	public static function clear_terms( array $post_ids, string $taxonomy ): bool {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return false;
		}

		$post_ids = Sanitize::object_ids( $post_ids );
		if ( empty( $post_ids ) ) {
			return false;
		}

		$success = true;

		foreach ( $post_ids as $post_id ) {
			$result = wp_set_object_terms( $post_id, [], $taxonomy, false );
			if ( is_wp_error( $result ) ) {
				$success = false;
			}
		}

		return $success;
	}

}