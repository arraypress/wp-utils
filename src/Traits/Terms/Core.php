<?php
/**
 * Trait: Terms Core
 *
 * This trait provides core functionality for working with multiple WordPress terms,
 * including existence checking, retrieval by various identifiers, and field access.
 *
 * @package     ArrayPress\Utils\Traits\Terms
 * @since       1.0.0
 * @author      David Sherlock
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Terms;

use ArrayPress\Utils\Common\Sanitize;
use WP_Term;

trait Core {

	/**
	 * Get an array of existing term IDs from a provided array of term IDs.
	 *
	 * @param array  $term_ids An array of term IDs to check.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return array An array of existing term IDs.
	 */
	public static function exists( array $term_ids, string $taxonomy ): array {
		$term_ids = Sanitize::object_ids( $term_ids );

		if ( empty( $term_ids ) || empty( $taxonomy ) ) {
			return [];
		}

		return array_filter( $term_ids, function ( $term_id ) use ( $taxonomy ) {
			$term = get_term( $term_id, $taxonomy );

			return ! is_wp_error( $term ) && $term instanceof WP_Term;
		} );
	}

	/**
	 * Get an array of term objects based on provided term IDs.
	 *
	 * @param array  $term_ids     An array of term IDs to retrieve.
	 * @param string $taxonomy     The taxonomy name.
	 * @param bool   $include_meta Optional. Whether to include term meta. Default false.
	 *
	 * @return array Array of term objects.
	 */
	public static function get( array $term_ids, string $taxonomy, bool $include_meta = false ): array {
		$term_ids = Sanitize::object_ids( $term_ids );

		if ( empty( $term_ids ) || empty( $taxonomy ) ) {
			return [];
		}

		$args = [
			'taxonomy'   => $taxonomy,
			'include'    => $term_ids,
			'hide_empty' => false,
		];

		if ( $include_meta ) {
			$args['update_term_meta_cache'] = true;
		}

		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) ) {
			return [];
		}

		// Maintain the order of the input IDs
		$ordered_terms = [];
		foreach ( $term_ids as $term_id ) {
			foreach ( $terms as $term ) {
				if ( $term->term_id === $term_id ) {
					$ordered_terms[] = $term;
					break;
				}
			}
		}

		return $ordered_terms;
	}

	/**
	 * Get an array of unique term IDs or term objects based on provided term names, slugs, or IDs.
	 *
	 * @param array  $term_identifiers An array of term names, slugs, IDs, or term objects to search for.
	 * @param string $taxonomy         The name of the taxonomy to search within.
	 * @param bool   $return_objects   Whether to return term objects instead of term IDs. Default is false.
	 *
	 * @return array An array of unique term IDs as integers or term objects.
	 */
	public static function get_by_identifiers( array $term_identifiers, string $taxonomy, bool $return_objects = false ): array {
		if ( empty( $term_identifiers ) || empty( $taxonomy ) ) {
			return [];
		}

		$unique_terms = [];

		foreach ( $term_identifiers as $identifier ) {
			if ( empty( $identifier ) ) {
				continue;
			}

			if ( is_object( $identifier ) && isset( $identifier->term_id ) ) {
				$term = get_term( $identifier->term_id, $taxonomy );
			} elseif ( is_numeric( $identifier ) ) {
				$term = get_term_by( 'id', $identifier, $taxonomy );
			} else {
				$term = get_term_by( 'slug', $identifier, $taxonomy );
				if ( ! $term ) {
					$term = get_term_by( 'name', $identifier, $taxonomy );
				}
			}

			if ( $term && ! is_wp_error( $term ) ) {
				$unique_terms[ $term->term_id ] = $term;
			}
		}

		if ( $return_objects ) {
			return array_values( $unique_terms );
		}

		return array_map( 'intval', array_keys( $unique_terms ) );
	}

	/**
	 * Get term names from an array of term identifiers.
	 *
	 * @param array  $terms    Array of term IDs, names, slugs, or term objects.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return array Array of term names, empty array if no valid terms found.
	 */
	public static function get_names( array $terms, string $taxonomy ): array {
		if ( empty( $terms ) || empty( $taxonomy ) ) {
			return [];
		}

		$result = [];

		foreach ( $terms as $term ) {
			// If term is already a WP_Term object
			if ( is_object( $term ) && isset( $term->name ) ) {
				$result[] = $term->name;
				continue;
			}

			// If term is numeric, try getting by ID
			if ( is_numeric( $term ) ) {
				$term_obj = get_term( (int) $term, $taxonomy );
				if ( ! is_wp_error( $term_obj ) && $term_obj instanceof WP_Term ) {
					$result[] = $term_obj->name;
				}
				continue;
			}

			// Try getting by name first, then slug
			$term_obj = get_term_by( 'name', (string) $term, $taxonomy );
			if ( ! $term_obj ) {
				$term_obj = get_term_by( 'slug', (string) $term, $taxonomy );
			}

			if ( $term_obj instanceof WP_Term ) {
				$result[] = $term_obj->name;
			}
		}

		return $result;
	}

	/**
	 * Get specified fields from terms based on provided term names, slugs, or IDs.
	 *
	 * @param array  $term_identifiers An array of term names, slugs, IDs, or term objects to search for.
	 * @param string $taxonomy         The name of the taxonomy to search within.
	 * @param string $field            The field to extract from each term. Default is 'slug'.
	 *
	 * @return array An array of specified field values from the terms.
	 */
	public static function get_fields( array $term_identifiers, string $taxonomy, string $field = 'slug' ): array {
		$terms = self::get_by_identifiers( $term_identifiers, $taxonomy, true );

		return wp_list_pluck( $terms, $field );
	}

}