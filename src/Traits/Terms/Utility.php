<?php
/**
 * Trait: Terms Utility
 *
 * This trait provides utility methods for performing bulk operations on WordPress terms
 * and other miscellaneous term-related functionality.
 *
 * @package     ArrayPress\Utils\Traits\Terms
 * @since       1.0.0
 * @author      David Sherlock
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Terms;

use WP_Error;
use WP_Term;

trait Utility {

	/**
	 * Bulk delete terms.
	 *
	 * Deletes multiple terms at once and tracks the success/failure of each deletion.
	 *
	 * @param array  $term_ids An array of term IDs to delete.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return array An array of results, with term IDs as keys and deletion results as values.
	 */
	public static function delete( array $term_ids, string $taxonomy ): array {
		$results = [];

		foreach ( $term_ids as $term_id ) {
			$results[ $term_id ] = wp_delete_term( $term_id, $taxonomy );
		}

		return $results;
	}

	/**
	 * Bulk merge terms.
	 *
	 * Merges multiple terms into a single target term, reassigning all associations.
	 *
	 * @param array  $source_term_ids Array of term IDs to merge from.
	 * @param int    $target_term_id  The ID of the term to merge into.
	 * @param string $taxonomy        The taxonomy name.
	 *
	 * @return array Array of results with source term IDs as keys.
	 */
	protected static function bulk_merge( array $source_term_ids, int $target_term_id, string $taxonomy ): array {
		$results = [];

		// Verify target term exists
		$target_term = get_term( $target_term_id, $taxonomy );
		if ( is_wp_error( $target_term ) || ! $target_term instanceof WP_Term ) {
			return array_fill_keys( $source_term_ids, new WP_Error( 'invalid_target', 'Invalid target term' ) );
		}

		foreach ( $source_term_ids as $source_term_id ) {
			// Skip if source and target are the same
			if ( $source_term_id === $target_term_id ) {
				$results[ $source_term_id ] = new WP_Error( 'same_term', 'Source and target terms are the same' );
				continue;
			}

			// Get all posts using the source term
			$posts = get_posts( [
				'numberposts' => - 1,
				'tax_query'   => [
					[
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $source_term_id,
					],
				],
			] );

			// Reassign posts to target term
			foreach ( $posts as $post ) {
				wp_remove_object_terms( $post->ID, $source_term_id, $taxonomy );
				wp_add_object_terms( $post->ID, $target_term_id, $taxonomy );
			}

			// Delete the source term
			$results[ $source_term_id ] = wp_delete_term( $source_term_id, $taxonomy );
		}

		return $results;
	}

	/**
	 * Bulk reorder terms.
	 *
	 * Updates the term order for multiple terms at once.
	 *
	 * @param array  $term_order Array of term IDs in desired order.
	 * @param string $taxonomy   The taxonomy name.
	 *
	 * @return bool True on success, false on failure.
	 */
	protected static function bulk_reorder( array $term_order, string $taxonomy ): bool {
		global $wpdb;

		$success = true;

		foreach ( $term_order as $position => $term_id ) {
			$result = $wpdb->update(
				$wpdb->terms,
				[ 'term_order' => $position ],
				[ 'term_id' => $term_id ]
			);

			if ( false === $result ) {
				$success = false;
			}
		}

		if ( $success ) {
			clean_term_cache( $term_order, $taxonomy );
		}

		return $success;
	}

	/**
	 * Sanitize and validate term data.
	 *
	 * Ensures term data meets requirements before operations.
	 *
	 * @param array  $term_data Array of term data (name, slug, description, etc.).
	 * @param string $taxonomy  The taxonomy name.
	 *
	 * @return array|WP_Error Sanitized term data or WP_Error on failure.
	 */
	protected static function sanitize_term_data( array $term_data, string $taxonomy ) {
		if ( empty( $term_data['name'] ) ) {
			return new WP_Error( 'empty_term_name', 'Term name cannot be empty' );
		}

		$sanitized = [
			'name'        => sanitize_term_field( 'name', $term_data['name'], 0, $taxonomy, 'db' ),
			'slug'        => ! empty( $term_data['slug'] ) ? sanitize_title( $term_data['slug'] ) : '',
			'description' => ! empty( $term_data['description'] )
				? sanitize_term_field( 'description', $term_data['description'], 0, $taxonomy, 'db' )
				: '',
			'parent'      => ! empty( $term_data['parent'] ) ? absint( $term_data['parent'] ) : 0,
		];

		// Validate parent term if specified
		if ( $sanitized['parent'] > 0 ) {
			$parent_term = get_term( $sanitized['parent'], $taxonomy );
			if ( ! $parent_term || is_wp_error( $parent_term ) ) {
				return new WP_Error( 'invalid_parent', 'Invalid parent term specified' );
			}
		}

		return $sanitized;
	}

	/**
	 * Clean term cache for multiple terms.
	 *
	 * Efficiently cleans cache for a set of terms.
	 *
	 * @param array  $term_ids Array of term IDs.
	 * @param string $taxonomy The taxonomy name.
	 */
	protected static function clean_terms_cache( array $term_ids, string $taxonomy ): void {
		clean_term_cache( $term_ids, $taxonomy );
		wp_cache_delete( 'all_ids', $taxonomy );
		wp_cache_delete( 'get', $taxonomy );
		delete_option( "{$taxonomy}_children" );
	}

}