<?php
/**
 * Trait: Term Fields
 *
 * This trait provides methods for accessing and retrieving various term fields,
 * including names, slugs, and custom field data from WordPress terms.
 *
 * @package     ArrayPress\Utils\Traits\Term
 * @since       1.0.0
 * @author      David Sherlock
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Term;

trait Fields {
	use Core;

	/**
	 * Get the term name.
	 *
	 * @param int    $term_id  The term ID.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return string|null The term name or null if not found.
	 */
	public static function get_name( int $term_id, string $taxonomy ): ?string {
		$term = self::get( $term_id, $taxonomy );

		return $term ? $term->name : null;
	}

	/**
	 * Get the term slug.
	 *
	 * @param int    $term_id  The term ID.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return string|null The term slug or null if not found.
	 */
	public static function get_slug( int $term_id, string $taxonomy ): ?string {
		$term = self::get( $term_id, $taxonomy );

		return $term ? $term->slug : null;
	}

	/**
	 * Get a specific field from the term.
	 *
	 * This method attempts to retrieve a field value from a term. It first checks
	 * for standard term properties (like name, slug, description) and then falls
	 * back to checking term meta.
	 *
	 * @param int    $term_id  The term ID.
	 * @param string $field    The field name to retrieve.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return mixed The field value or null if not found.
	 */
	public static function get_field( int $term_id, string $field, string $taxonomy ) {
		$term = self::get( $term_id, $taxonomy );
		if ( ! $term ) {
			return null;
		}

		// First, check if it's a property of the term object
		if ( isset( $term->$field ) ) {
			return $term->$field;
		}

		// Check if it's a term meta field
		return get_term_meta( $term_id, $field, true );
	}

}