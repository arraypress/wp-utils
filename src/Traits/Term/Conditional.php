<?php
/**
 * Trait: Term Conditional
 *
 * This trait provides conditional functionality for working with WordPress terms.
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

trait Conditional {
	use Core;

	/**
	 * Check if a specific term exists within a collection of term IDs.
	 *
	 * @param mixed  $term     The term to check for (ID, name, or slug).
	 * @param array  $term_ids Array of term IDs to check against.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return bool True if the term is found, false otherwise.
	 */
	public static function exists_in( $term, array $term_ids, string $taxonomy ): bool {
		if ( empty( $term ) || empty( $term_ids ) || empty( $taxonomy ) ) {
			return false;
		}

		$term_obj = static::get_by_identifier( $term, $taxonomy );
		if ( ! $term_obj ) {
			return false;
		}

		return in_array( $term_obj->term_id, array_map( 'absint', $term_ids ), true );
	}

	/**
	 * Check if a term has children.
	 *
	 * @param mixed  $term     The term to check (ID, slug, name, or term object).
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return bool True if the term has children, false otherwise.
	 */
	public static function has_children( $term, string $taxonomy ): bool {
		$term_obj = static::get_by_identifier( $term, $taxonomy );
		if ( ! $term_obj ) {
			return false;
		}

		$children = get_term_children( $term_obj->term_id, $taxonomy );

		return ! is_wp_error( $children ) && ! empty( $children );
	}

	/**
	 * Check if a term has a specific parent.
	 *
	 * @param mixed  $term        The term to check (ID, slug, name, or term object).
	 * @param mixed  $parent_term The parent term to check against (ID, slug, name, or term object).
	 * @param string $taxonomy    The taxonomy name.
	 *
	 * @return bool True if the term has the specified parent, false otherwise.
	 */
	public static function has_parent( $term, $parent_term, string $taxonomy ): bool {
		$term_obj   = static::get_by_identifier( $term, $taxonomy );
		$parent_obj = static::get_by_identifier( $parent_term, $taxonomy );

		if ( ! $term_obj || ! $parent_obj ) {
			return false;
		}

		return $term_obj->parent === $parent_obj->term_id;
	}

	/**
	 * Check if a term has posts.
	 *
	 * @param mixed  $term     The term to check (ID, slug, name, or term object).
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return bool True if the term has associated posts, false otherwise.
	 */
	public static function has_posts( $term, string $taxonomy ): bool {
		$term_obj = static::get_by_identifier( $term, $taxonomy );

		return $term_obj && $term_obj->count > 0;
	}

	/**
	 * Check if a term's post count meets a specific threshold.
	 *
	 * @param mixed  $term     The term to check (ID, slug, name, or term object).
	 * @param string $taxonomy The taxonomy name.
	 * @param int    $count    The count to compare against.
	 * @param string $operator Comparison operator: '>', '<', '>=', '<=', or '='.
	 *
	 * @return bool True if the condition is met, false otherwise.
	 */
	public static function has_post_count( $term, string $taxonomy, int $count, string $operator = '=' ): bool {
		$term_obj = static::get_by_identifier( $term, $taxonomy );
		if ( ! $term_obj ) {
			return false;
		}

		switch ( $operator ) {
			case '>':
				return $term_obj->count > $count;
			case '<':
				return $term_obj->count < $count;
			case '>=':
				return $term_obj->count >= $count;
			case '<=':
				return $term_obj->count <= $count;
			default:
				return $term_obj->count === $count;
		}
	}

	/**
	 * Check if a term is a descendant of another term.
	 *
	 * @param mixed  $term     The term to check (ID, slug, name, or term object).
	 * @param mixed  $ancestor The potential ancestor term (ID, slug, name, or term object).
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return bool True if the term is a descendant, false otherwise.
	 */
	public static function is_descendant( $term, $ancestor, string $taxonomy ): bool {
		$term_obj = static::get_by_identifier( $term, $taxonomy );
		$ancestor_obj = static::get_by_identifier( $ancestor, $taxonomy );

		if ( ! $term_obj || ! $ancestor_obj ) {
			return false;
		}

		$ancestors = get_ancestors( $term_obj->term_id, $taxonomy );
		return in_array( $ancestor_obj->term_id, $ancestors, true );
	}

}