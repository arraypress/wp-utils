<?php
/**
 * Trait: Terms Analysis
 *
 * This trait provides methods for analyzing relationships and commonalities
 * between multiple WordPress terms, including ancestry and taxonomy analysis.
 *
 * @package     ArrayPress\Utils\Traits\Terms
 * @since       1.0.0
 * @author      David Sherlock
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Terms;

use WP_Term;

trait Analysis {

	/**
	 * Get the common ancestors of multiple terms.
	 *
	 * Analyzes multiple terms and finds any ancestors that are shared between all
	 * of them, useful for determining common parent terms in hierarchical taxonomies.
	 *
	 * @param array  $term_ids An array of term IDs.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return array An array of common ancestor term IDs.
	 */
	public static function get_common_ancestors( array $term_ids, string $taxonomy ): array {
		if ( empty( $term_ids ) ) {
			return [];
		}

		$all_ancestors = [];

		foreach ( $term_ids as $term_id ) {
			$ancestors       = get_ancestors( $term_id, $taxonomy );
			$all_ancestors[] = $ancestors;
		}

		return ! empty( $all_ancestors ) ? array_intersect( ...$all_ancestors ) : [];
	}

	/**
	 * Check if all given terms belong to the same taxonomy.
	 *
	 * Verifies whether a set of terms all belong to the same taxonomy, which is
	 * useful for validating term sets before performing operations on them.
	 *
	 * @param array $term_ids An array of term IDs.
	 *
	 * @return bool True if all terms belong to the same taxonomy, false otherwise.
	 */
	public static function are_in_same_taxonomy( array $term_ids ): bool {
		if ( empty( $term_ids ) ) {
			return false;
		}

		$taxonomies = [];

		foreach ( $term_ids as $term_id ) {
			$term = get_term( $term_id );
			if ( is_wp_error( $term ) ) {
				return false;
			}
			$taxonomies[] = $term->taxonomy;
		}

		return count( array_unique( $taxonomies ) ) === 1;
	}

	/**
	 * Find terms with shared characteristics.
	 *
	 * Identifies terms that share specific attributes or relationships,
	 * such as having the same parent, similar names, or matching meta values.
	 *
	 * @param array  $term_ids An array of term IDs to analyze.
	 * @param string $taxonomy The taxonomy name.
	 * @param array  $criteria Optional. Criteria for determining similarity.
	 *
	 * @return array An array of terms grouped by their shared characteristics.
	 */
	protected static function find_similar_terms( array $term_ids, string $taxonomy, array $criteria = [] ): array {
		$terms    = [];
		$grouped  = [];
		$defaults = [
			'by_parent'    => true,  // Group by parent term
			'by_meta'      => [],    // Array of meta keys to compare
			'name_pattern' => false, // Regular expression for name matching
		];

		$criteria = wp_parse_args( $criteria, $defaults );

		// Get all terms
		foreach ( $term_ids as $term_id ) {
			$term = get_term( $term_id, $taxonomy );
			if ( ! is_wp_error( $term ) && $term instanceof WP_Term ) {
				$terms[] = $term;
			}
		}

		// Group by parent
		if ( $criteria['by_parent'] ) {
			foreach ( $terms as $term ) {
				$grouped['by_parent'][ $term->parent ][] = $term;
			}
		}

		// Group by meta values
		if ( ! empty( $criteria['by_meta'] ) ) {
			foreach ( $criteria['by_meta'] as $meta_key ) {
				foreach ( $terms as $term ) {
					$meta_value                                    = get_term_meta( $term->term_id, $meta_key, true );
					$grouped['by_meta'][ $meta_key ][ $meta_value ][] = $term;
				}
			}
		}

		// Group by name pattern
		if ( $criteria['name_pattern'] ) {
			foreach ( $terms as $term ) {
				if ( preg_match( $criteria['name_pattern'], $term->name, $matches ) ) {
					$pattern_match = $matches[0] ?? '';
					$grouped['by_name_pattern'][ $pattern_match ][] = $term;
				}
			}
		}

		return $grouped;
	}

	/**
	 * Calculate the relationship depth between terms.
	 *
	 * Determines how many levels separate two terms in a hierarchical taxonomy.
	 * Returns -1 if terms are not in the same branch.
	 *
	 * @param int    $term_id_1 First term ID.
	 * @param int    $term_id_2 Second term ID.
	 * @param string $taxonomy  The taxonomy name.
	 *
	 * @return int The number of levels between terms, or -1 if not related.
	 */
	protected static function get_relationship_depth( int $term_id_1, int $term_id_2, string $taxonomy ): int {
		$ancestors_1 = get_ancestors( $term_id_1, $taxonomy );
		$ancestors_2 = get_ancestors( $term_id_2, $taxonomy );

		// If one is ancestor of another
		if ( in_array( $term_id_1, $ancestors_2, true ) ) {
			return array_search( $term_id_1, $ancestors_2, true ) + 1;
		}

		if ( in_array( $term_id_2, $ancestors_1, true ) ) {
			return array_search( $term_id_2, $ancestors_1, true ) + 1;
		}

		// Find common ancestor
		$common_ancestors = array_intersect( $ancestors_1, $ancestors_2 );
		if ( empty( $common_ancestors ) ) {
			return -1;
		}

		$nearest_common_ancestor = reset( $common_ancestors );
		$depth_1                = array_search( $nearest_common_ancestor, $ancestors_1, true ) + 1;
		$depth_2                = array_search( $nearest_common_ancestor, $ancestors_2, true ) + 1;

		return $depth_1 + $depth_2;
	}

}