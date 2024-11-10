<?php
/**
 * Trait: Term Hierarchy
 *
 * This trait provides methods for working with hierarchical relationships between terms,
 * including retrieving children, siblings, and navigating term relationships.
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

trait Hierarchy {

	/**
	 * Get all child terms of a given term.
	 *
	 * @param int    $parent_id The parent term ID.
	 * @param string $taxonomy  The taxonomy name.
	 * @param array  $args      Optional. Additional get_terms() arguments.
	 *
	 * @return array An array of child term objects.
	 */
	public static function get_children( int $parent_id, string $taxonomy, array $args = [] ): array {
		$defaults = [
			'taxonomy'   => $taxonomy,
			'child_of'   => $parent_id,
			'hide_empty' => false,
		];
		$args     = wp_parse_args( $args, $defaults );
		$terms    = get_terms( $args );

		return is_wp_error( $terms ) ? [] : $terms;
	}

	/**
	 * Get term siblings.
	 *
	 * @param int    $term_id  The term ID.
	 * @param string $taxonomy The taxonomy name.
	 * @param array  $args     Optional. Additional get_terms() arguments.
	 *
	 * @return array An array of sibling term objects.
	 */
	public static function get_siblings( int $term_id, string $taxonomy, array $args = [] ): array {
		$term = get_term( $term_id, $taxonomy );
		if ( is_wp_error( $term ) ) {
			return [];
		}

		$defaults = [
			'taxonomy'   => $taxonomy,
			'parent'     => $term->parent,
			'exclude'    => $term_id,
			'hide_empty' => false,
		];
		$args     = wp_parse_args( $args, $defaults );
		$siblings = get_terms( $args );

		return is_wp_error( $siblings ) ? [] : $siblings;
	}

	/**
	 * Get the next sibling term.
	 *
	 * @param int    $term_id  The term ID.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return WP_Term|null The next sibling term or null if not found.
	 */
	public static function get_next_sibling( int $term_id, string $taxonomy ): ?WP_Term {
		$term = get_term( $term_id, $taxonomy );
		if ( is_wp_error( $term ) ) {
			return null;
		}

		$siblings = self::get_siblings( $term_id, $taxonomy, [ 'orderby' => 'name', 'order' => 'ASC' ] );
		$found    = false;
		foreach ( $siblings as $sibling ) {
			if ( $found ) {
				return $sibling;
			}
			if ( $sibling->term_id === $term_id ) {
				$found = true;
			}
		}

		return null;
	}

	/**
	 * Get the previous sibling term.
	 *
	 * @param int    $term_id  The term ID.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return WP_Term|null The previous sibling term or null if not found.
	 */
	public static function get_previous_sibling( int $term_id, string $taxonomy ): ?WP_Term {
		$term = get_term( $term_id, $taxonomy );
		if ( is_wp_error( $term ) ) {
			return null;
		}

		$siblings = self::get_siblings( $term_id, $taxonomy, [ 'orderby' => 'name', 'order' => 'ASC' ] );
		$previous = null;
		foreach ( $siblings as $sibling ) {
			if ( $sibling->term_id === $term_id ) {
				return $previous;
			}
			$previous = $sibling;
		}

		return null;
	}

	/**
	 * Get all terms that share the same parent as the given term.
	 *
	 * @param int    $term_id  The term ID.
	 * @param string $taxonomy The taxonomy name.
	 * @param array  $args     Optional. Additional get_terms() arguments.
	 *
	 * @return array An array of term objects that share the same parent.
	 */
	public static function get_cousins( int $term_id, string $taxonomy, array $args = [] ): array {
		$term = get_term( $term_id, $taxonomy );
		if ( is_wp_error( $term ) ) {
			return [];
		}

		$defaults = [
			'taxonomy'   => $taxonomy,
			'parent'     => $term->parent,
			'hide_empty' => false,
		];
		$args     = wp_parse_args( $args, $defaults );
		$cousins  = get_terms( $args );

		return is_wp_error( $cousins ) ? [] : $cousins;

	}

}