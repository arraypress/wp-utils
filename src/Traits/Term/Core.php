<?php
/**
 * Trait: Term Core
 *
 * This trait provides core functionality for working with WordPress terms,
 * including term existence checking, retrieval by various identifiers (ID, slug,
 * name, meta), and term querying capabilities.
 *
 * @package     ArrayPress\Utils\Traits\Term
 * @since       1.0.0
 * @author      David Sherlock
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Term;

use ArrayPress\Utils\Database\Exists;
use WP_Term;

trait Core {

	/**
	 * Check if the term exists in the database.
	 *
	 * @param int $term_id The ID of the term to check.
	 *
	 * @return bool True if the term exists, false otherwise.
	 */
	public static function exists( int $term_id ): bool {
		return Exists::row( 'terms', 'term_id', $term_id );
	}

	/**
	 * Get a term object by its ID.
	 *
	 * @param int    $term_id  The term ID.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return WP_Term|null Returns the term object if found, null if not found or on error.
	 */
	public static function get( int $term_id, string $taxonomy ): ?WP_Term {
		$term = get_term( $term_id, $taxonomy );

		return ( ! is_wp_error( $term ) && $term instanceof WP_Term ) ? $term : null;
	}

	/**
	 * Get a single term by its identifier (ID, slug, name, or term object).
	 *
	 * This method attempts to find a term using different identifiers in the following order:
	 * 1. Term object (if provided)
	 * 2. Term ID (if numeric)
	 * 3. Term slug
	 * 4. Term name
	 *
	 * @param mixed  $identifier The term identifier (ID, slug, name, or term object).
	 * @param string $taxonomy   The name of the taxonomy to search within.
	 *
	 * @return WP_Term|null Returns the term object if found, null if not found or on error.
	 */
	public static function get_by_identifier( $identifier, string $taxonomy ): ?WP_Term {
		if ( empty( $identifier ) || empty( $taxonomy ) ) {
			return null;
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

		return ( ! is_wp_error( $term ) && $term instanceof WP_Term ) ? $term : null;
	}

	/**
	 * Get a term by its slug.
	 *
	 * @param string $slug     The term slug.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return WP_Term|null Returns the term object if found, null if not found or on error.
	 */
	public static function get_by_slug( string $slug, string $taxonomy ): ?WP_Term {
		$term = get_term_by( 'slug', $slug, $taxonomy );

		return ( ! is_wp_error( $term ) && $term instanceof WP_Term ) ? $term : null;
	}

	/**
	 * Get a term by its name.
	 *
	 * @param string $name     The term name.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return WP_Term|null Returns the term object if found, null if not found or on error.
	 */
	public static function get_by_name( string $name, string $taxonomy ): ?WP_Term {
		$term = get_term_by( 'name', $name, $taxonomy );

		return ( ! is_wp_error( $term ) && $term instanceof WP_Term ) ? $term : null;
	}

	/**
	 * Get the first found term by meta value.
	 *
	 * @param string $meta_key   The meta key to search by.
	 * @param mixed  $meta_value The meta value to search for.
	 * @param string $taxonomy   The taxonomy name.
	 *
	 * @return WP_Term|null Returns the term object if found, null if not found or on error.
	 */
	public static function get_by_meta( string $meta_key, string $meta_value, string $taxonomy ): ?WP_Term {
		$terms = get_terms( [
			'taxonomy'   => $taxonomy,
			'meta_key'   => $meta_key,
			'meta_value' => $meta_value,
			'number'     => 1,
			'hide_empty' => false,
		] );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return null;
		}

		return reset( $terms );
	}

	/**
	 * Get a random term from the specified taxonomy.
	 *
	 * @param string $taxonomy The taxonomy name.
	 * @param array  $args     Optional. Additional arguments for get_terms().
	 *
	 * @return WP_Term|null Returns a random term object if found, null if not found or on error.
	 */
	public static function get_random( string $taxonomy, array $args = [] ): ?WP_Term {
		$defaults = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'orderby'    => 'RAND',
			'number'     => 1,
		];

		$args  = wp_parse_args( $args, $defaults );
		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return null;
		}

		return reset( $terms );
	}

	/**
	 * Get the most recently created term in a taxonomy.
	 *
	 * @param string $taxonomy The taxonomy name.
	 * @param array  $args     Optional. Additional arguments for get_terms().
	 *
	 * @return WP_Term|null Returns the most recent term object if found, null if not found or on error.
	 */
	public static function get_most_recent( string $taxonomy, array $args = [] ): ?WP_Term {
		$defaults = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'orderby'    => 'term_id',
			'order'      => 'DESC',
			'number'     => 1,
		];

		$args  = wp_parse_args( $args, $defaults );
		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return null;
		}

		return reset( $terms );
	}

	/**
	 * Get terms that have a specific number of posts assigned to them.
	 *
	 * @param string $taxonomy   The taxonomy name.
	 * @param int    $post_count The number of posts to match.
	 * @param string $operator   The comparison operator ('=', '>', '<', '>=', '<=').
	 * @param array  $args       Optional. Additional arguments for get_terms().
	 *
	 * @return array An array of term objects matching the criteria.
	 */
	public static function get_by_object_count( string $taxonomy, int $post_count, string $operator = '=', array $args = [] ): array {
		$defaults = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		];

		$args  = wp_parse_args( $args, $defaults );
		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) ) {
			return [];
		}

		return array_filter( $terms, function ( $term ) use ( $post_count, $operator ) {
			switch ( $operator ) {
				case '>':
					return $term->count > $post_count;
				case '<':
					return $term->count < $post_count;
				case '>=':
					return $term->count >= $post_count;
				case '<=':
					return $term->count <= $post_count;
				default:
					return $term->count === $post_count;
			}
		} );
	}

}