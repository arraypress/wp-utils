<?php
/**
 * Trait: Terms Sanitize
 *
 * This trait provides advanced query functionality for WordPress terms,
 * including retrieving unused terms, most used terms, and related terms analysis.
 *
 * @package     ArrayPress\Utils\Traits\Terms
 * @since       1.0.0
 * @author      David Sherlock
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Terms;

use ArrayPress\Utils\Common\Sanitize as CommonSanitize;

trait Sanitize {
	use Core;

	/**
	 * Sanitize and validate a list of term identifiers (IDs, slugs, names, or term objects).
	 *
	 * This method ensures all terms exist within the specified taxonomy and returns an array of
	 * unique, valid term IDs or term objects. Returns an empty array if the taxonomy doesn't exist
	 * or no valid terms are found.
	 *
	 * @param array|mixed $identifiers    The input array of term identifiers (IDs, slugs, names, or term objects).
	 * @param string      $taxonomy       The taxonomy name to validate against.
	 * @param bool        $return_objects Optional. Whether to return term objects instead of IDs. Default false.
	 *
	 * @return array<int|object> Array of sanitized term IDs or term objects.
	 */
	public static function sanitize_by_identifiers( $identifiers, string $taxonomy, bool $return_objects = false ): array {
		// Ensure taxonomy exists
		if ( empty( $taxonomy ) || ! taxonomy_exists( $taxonomy ) ) {
			return [];
		}

		// Convert to array if not already
		$identifiers = is_array( $identifiers ) ? $identifiers : [ $identifiers ];

		// Remove empty values and sanitize strings
		$identifiers = array_filter( $identifiers, function ( $identifier ) {
			return ! empty( $identifier ) || $identifier === '0';
		} );

		$identifiers = array_map( function ( $identifier ) {
			if ( is_string( $identifier ) ) {
				return sanitize_text_field( $identifier );
			}

			return $identifier;
		}, $identifiers );

		if ( empty( $identifiers ) ) {
			return [];
		}

		// Use get_by_identifiers to validate and retrieve terms
		return self::get_by_identifiers( $identifiers, $taxonomy, $return_objects );
	}

	/**
	 * Sanitize and validate a list of taxonomy term IDs.
	 *
	 * Ensures all terms exist within the specified taxonomy and returns an array of
	 * unique, valid term IDs. Returns an empty array if the taxonomy doesn't exist
	 * or no valid terms are found.
	 *
	 * @param array|mixed $term_ids The input array of taxonomy term IDs to be sanitized.
	 * @param string      $taxonomy The taxonomy name to validate against.
	 *
	 * @return array<int, int> Array of sanitized and validated term IDs.
	 */
	public static function sanitize( $term_ids, string $taxonomy ): array {
		// Ensure taxonomy exists
		if ( empty( $taxonomy ) || ! taxonomy_exists( $taxonomy ) ) {
			return [];
		}

		// Convert to array and ensure we have values
		$term_ids = CommonSanitize::object_ids( $term_ids );
		if ( empty( $term_ids ) ) {
			return [];
		}

		// Filter for valid terms only
		$valid_terms = array_filter( $term_ids, function ( $term_id ) use ( $taxonomy ) {
			return term_exists( (int) $term_id, $taxonomy );
		} );

		// Return unique values as integers
		return array_unique( array_map( 'intval', $valid_terms ) );
	}

}