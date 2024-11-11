<?php
/**
 * Trait: Term Analysis
 *
 * This trait provides advanced analysis methods for WordPress terms, including
 * hierarchy analysis, path generation, depth calculation, and ancestor determination.
 *
 * @package     ArrayPress\Utils\Traits\Term
 * @since       1.0.0
 * @author      David Sherlock
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Term;

use WP_Term;

trait Analysis {

	/**
	 * Get the term hierarchy as a nested array.
	 *
	 * @param string $taxonomy The taxonomy name.
	 * @param int    $parent   The parent term ID. Use 0 for top-level terms.
	 * @param array  $args     Optional. Additional get_terms() arguments.
	 *
	 * @return array An array of term objects with 'children' key for nested terms.
	 */
	public static function get_hierarchy( string $taxonomy, int $parent = 0, array $args = [] ): array {
		$defaults = [
			'taxonomy'   => $taxonomy,
			'parent'     => $parent,
			'hide_empty' => false,
		];
		$args     = wp_parse_args( $args, $defaults );
		$terms    = get_terms( $args );

		if ( is_wp_error( $terms ) ) {
			return [];
		}

		$hierarchy = [];
		foreach ( $terms as $term ) {
			$term->children = self::get_hierarchy( $taxonomy, $term->term_id, $args );
			$hierarchy[]    = $term;
		}

		return $hierarchy;
	}

	/**
	 * Get the full hierarchical path of a term.
	 *
	 * @param int    $term_id   The term ID.
	 * @param string $taxonomy  The taxonomy name.
	 * @param string $separator The separator between terms in the path.
	 *
	 * @return string The full path of the term.
	 */
	public static function get_path( int $term_id, string $taxonomy, string $separator = ' > ' ): string {
		$term = get_term( $term_id, $taxonomy );
		if ( is_wp_error( $term ) ) {
			return '';
		}

		$path = [ $term->name ];
		while ( $term->parent !== 0 ) {
			$term = get_term( $term->parent, $taxonomy );
			if ( is_wp_error( $term ) ) {
				break;
			}
			array_unshift( $path, $term->name );
		}

		return implode( $separator, $path );
	}

	/**
	 * Calculate the depth of a term in the hierarchy.
	 *
	 * @param int    $term_id  The term ID.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return int The depth of the term (0 for top-level terms).
	 */
	public static function get_depth( int $term_id, string $taxonomy ): int {
		$ancestors = get_ancestors( $term_id, $taxonomy );

		return count( $ancestors );
	}

	/**
	 * Get the root ancestor of a term.
	 *
	 * @param int    $term_id  The term ID.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return WP_Term|null Returns the root ancestor term if found, null if not found or on error.
	 */
	public static function get_root_ancestor( int $term_id, string $taxonomy ): ?WP_Term {
		$ancestors = get_ancestors( $term_id, $taxonomy );
		if ( empty( $ancestors ) ) {
			$term = get_term( $term_id, $taxonomy );

			return ( ! is_wp_error( $term ) && $term instanceof WP_Term ) ? $term : null;
		}

		$root_id = end( $ancestors );
		$term    = get_term( $root_id, $taxonomy );

		return ( ! is_wp_error( $term ) && $term instanceof WP_Term ) ? $term : null;
	}

	/**
	 * Get the first common ancestor between two terms.
	 *
	 * @param int    $term_id_1 First term ID.
	 * @param int    $term_id_2 Second term ID.
	 * @param string $taxonomy  The taxonomy name.
	 *
	 * @return WP_Term|null Returns the first common ancestor if found, null if not found or on error.
	 */
	public static function get_common_ancestor( int $term_id_1, int $term_id_2, string $taxonomy ): ?WP_Term {
		$ancestors_1 = array_reverse( get_ancestors( $term_id_1, $taxonomy ) );
		$ancestors_2 = array_reverse( get_ancestors( $term_id_2, $taxonomy ) );

		$common_ancestor_id = null;
		$i                  = 0;

		while ( isset( $ancestors_1[ $i ] ) && isset( $ancestors_2[ $i ] )
		        && $ancestors_1[ $i ] === $ancestors_2[ $i ] ) {
			$common_ancestor_id = $ancestors_1[ $i ];
			$i ++;
		}

		if ( $common_ancestor_id ) {
			return self::get( $common_ancestor_id, $taxonomy );
		}

		return null;
	}

}