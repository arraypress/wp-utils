<?php
/**
 * Trait: Term Relationship
 *
 * This trait provides methods for analyzing relationships between terms,
 * such as descendant checks and child term verification.
 *
 * @package     ArrayPress\Utils\Traits\Term
 * @since       1.0.0
 * @author      David Sherlock
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Term;

trait Relationship {

	/**
	 * Check if a term is a descendant of another term.
	 *
	 * @param int    $term_id     The term ID to check.
	 * @param int    $ancestor_id The potential ancestor term ID.
	 * @param string $taxonomy    The taxonomy name.
	 *
	 * @return bool True if the term is a descendant, false otherwise.
	 */
	public static function is_descendant( int $term_id, int $ancestor_id, string $taxonomy ): bool {
		$ancestors = get_ancestors( $term_id, $taxonomy );

		return in_array( $ancestor_id, $ancestors, true );
	}

	/**
	 * Check if a term has children.
	 *
	 * @param int    $term_id  The term ID.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return bool True if the term has children, false otherwise.
	 */
	public static function has_children( int $term_id, string $taxonomy ): bool {
		$children = get_terms( [
			'taxonomy'   => $taxonomy,
			'parent'     => $term_id,
			'hide_empty' => false,
			'number'     => 1,
		] );

		return ! is_wp_error( $children ) && ! empty( $children );
	}

}