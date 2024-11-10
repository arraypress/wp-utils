<?php
/**
 * Trait: Terms Conditional
 *
 * This trait provides conditional functionality for working with multiple WordPress terms.
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


trait Conditional {

	/**
	 * Required trait method for getting terms by their identifiers.
	 *
	 * @param array  $term_identifiers An array of term identifiers (IDs, slugs, names, or term objects).
	 * @param string $taxonomy         The taxonomy name.
	 * @param bool   $return_objects   Whether to return term objects instead of term IDs.
	 *
	 * @return array An array of unique term IDs or WP_Term objects.
	 */
	abstract protected static function get_by_identifiers( array $term_identifiers, string $taxonomy, bool $return_objects = false ): array;

	/**
	 * Check if all or any of the specified terms exist within a collection of term IDs.
	 *
	 * @param array  $terms     Array of terms to check for (IDs, names, or slugs).
	 * @param array  $term_ids  Array of term IDs to check against.
	 * @param string $taxonomy  The taxonomy name.
	 * @param bool   $match_all Whether all terms must be present (true) or any term (false).
	 *
	 * @return bool True if the terms are found according to match_all parameter, false otherwise.
	 */
	public static function exists_in( array $terms, array $term_ids, string $taxonomy, bool $match_all = true ): bool {
		if ( empty( $terms ) || empty( $term_ids ) || empty( $taxonomy ) ) {
			return false;
		}

		$search_term_ids = static::get_by_identifiers( $terms, $taxonomy );
		if ( empty( $search_term_ids ) ) {
			return false;
		}

		$term_ids  = array_map( 'absint', $term_ids );
		$intersect = array_intersect( $search_term_ids, $term_ids );

		return $match_all
			? count( $intersect ) === count( $search_term_ids )
			: ! empty( $intersect );
	}

	/**
	 * Check if all specified terms have posts.
	 *
	 * @param array  $terms    Array of terms to check (IDs, names, or slugs).
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return bool True if all terms have posts, false otherwise.
	 */
	public static function have_posts( array $terms, string $taxonomy ): bool {
		$term_objects = static::get_by_identifiers( $terms, $taxonomy, true );
		if ( empty( $term_objects ) ) {
			return false;
		}

		foreach ( $term_objects as $term ) {
			if ( $term->count === 0 ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if all specified terms have a minimum post count.
	 *
	 * @param array  $terms    Array of terms to check (IDs, names, or slugs).
	 * @param string $taxonomy The taxonomy name.
	 * @param int    $count    Minimum number of posts required.
	 *
	 * @return bool True if all terms meet the minimum post count, false otherwise.
	 */
	public static function have_minimum_posts( array $terms, string $taxonomy, int $count ): bool {
		$term_objects = static::get_by_identifiers( $terms, $taxonomy, true );
		if ( empty( $term_objects ) ) {
			return false;
		}

		foreach ( $term_objects as $term ) {
			if ( $term->count < $count ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if any of the specified terms are siblings (share the same parent).
	 *
	 * @param array  $terms    Array of terms to check (IDs, names, or slugs).
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return bool True if any terms are siblings, false otherwise.
	 */
	public static function are_siblings( array $terms, string $taxonomy ): bool {
		$term_objects = static::get_by_identifiers( $terms, $taxonomy, true );
		if ( count( $term_objects ) < 2 ) {
			return false;
		}

		$parents = [];
		foreach ( $term_objects as $term ) {
			if ( $term->parent ) {
				$parents[] = $term->parent;
			}
		}

		return count( array_unique( $parents ) ) === 1 && ! empty( $parents );
	}

	/**
	 * Check if the specified terms form a direct hierarchical chain.
	 *
	 * @param array  $terms    Array of terms in expected hierarchical order.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return bool True if terms form a direct parent-child chain, false otherwise.
	 */
	public static function are_hierarchical( array $terms, string $taxonomy ): bool {
		$term_objects = static::get_by_identifiers( $terms, $taxonomy, true );
		if ( count( $term_objects ) < 2 ) {
			return false;
		}

		for ( $i = 1; $i < count( $term_objects ); $i ++ ) {
			if ( $term_objects[ $i ]->parent !== $term_objects[ $i - 1 ]->term_id ) {
				return false;
			}
		}

		return true;
	}

}